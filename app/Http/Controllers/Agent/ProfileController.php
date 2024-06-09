<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Support\Str;
use App\ModelsExtended\User;
use Illuminate\Http\Request;
use App\Http\Responses\OkResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Rules\PhoneNumberValidationRule;
use App\Repositories\Stripe\StripeReportSDK;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|null | User
     */
    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }
    /**
     * Get my details
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function me()
    {
        return User::with( "account_status", "currency_type", "default_itinerary_theme" )->find( auth()->id() )->presentForDev();
    }

    /**
     * Update my details
     */
    public function update()
    {
        $this->validatedRules([
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => [ 'nullable', 'max:30', new PhoneNumberValidationRule() ],
            'location' => 'nullable|string|max:100',
            //'email' => 'nullable|unique:users,email',
            'email' => 'nullable|email',
            'default_currency_id' => 'nullable|exists:currency_types,id',
            'job_title' => 'nullable|string|max:50',
            'zip' => 'nullable|string|max:10',
            //'profile_image' => 'nullable|image|max:20000',    // 20KB
            'profile_image' => 'nullable',    // allow file as url or blob
        ]);

        if( \request( 'email' ) ) $this->user->email = \request( 'email' );
        if( \request( 'first_name' ) ) $this->user->first_name = \request( 'first_name' );
        if( \request( 'last_name' ) ) $this->user->last_name = \request( 'last_name' );
        if( \request( 'phone' ) ) $this->user->phone = \request( 'phone' );
        if( \request( 'zip' ) ) $this->user->zip = \request( 'zip' );
        if( \request( 'job_title' ) ) $this->user->job_title = \request( 'job_title' );
        if( \request( 'default_currency_id' ) ) $this->user->default_currency_id = \request( 'default_currency_id' );
        if( \request( 'location' ) ) $this->user->location = \request( 'location' );

        if(\request('password') &&  \request('old_password')):

            if (!Hash::check(\request('old_password'), auth()->user()->getAuthPassword())){
            throw new \Exception("Your current password is wrong!");
            }

            $this->user->update(["password" => app('hash')->make(\request( 'password' ))]);

        else:
            $this->updateProfilePicture($this->user);
            $this->user->save();
        endif;

        return $this->me();
    }

    /**
     * You need to still call update on user object
     * It doesn't throw exception
     * @return void
     */
    public function updateProfilePicture(User $user)
    {
        try {
            if( \request(  )->hasFile( 'profile_image' ) )
            {
                $user->storeNewProfilePicture( \request( )->file( 'profile_image' ) );
            }elseif( \request(  )->has( 'profile_image' )  ) {
                $profile_image =  \request(   'profile_image' );
                if( $profile_image && Str::of($profile_image)->startsWith("http") )
                {
                    $user->profile_image_url = $profile_image;
                }
            }
        }catch (\Exception $exception){
            Log::error($exception->getMessage(), $exception->getTrace());
        }
    }

    /**
     * Update my details
     */
    public function updateItineraryDesign()
    {
        $this->validatedRules([
            'property_design_id' => 'filled|exists:property_design,id',
            'itinerary_logo' => 'filled|image|max:20000',    // 20MB
        ]);

        $default_itinerary_theme = $this->user->default_itinerary_theme;
        if( ! $default_itinerary_theme ) $default_itinerary_theme = $this->user->default_itinerary_theme()->create();

        if( \request( 'property_design_id' ) ) $default_itinerary_theme->property_design_id = \request( 'property_design_id' );

        if( \request(  )->hasFile( 'itinerary_logo' ) )
        {
            $default_itinerary_theme->storeNewItineraryLogo( \request( )->file( 'itinerary_logo' ) );
        }

        $default_itinerary_theme->update();

        return $this->me();
    }

    public function invoiceList(Request $request){

        $result = array();

        $Last30PaymentByUser = DB::table('advisor_request_payment')
                    ->select('amount','request_title','stripe_charge_id',DB::raw('DATE_FORMAT(advisor_request_payment.created_at, "%b, %d %Y") as created_at'))
                    ->join('advisor_request', 'advisor_request.id', '=', 'advisor_request_payment.advisor_request_id')
                    ->whereNull('advisor_request_payment.stripe_refund_id')
                    ->whereNotNull('advisor_request_payment.stripe_charge_id')
                    ->where('advisor_request.created_by_id',$this->user->created_by_id)
                    ->orderBy('created_at', 'desc')
                    ->limit(30)
                    ->get();


        $Last30Tasks = DB::table('advisor_request')
                    ->select('request_title',DB::raw('DATE_FORMAT(created_at, "%b, %d %Y") as created_at'),'advisor_request_status.description','total_amount as amount')
                    ->join('advisor_request_status', 'advisor_request.advisor_request_status_id', '=', 'advisor_request_status.id')
                    ->where('created_by_id',$this->user->created_by_id)
                    ->orderBy('created_at', 'desc')
                    ->limit(30)
                    ->get();

                    $result['payments'] = $Last30PaymentByUser;
                    $result['tasks'] = $Last30Tasks;

        return $result;
    }

    /**
     * Update my password
     * @param Request $request
     * @return OkResponse
     * @throws ValidationException
     */
    public function updatePassword( Request $request )
    {
        $this->validatedRules([
            'current_password' => 'required|string|max:20',
            'password' => AuthController::getPasswordRule(),
        ]);

        if (!Hash::check($request->input('current_password'), auth()->user()->getAuthPassword()))
            throw new \Exception("Your current password is wrong!");

        auth()->user()
            ->update([
                "password" => Hash::make($request->input('password'))
            ]);

        return new OkResponse();
    }
}
