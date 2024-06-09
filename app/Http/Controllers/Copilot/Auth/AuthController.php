<?php

namespace App\Http\Controllers\Copilot\Auth;

use App\Events\UserStatusChangedEvent;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Responses\ExpectionFailedResponse;
use App\Http\Responses\OkResponse;
use App\Http\Responses\PreConditionFailedResponse;
use App\ModelsExtended\AccountStatus;
use App\ModelsExtended\CopilotInfo;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;

class AuthController extends \App\Http\Controllers\Auth\AuthController
{
    protected int $TARGETED_ROLE = Role::Concierge;

    public function getCommonRules()
    {
        return array_merge(
            Arr::only( parent::getCommonRules(),
            [
                'first_name',
                'last_name',
                'phone',
//                'address',
                'linkedin_url',
                'email',
            ]
        ),
        [
            'profile_image' => 'nullable',    // allow file as url or blob

            'timezone_offset_tzab' => 'required|string|exists:db_timezone,offset_tzab',
            //'country_id' => 'required|numeric|exists:countries,id',
            'city' => 'required|string|max:100',

            // info
          //  'how_to_fulfill' => 'required|string|min:1|max:10000',
           // 'free_time_recommendations' => 'required|string|min:1|max:10000',
//            'strengths' => 'nullable|string|min:1|max:10000',
//            'weaknesses' => 'nullable|string|min:1|max:10000',
            //'confidential_handling' => 'required|string|min:1|max:10000',
            //'experience' => 'required|string|min:1|max:10000',
           // 'contact_references' => 'required|string|min:1|max:10000',
           // 'other_info' => 'filled|string|min:1|max:10000',
           // 'bio' => 'filled|string|min:1|max:5000',

            // 'resume' => 'filled|mimes:pdf|max:20000',    // 20MB

            // duties
           // 'copilot_duties' => 'required|array|min:1',
          //  'copilot_duties.*' => 'required|exists:advisor_request_type,id'
        ]
       );
    }

    /**
     * @param Request $request
     * @return User
     * @throws ValidationException
     */
    private function createAccount(Request $request ): User
    {
        $this->validateCreateUserRequest();

        return DB::transaction(function ( ) use ( $request ){

            $user = $this->createAccountFromRequest(
                $request, AccountStatus::PENDING_APPROVAL, $this->TARGETED_ROLE, null
            );

            //(new ProfileController())->updateProfilePicture( $user );
            // $user->update([
            //     "preferred_timezone_tzab" => $request->input('timezone_offset_tzab'),
            //     "country_id" => $request->input('country_id'),
            //     "city" => $request->input('city'),
            // ]);
            // needed to persist the picture

            // $user->copilot_duties()->createMany(  array_map( function ( $duty ) {
            //     return [ 'advisor_request_type_id' => $duty, ];
            // }, $request->input( 'copilot_duties' ) )  );

            // $user->copilot_info()->create([
            //     'how_to_fulfill' => $request->input(  'how_to_fulfill' ),
            //     'free_time_recommendations' => $request->input(  'free_time_recommendations' ),
            //     'strengths' => $request->input(  'strengths' ),
            //     'weaknesses' => $request->input(  'weaknesses' ),
            //     'confidential_handling' => $request->input(  'confidential_handling' ),
            //     'experience' => $request->input(  'experience' ),
            //     'contact_references' => $request->input(  'contact_references' ),
            //     'other_info' => $request->input(  'other_info' ),
            //     'bio' => $request->input(  'bio' ),
            //     'resume_relative_url' => $request->hasFile('resume') ? CopilotInfo::saveImageOnCloud( $request->file(  'resume' ) , $user ) : null
            // ]);

            return $user;
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
            event(new UserStatusChangedEvent( $this->createAccount($request) ));

            //  Return
            return new OkResponse( message( "Please, wait till your profile is approved." ) );

        } catch (ValidationException $e) {
            return new PreConditionFailedResponse( $e->errors() );
        } catch (\Exception $exception )
        {
            return new ExpectionFailedResponse( errorKeyMessage( $exception->getMessage()  )  );
        }
    }

}
