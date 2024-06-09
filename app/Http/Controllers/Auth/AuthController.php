<?php

namespace App\Http\Controllers\Auth;

use App\Console\Commands\Payments\DetectSubscriptions;
use App\Console\Commands\Payments\SetupCustomerAccount;
use App\Events\UserStatusChangedEvent;
use App\Http\Controllers\Agent\LicenseController;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ConvertStringsToBooleanMiddleware;
use App\Http\Responses\ExpectionFailedResponse;
use App\Http\Responses\OkResponse;
use App\Http\Responses\PreConditionFailedResponse;
use App\Http\Responses\UnauthorizedResponse;
use App\Mail\Auth\ResetCodeMail;
use App\ModelsExtended\AccountStatus;
use App\ModelsExtended\AgencyUsageMode;
use App\ModelsExtended\ApplicationProductPrice;
use App\ModelsExtended\RegistrationAccessCode;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use App\ModelsExtended\UserRole;
use App\Repositories\Stripe\CustomApiInitiatedException;
use App\Repositories\Stripe\StripeSubscriptionSDK;
use App\Rules\PhoneNumberValidationRule;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\ExpectationFailedException;
use Stripe\Exception\ApiErrorException;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware( ConvertStringsToBooleanMiddleware::class );
    }

    protected int $TARGETED_ROLE = Role::Agent;

    /**
     * @param Request $request
     * @return OkResponse
     * @throws ValidationException
     * @throws \Exception
     */
    public function emailAvailability(Request $request)
    {
        $this->validatedRules(
        [
            'email' => 'required|email|max:200'
        ]);

        // Since we are using soft deletes for now
       if( User::withTrashed()
            ->where("email", $request->input("email"))
            ->first() ) throw new \Exception( $request->input("email") . " is NOT available for registration!" );

        return new OkResponse(message( $request->input("email") . " is available for registration!" ));
    }

    /**
     * Required, confirmed and policies
     * @return array
     */
    public static function getPasswordRule(){
        return [
            'required', 'max:20', 'string',
            Password::min(8)
                ->mixedCase()
                ->numbers(),
            'confirmed'
        ];
    }

    /**
     * Rules to validate
     * @return array
     */
    public function getCommonRules()
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
           // 'phone' => [ 'required', 'max:30', new PhoneNumberValidationRule() ],
            'phone' => 'required',
            'address' => 'required|string|max:100',

            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:10',
            'password' => 'required|string|max:100',

            'is_virtuoso_member' => 'required|boolean',

            'linkedin_url' => 'filled|url|max:300',
