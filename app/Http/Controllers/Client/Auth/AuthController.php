<?php

namespace App\Http\Controllers\Client\Auth;

use App\Http\Controllers\Client\ProfileController;
use App\Http\Responses\ExpectionFailedResponse;
use App\Http\Responses\OkResponse;
use App\Http\Responses\PreConditionFailedResponse;
use App\Mail\Auth\ClientAccountApprovedMail;
use App\ModelsExtended\AccountStatus;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;

class AuthController extends \App\Http\Controllers\Auth\AuthController
{

    protected int $TARGETED_ROLE = Role::Client;

    public function getCommonRules()
    {
        return array_merge(
            Arr::only( parent::getCommonRules(),
            [
                'first_name',
                'last_name',
                'phone',
                'email',
                'state',
                'city',
                'zip',
            ]
        ),
        [
            'address_line1' => 'required|string|max:150',
            'address_line2' => 'nullable|string|max:150',
            'country_id' => 'required|numeric|exists:countries,id',

            'favorite_vacation_spot' => 'nullable|string|max:500',
            'preferred_cuisine' => 'nullable|string|max:500',
            'allergies' => 'nullable|string|max:500',
            'password' => self::getPasswordRule(),
            'profile_image' => 'nullable',    // allow file as url or blob
        ]
       );
    }

    /**
     * For client creation, it will override the password because you have to specify on creation
     *
     * @param Request $request
     * @return User
     * @throws ValidationException
     */
    private function createAccount(Request $request ): User
    {
        $this->validateCreateUserRequest();

        return DB::transaction(function ( ) use ( $request ){

            $user = $this->createAccountFromRequest(
                $request, AccountStatus::APPROVED, $this->TARGETED_ROLE, null
            );

            (new ProfileController())->updateProfilePicture( $user );
            $user->update([
                "address_line1" => $request->input('address_line1'),
                "address_line2" => $request->input('address_line2'),
                "country_id" => $request->input('country_id'),
                "state" => $request->input('state'),
                "city" => $request->input('city'),
                "zip" => $request->input('zip'),
                "password" => Hash::make($request->input('password'))
            ]);

            $user->client_info()->create([
                "favorite_vacation_spot" => $request->input('favorite_vacation_spot'),
                "preferred_cuisine" => $request->input('preferred_cuisine'),
                "allergies" => $request->input('allergies'),
            ]);

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

            $user = $this->createAccount($request);

            // send email
            Mail::send( new ClientAccountApprovedMail( $user ) );

            // login user straight away
            return $this->loginWithCredentials( $user->email , $request->input( 'password' ), $this->TARGETED_ROLE );

        } catch (ValidationException $e) {
            return new PreConditionFailedResponse( $e->errors() );
        } catch (\Exception $exception )
        {
            return new ExpectionFailedResponse( errorKeyMessage( $exception->getMessage()  )  );
        }
    }

}
