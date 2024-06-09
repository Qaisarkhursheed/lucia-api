<?php

namespace App\ModelsExtended;

use App\Http\Middleware\Authenticate;
use App\Mail\Auth\ResetCodeMail;
use App\Models\AdvisorRequestArchived;
use App\Models\StripeConnectReminder;
use App\Models\FavoriteCopilot;
use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use App\ModelsExtended\Interfaces\IHasFolderStoragePathModelInterface;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Repositories\Stripe\StripeConnectSDK;
use App\Repositories\Stripe\StripeSubscriptionSDK;
use App\Models\Testimonials;

/**
 * @property bool $is_google_auth_validated
 * @property StripeConnectReminder|null $last_stripe_connect_reminder
 * @property MasterSubAccount $master_sub_account
 * @property Collection|MasterSubAccount[] $master_sub_accounts
 * @property CopilotAverageFeedback|null $average_feedback
 * @property CopilotRatingsCategorized[]|Collection $ratings
 * @property DefaultItineraryTheme $default_itinerary_theme
 * @property UserStripeAccount $user_stripe_account
 * @property StripeSubscriptionHistory $last_subscription
 * @property Collection|CopilotDuty[] $copilot_duties
 * @property Collection|UserRole[] $roles
 * @property CopilotInfo|null $copilot_info
 */