//                'job_title' => 'nullable|string|max:100',
            'agency_name' => 'nullable|string|max:100',
            'agency_usage_mode_id' => 'required|numeric|exists:agency_usage_mode,id',
            'email' => 'required|email|max:200',
            'subscription_price_id' => 'required|numeric|exists:application_product_prices,id',
            'stripe_payment_token' => 'required|string',
        ];
    }

    /**
     * @return array
     * @throws ValidationException
     */
    public  function validateCreateUserRequest()
    {
       return Validator::validate( \request()->all(), $this->getCommonRules());
    }

    /**
     * @param Request $request
     * @param int $account_status_id
     * @param int|null $agency_usage_mode_id
     * @return User
     * @throws ApiErrorException
     * @throws CustomApiInitiatedException
     * @throws ValidationException
     */
    public function createAgentAccountFromRequest(Request $request,
                                                         int $account_status_id = AccountStatus::PENDING_APPROVAL,
                                                        ?int $agency_usage_mode_id = AgencyUsageMode::LUCIA_EXPERIENCE
    ): User
    {
        $this->validateCreateUserRequest();

        $user = $this->createAccountFromRequest(
            $request,
            $account_status_id,
            $this->TARGETED_ROLE,
            $agency_usage_mode_id,
            $request->input('stripe_payment_token')
        );

        $userRole = UserRole::getUserRole( $this->TARGETED_ROLE, $user->id );


        $price = ApplicationProductPrice::getById($request->input('subscription_price_id'));
        $this->createSubscriptionWithTrialIfAvailable( $userRole, $price );

        return $user;
    }

    /**
     * No validation is called here
     *
     * @param Request $request
     * @param int $account_status_id
     * @param int $role_id
     * @param int|null $agency_usage_mode_id
     * @param string|null $stripe_payment_token
     * @return User
     */
    public function createAccountFromRequest(Request $request,
                                                         int $account_status_id = AccountStatus::PENDING_APPROVAL,
                                                         int $role_id = Role::Agent,
                                                         ?int $agency_usage_mode_id = AgencyUsageMode::LUCIA_EXPERIENCE,
                                                    ?string $stripe_payment_token = null
    ): User
    {
        return  DB::transaction(function () use ($request, $agency_usage_mode_id, $account_status_id, $role_id, $stripe_payment_token ){

            $user = $this->updateOrCreateUserAccount($request, $agency_usage_mode_id, $account_status_id);
            if( $user->hasRole($role_id) )
                throw new \Exception("You already have an account as " . Role::getById( $role_id )->description . " on Lucia. You can reset your password if you have forgotten it.");

            // create the new role
            $user->roles()->create(["role_id" => $role_id]);

            // apply the status
            $user->update(['account_status_id'  => $account_status_id]);

            // add payment method if specified
            if( $stripe_payment_token )
             (new StripeSubscriptionSDK())->createPaymentSourceForCustomer(
                    $user->refresh()->user_stripe_account->getStripeCustomerIdAttribute(),
                    $stripe_payment_token
             );


           return $user->refresh();
       });
    }


    /**
     * Register new as a client
     * @param Request $request
     * @return ExpectionFailedResponse|OkResponse|PreConditionFailedResponse
     */
    public function register(Request $request)
    {
        try {

            // raise event
            event(
                new UserStatusChangedEvent(
                    self::createAgentAccountFromRequest( $request, AccountStatus::APPROVED, $request->input('agency_usage_mode_id')  )
                )
            );

            //  Return
            return new OkResponse( message( "Please, check your email for your temporal password." ) );

        } catch (ValidationException $e) {
            return new PreConditionFailedResponse( $e->errors() );
        } catch (\Exception $exception )
        {
            return new ExpectionFailedResponse( errorKeyMessage( $exception->getMessage()  )  );
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return new OkResponse( message( 'Successfully logged out' ) );
    }

    /**
     * Login User if user is approved
     *
     * @return JsonResponse|OkResponse|UnauthorizedResponse
     * @throws ValidationException
     */
    public function login( Request $request)
    {
        $this->validatedRules(
            [
                'email' => 'required|email|max:200|exists:users,email',
                'password' => 'required|string|max:200',
            ]);

        return $this->loginWithCredentials(
            $request->input( 'email' ),
            $request->input( 'password' ),
            $this->TARGETED_ROLE );
    }

    /**
     * Try login with the credentials
     * AGENT LOGIN
     *
     * @param string $email
     * @param string $password
     * @param int $role_id
     * @return OkResponse|UnauthorizedResponse|JsonResponse
     * @throws \Exception
     */
    protected function loginWithCredentials( string $email, string $password, int $role_id ){
        // Try Login Credential
        if (!$token = auth()->attempt([ 'email' => $email, 'password' => $password ])) {
            return new UnauthorizedResponse(message("Invalid Login Details!" ));
        }

        $user = auth()->user();
        if( !$user->hasRole($role_id) )
            throw new \Exception("You do not have an account as " . Role::getById( $role_id )->description . " on Lucia. Please, register first to access this role!");

        //  Return logged in details
        return $this->throwExceptionIfNotApproved($user)
            ->throwExceptionIfNotQualified($user)
            ->respondWithToken($token, $role_id);
    }

    /**
     * @param User | Authenticatable  $user
     * @return $this
     */
    private function throwExceptionIfNotApproved( $user)
    {
        if (! $user->isApproved() ) {
            throw new UnauthorizedException( "Your profile is not approved yet!" );
        }
        return $this;
    }

    /**
     * @param User | Authenticatable  $user
     * @return $this
     */
    protected function throwExceptionIfNotQualified( $user): AuthController
    {
        if (! $user->hasRole( $this->TARGETED_ROLE ) ) {
            throw new UnauthorizedException( "You must be registered in as " . Role::getById($this->TARGETED_ROLE)->description .  " to login on this app section!" );
        }
        return $this;
    }

//    /**
//     * Refresh a token.
//     *
//     * @return JsonResponse
//     * @throws CustomApiInitiatedException
//     * @throws ApiErrorException
//     */
//    public function refresh()
//    {
//        return $this->respondWithToken(auth()->refresh());
//    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     * @param int $role_id
     * @return JsonResponse
     */
    protected function respondWithToken(string $token, int $role_id)
    {
        // Check if user is still new, activate trial
//        if( auth()->user()->isTravelAgent() )  $this->createTrialSubscription(auth()->user());

        // means you are logged in here
        $user = User::getById(auth()->id());
        $allocated_minutes = auth()->factory()->getTTL();

        // store session
        $user->user_sessions()->create([
            'role_id' => $role_id,
            'allocated_minutes' => $allocated_minutes,
            'token' => $token,
            'expiry_date_time' => Carbon::now()->addMinutes($allocated_minutes),
            'ip_address' => getIpAddressOverlookProxy(),
        ]);

        return new OkResponse([

            'user' => $user->presentForDev(),

            'logged_in_as' => Role::getById($role_id)->description,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in_seconds' =>  $allocated_minutes * 60    // return in seconds
        ]);
    }


    /**
     * @param UserRole $userRole
     * @param ApplicationProductPrice $price
     * @return void
     * @throws ApiErrorException
     * @throws CustomApiInitiatedException
     */
    public function createSubscriptionWithTrialIfAvailable(UserRole $userRole, ApplicationProductPrice $price)
    {
        // First do monitor just to be sure
        if ($userRole->has_valid_license) return;

        // call artisan here
        Artisan::call('payments:detect-subscriptions ' . $userRole->user_id);
        $userRole->refresh();

        // if trial days is entered on the environment
        $STRIPE_LUCIA_TRIAL_DAYS = intval(env('STRIPE_LUCIA_TRIAL_DAYS'));

        // disable trial for yearly
        if( $price->id === ApplicationProductPrice::LUCIA_EXPERIENCE_YEARLY )
            $STRIPE_LUCIA_TRIAL_DAYS = null;


        $stripe_price_id = $price->stripe_price_id;

        //If partner agent then take the partner payment plan
        if($userRole->user->preferred_partner_id):
            $stripe_price_id = ($price->id === ApplicationProductPrice::LUCIA_EXPERIENCE_YEARLY)?$userRole->user->partner->annual_price:$userRole->user->partner->monthly_price;
        endif;

        // only applicable if you have never had a license or subscription before on the platform
        $SDK = new StripeSubscriptionSDK();
        if (!$userRole->has_valid_license && !$userRole->user->stripe_subscription_histories()->exists()) {
            DetectSubscriptions::addActiveSubscriptionToUser($userRole->user,
                $SDK->createSubscription(
                    $stripe_price_id,
                    $userRole->user->user_stripe_account->getStripeCustomerIdAttribute(), null,
                    $STRIPE_LUCIA_TRIAL_DAYS,
                    [
                        "role_id" => $userRole->role_id
                    ]
                )
            );
        }
    }

    /**
     * Generates and send reset password code to email
     * @param Request $request
     * @return OkResponse
     * @throws ValidationException
     */
    public function forgotPassword(  Request $request )
    {
        $this->validatedRules([
            'email' => 'required|email|max:200|exists:users,email',
        ]);

        $email = $request->input("email");

        $user = User::getByEmail( $email);

        $this->throwExceptionIfNotApproved($user);

        return new OkResponse([
            "expires_in_minutes" => Carbon::now()->diffInMinutes( $user->createValidationToken()->password_reset_token_expiry )
        ]);
    }


    /**
     * Checks if the reset token is still valid
     * @param Request $request
     * @return OkResponse
     * @throws ValidationException
     */
    public function validateResetToken( Request $request )
    {
        $this->validatedRules([
            'email' => 'required|email|max:200|exists:users,email',
            'password_reset_token' => 'required|string|max:100',
        ]);

        $this->requestHasValidPasswordResetToken( $request );
        return new OkResponse();
    }

    /**
     * Update users password if token is valid
     * @param Request $request
     * @return OkResponse
     * @throws ValidationException
     * @throws \Exception
     */
    public function updatePassword( Request $request )
    {
        $this->validatedRules([
            'email' => 'required|email|max:200|exists:users,email',
            'password_reset_token' => 'required|string|max:100',
            'password' => self::getPasswordRule(),
        ]);

        $user = $this->requestHasValidPasswordResetToken( $request )
                ->clearValidationToken();

        $user->update([
            "password" => Hash::make( $request->input('password' ) )
        ]);

        return new OkResponse();
    }

    /**
     * Checks if current request user has valid reset token
     * @param Request $request
     * @return User
     * @throws \Exception
     */
    private function requestHasValidPasswordResetToken( Request  $request): User
    {
        $user = User::getByEmail( $request->input("email" ));

        if( !$user || !$user->hasValidationToken(  $request->input( "password_reset_token") ) )
            throw new \Exception( "Invalid code or expired code!");

        return $user;
    }


    /**
     * Login User if user is approved
     *
     * @return JsonResponse|OkResponse|UnauthorizedResponse
     * @throws ValidationException
     */
    public function impersonate( string $impersonation_token, Request $request)
    {
        // Get agent to impersonate
        $user = User::getImpersonableAgent( $impersonation_token );

        if (! $user ) return new UnauthorizedResponse(message("Invalid Impersonation Details! Make sure you still have a valid token." ));

        // login and get token
        $token = auth()->login( $user );

        // clear
        $user->impersonation_token = null;
        $user->update();

        //  Return logged in details
        return $this->throwExceptionIfNotApproved(auth()->user())
            ->throwExceptionIfNotQualified(auth()->user())
            ->respondWithToken($token, $this->TARGETED_ROLE );
    }

    /**
     * Checks if the registration access token is still valid
     * @param Request $request
     * @return OkResponse
     * @throws ValidationException
     */
    public function validateRegistrationToken( Request $request )
    {
        $this->validatedRules([
            'code' => 'required|string|max:20',
        ]);

        if( ! RegistrationAccessCode::getByCode( $request->input( 'code' ) ) )
            throw new ExpectationFailedException('Invalid registration access code!');

        return new OkResponse();
    }

    /**
     * This won't change the account status if it already exists
     *
     * @param Request $request
     * @param int|null $agency_usage_mode_id
     * @param int $account_status_id
     * @return Builder|Model|User
     * @throws \Exception
     */
    private function updateOrCreateUserAccount(Request $request,
                                               ?int $agency_usage_mode_id,
                                               int $account_status_id)
    {
        $user = User::getByEmailWithTrashed( $request->input('email') );
        if( $user && $user->deleted_at ) throw new \Exception("Your account has been deleted, you can not recreate a new account with the same email again.");

        if( !$user )
        {
            // This currently will be crashing for deleted accounts
            // because we are currently using soft delete.

            // Create new user
            $user = (
            new User([
                'email' => $request->input( 'email' ),
                'password' => app('hash')->make( Str::random(8 ) ),
                'first_name'  => $request->input( 'first_name' ),
                'last_name'  => $request->input( 'last_name' ),
                'phone'  => $request->input( 'phone' ),
                'location'  => $request->input( 'address' ),
                'country_id'  => $request->input('country'),
                "state" => $request->input('state'),
               // "city" => $request->input('city'),
                "zip" => $request->input('zip'),
                "password" =>app('hash')->make($request->input('password')),// Hash::make($request->input('password')),
                "is_virtuoso_member" => $request->input('is_virtuoso_member', false),

                'agency_name'  => $request->input( 'agency_name' ),
                'preferred_partner_id'  => $request->input( 'preferred_partner_id' ),
                'linkedin_url'  => $request->input( 'linkedin_url' ),
                'agency_usage_mode_id'  => $agency_usage_mode_id,
                'account_status_id'  => $account_status_id,
            ])
            )->setFriendlyIdentifier();
            $user->save();

        }else if( $agency_usage_mode_id )
        {
            $user->update([
                'agency_usage_mode_id'  => $agency_usage_mode_id,
            ]);
        }

        SetupCustomerAccount::createOrUpdateCustomer( $user );
        return $user;
    }
}