class User extends \App\Models\User implements AuthenticatableContract, AuthorizableContract, JWTSubject,
    IDeveloperPresentationInterface, IHasFolderStoragePathModelInterface
{
    use Authenticatable, Authorizable, HasFactory, Notifiable;

    const DEFAULT_ADMIN = 1;

    protected $appends = ['is_google_auth_validated'];
    protected $with = [
		'isFavorite'
	];

    /**
     * @return User|null
     */
    public static function getDefaultAdmin()
    {
        return self::find(self::DEFAULT_ADMIN);
    }

    /**
     * @return User|null
     */
    public static function getCalendarSyncUser()
    {
        return self::find(env('GOOGLE_CALENDAR_SYNC_USER_ID'));
    }

    /**
     * Call setFriendlyIdentifier() first if new
     * get the expected profile picture relative path
     * @return string
     */
    public function getProfilePictureRelativePath()
    {
        // to help with cache bursting
        return $this->friendly_identifier . sprintf('/profile_picture-%s.png', Str::random());
    }

    /**
     * Get traceable folder path
     */
    public function getFolderStorageRelativePath(): string
    {
        return $this->friendly_identifier;
    }

    /**
     * Create a friendly unique identifier for this user
     * because email will likely be editable
     * However, this does not save it to database
     * @return User
     */
    public function setFriendlyIdentifier()
    {
        $this->friendly_identifier = Str::slug($this->email . ' ' . Carbon::now()->timestamp);
        return $this;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function isLoggedInAsAdmin()
    {
        return Authenticate::getUserSession()->role_id === Role::Administrator;
    }

    public function isCopilot()
    {
        return $this->hasRole( Role::Concierge );
    }

    public function isLoggedInAsCopilot()
    {
        return Authenticate::getUserSession()->role_id === Role::Concierge;
    }

    public function isLoggedInAsClient()
    {
        return Authenticate::getUserSession()->role_id === Role::Client;
    }

    public function isLoggedInAsMasterAccount()
    {
        return Authenticate::getUserSession()->role_id === Role::MasterAccount;
    }

    public function isMasterAccountOwner()
    {
        return $this->master_account != null;
    }

    /**
     * @return UserRole|null
     */
    public function lastestUserRole(): ?UserRole
    {
        return $this->roles->sort(fn(UserRole $userRole) => $userRole->id)->last();
    }

    /**
     * @return UserRole|null
     */
    public function agentRole(): ?UserRole
    {
        return $this->roles->where("role_id", "=", Role::Agent)->first();
    }

    /**
     * @return int|null
     */
    public function masterAccountId()
    {
        return optional($this->master_sub_account)->master_account_id;
    }

    public function isTravelAgent(): bool
    {
        return $this->hasRole( Role::Agent );
    }

    public function isLoggedInAsTravelAgent()
    {
        return Authenticate::getUserSession()->role_id === Role::Agent;
    }

    /**
     * @param int $role_id
     * @return bool
     */
    public function hasRole(int $role_id): bool
    {
        return $this->roles->where("role_id", "=", $role_id)->first() !== null;
    }

    /**
     * @param int $role_id
     * @return bool
     */
    public function deleteRole(int $role_id): bool
    {
        return $this->roles()->where("role_id", "=", $role_id)->delete();
    }

    public function isApproved()
    {
        return $this->account_status_id === AccountStatus::APPROVED;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->preferred_timezone_tzab;
    }

    /**
     * @return Builder|Model|object|null|User
     */
    public static function getAgent(string $email): ?User
    {
        return User::query()
            ->whereHas("roles", function (Builder $builder) {
                $builder->where("user_role.role_id", Role::Agent);
            })
            ->where("email", $email)
            ->first();
    }

    /**
     * @param string $email
     * @return User|Builder|Model|object|null
     */
    public static function getByEmail(string $email)
    {
        return User::query()
            ->where("email", $email)
            ->first();
    }

    /**
     * @param string $email
     * @return User|Builder|Model|object|null
     */
    public static function getByEmailWithTrashed(string $email)
    {
        return User::withTrashed()
            ->where("email", $email)
            ->first();
    }

    /**
     * @param int $id
     * @return User|Builder|Model|object|null
     */
    public static function getById(int $id)
    {
        return User::query()
            ->where("id", $id)
            ->first();
    }

    /**
     * @return Builder|Model|object|null|User
     */
    public static function getConceirge(string $email)
    {
        return User::query()
            ->whereHas("roles", function (Builder $builder) {
                $builder->where("user_role.role_id", Role::Concierge);
            })
            ->where("email", $email)
            ->first();
    }

    /**
     * @return Builder
     */
    public static function Agents()
    {
        return User::query()
            ->whereHas("roles", function (Builder $builder) {
                $builder->where("user_role.role_id", Role::Agent);
            });
    }

    /**
     * @return Builder|Model|object|null|User
     */
    public static function getImpersonableAgent(string $impersonation_token)
    {
        return User::query()
            ->whereHas("roles", function (Builder $builder) {
                $builder->where("user_role.role_id", Role::Agent);
            })
            ->where("impersonation_token", $impersonation_token)
            ->where("impersonation_token_expiry", '>=', Carbon::now())
            ->first();
    }

    /**
     * @param UploadedFile $profile_image
     * @return $this
     */
    public function storeNewProfilePicture(UploadedFile $profile_image): User
    {
        $relativePath = $this->profile_image_url ? self::getStorageRelativePath($this->profile_image_url) : null;

        if (Storage::cloud()->exists($relativePath)) Storage::cloud()->delete($relativePath);

        $relativePath = $this->getProfilePictureRelativePath();
        Storage::cloud()->put($relativePath, $profile_image->getContent());
        $this->profile_image_url = Storage::cloud()->url($relativePath);

        return $this;
    }

    public function master_sub_account()
    {
        return $this->hasOne(MasterSubAccount::class);
    }

    public function master_sub_accounts()
    {
        return $this->hasMany(MasterSubAccount::class, 'created_by_id');
    }

    /**
     * This contains last working subscription
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user_stripe_account()
    {
        return $this->hasOne(UserStripeAccount::class);
    }

    /**
     * This contains last working subscription
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function average_feedback()
    {
        return $this->hasOne(CopilotAverageFeedback::class, 'copilot_id');
    }

    /**
     * This contains last working subscription
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ratings()
    {
        return $this->hasMany(CopilotRatingsCategorized::class, 'copilot_id');
    }

    /**
     * get last subscription real state
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function last_subscription()
    {
        return $this->hasOne(StripeSubscriptionHistory::class)->latest();
    }

    /**
     * @return bool
     */
    public function getIsGoogleAuthValidatedAttribute(): bool
    {
        return $this->google_authentication_token != null;
    }

    /**
     * @inheritDoc
     */
    public function presentForDev(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            "first_name" => $this->first_name,
            "last_name" => $this->last_name,
            "profile_image_url" => $this->profile_image_url,
            "phone" => $this->phone,
            "location" => $this->location,
            "address_line1" => $this->address_line1,
            "address_line2" => $this->address_line2,
            "country" => $this->country->description,
            "state" => $this->state,
            "city" => $this->city,
            "zip" => $this->zip,
            "agency_name" => $this->agency_name,
            "job_title" => $this->job_title,
            "preferred_partner_id"=>$this->preferred_partner_id,
            "preferred_timezone_tzab" => $this->preferred_timezone_tzab,

            "default_currency_id" => $this->default_currency_id,
            "default_currency" => optional($this->currency_type)->description,

            "has_valid_license" => $this->has_valid_license,
            "linkedin_url" => $this->linkedin_url,

            "agency_usage_mode_id" => $this->agency_usage_mode_id,
            "agency_usage_mode" => optional($this->agency_usage_mode)->description,

            "is_google_auth_validated" => $this->is_google_auth_validated,

            'stripe_connect' =>$this->user_stripe_account?optional($this->user_stripe_account)->getStripeConnectAccountStatus():'',

            "roles" => $this->roles->map->presentForDev(),
            "subscription"=>$this->user_stripe_account?$this->getUserSubscription($this->user_stripe_account):null,
            "current_subscription" => $this->last_subscription,
            "account_status_id" => $this->account_status_id,
            "account_status" =>$this->account_status?optional($this->account_status)->description:'',
            "itinerary_theme" => $this->default_itinerary_theme?optional($this->default_itinerary_theme)->presentForDev():[],
            "master_account" => !$this->master_sub_account ? null :
                [
                    "title" => $this->master_sub_account?$this->master_sub_account->master_account->title:null,
                    "master_sub_account_id" =>$this->master_sub_account?$this->master_sub_account->id:null,
                    "master_account_id" => $this->masterAccountId(),
                    "is_owner" => $this->isMasterAccountOwner(),
                ],

            "co_pilot_duties" =>$this->copilot_duties?$this->copilot_duties->map->presentForDev():[],

            "copilot_info" =>$this->copilot_info?optional($this->copilot_info)->presentForDev():[],

            "client_info" => optional($this->client_info)->only([
                'favorite_vacation_spot',
                'preferred_cuisine',
                'allergies',
            ]),
            "unReadMessagesCount"=>AdvisorChat::with('advisor_request')->where('receiver_id',$this->id)->where('seen',0)->count(),
            "testimonials"=>$this->getTestimonials(),
            "isExpired" => $this->isSubscriptionExpired(),
            "hourly_rate" => $this->hourly_rate,
        ];
    }

    public function isSubscriptionExpired()
    {
        $latestSubscription = $this->last_subscription()->first();
        // dd($latestSubscription);
        if (!$latestSubscription) {
            // no subscription found, assume expired
            return true;
        }

        return Carbon::now()->gt($latestSubscription->current_period_end) ? true : false;
    }

    public function isFavorite()
	{
		return $this->hasMany(FavoriteCopilot::class,'copilot_id');
	}

    public function itineraries()
    {
        return $this->hasMany(Itinerary::class);
    }

    public function roles()
    {
        return $this->hasMany(UserRole::class);
    }

    public function advisor_requests()
    {
        return $this->hasMany(AdvisorRequest::class, 'created_by_id');
    }

    public function copilot_duties()
    {
        return $this->hasMany(CopilotDuty::class, 'copilot_id');
    }

    public function copilot_info()
    {
        return $this->hasOne(CopilotInfo::class, 'copilot_id');
    }

    public function rating()
    {
        return $this->hasOne(CopilotAverageFeedback::class, 'copilot_id');
    }

    public function default_itinerary_theme()
    {
        return $this->hasOne(DefaultItineraryTheme::class);
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function last_stripe_connect_reminder()
    {
        return $this->hasOne(StripeConnectReminder::class)
            ->latest('created_at');
    }

    /**
     *
     * @return total archived request by this copilot
     */
    public function archived_count($copilot_id)
    {
        return AdvisorRequestArchived::where('copilot_id',$copilot_id)->count();
    }

    /**
     * This will create a reset token that can be used
     * for validations on this field password_reset_token
     *
     * @return $this
     */
    public function createValidationToken(): User
    {
        $code = (string)rand(100000, 999999);

        $this->update([
            'password_reset_token' => encrypt($code),
            'password_reset_token_expiry' => Carbon::now()->addHours(2)
        ]);

        // Send Email
        Mail::send(new ResetCodeMail($this));

        return $this;
    }

    /**
     * It will validate against the reset token if provided
     * else it will just check the expiry on available one
     *
     * @param string|null $password_reset_token
     * @return bool
     */
    public function hasValidationToken(?string $password_reset_token = null): bool
    {
        $validTokenAvailable = $this->password_reset_token_expiry && $this->password_reset_token_expiry->greaterThanOrEqualTo(Carbon::now());
        return $password_reset_token ?
            $validTokenAvailable && (decrypt($this->password_reset_token) === $password_reset_token) : $validTokenAvailable;
    }

    public function getUserSubscription($stripeAccount){
        return StripeSubscriptionHistory::where('user_id',$this->id)->orderBy("id","desc")->first();
        // $result = array();
        // $result['amount'] = 0;
        // $result['plan'] = "No";
        // $result['ends_at']="";
        // $result['nextPaymentDate'] = "";

        // try {
        //     $SDK = new StripeSubscriptionSDK();
        // $customerID = ($stripeAccount)?$stripeAccount->stripe_customer['id']:'';
        // if(!$customerID):
        //     return null;
        // endif;
        // $subscription =  $SDK->fetchCustomerSubscriptions($customerID);

        // if($subscription):
        //    $result['amount'] = isset($subscription[0]['plan'])?($subscription[0]['plan']->amount/100):0;
        //    $result['plan'] = isset($subscription[0]['plan'])?strtoupper($subscription[0]['plan']->interval):'';
        // endif;

        // $InvoiceJsonString= $SDK->upcomingInvoice($customerID);

        // if($InvoiceJsonString):
        //     // $InvoiceArray = json_encode($InvoiceJsonString,true);
        //     // dd($InvoiceJsonString['period_start']);
        //     $nextPaymentdate = isset($InvoiceJsonString['period_start'])?date('M d, Y',strftime($InvoiceJsonString['period_start'])):'';
        //     // dd(strftime('%m-%d-%Y', $InvoiceJsonString['period_start']));
        //     $result['ends_at']=  isset($InvoiceJsonString['period_end'])?date('M d, Y',strftime($InvoiceJsonString['period_end'])):'';;
        //     $result['nextPaymentDate'] = "Your next payment is on <b>".$nextPaymentdate."</b>";

        //  endif;

        // } catch (\Throwable $th) {
        //     //throw $th;
        // }


        return $result;

    }

    public function getTestimonials(){

        return Testimonials::all();
    }


    /**
     * password_reset_token
     *
     * @return $this
     */
    public function clearValidationToken(): User
    {
        $this->update([
            "password_reset_token" => null,
            "password_reset_token_expiry" => Carbon::now()
        ]);
        return $this;
    }
}
