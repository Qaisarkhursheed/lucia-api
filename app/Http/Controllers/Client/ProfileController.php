<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\Role;
use App\Rules\PhoneNumberValidationRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends \App\Http\Controllers\Agent\ProfileController
{
    public function update()
    {
        $this->validatedRules([
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'profile_image' => 'nullable',    // allow file as url or blob
        ]);

        if( \request( 'first_name' ) ) $this->user->first_name = \request( 'first_name' );
        if( \request( 'last_name' ) ) $this->user->last_name = \request( 'last_name' );

        $this->updateProfilePicture($this->user);

        $this->user->save();

        return $this->me();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function updatePhone(Request $request)
    {
        $this->validatedRules([
            'current_phone' =>  [ 'required', 'max:30', new PhoneNumberValidationRule() ],
            'new_phone' => [ 'required', 'max:30', new PhoneNumberValidationRule() ],
            'reset_code' => 'required|string',
        ]);

        if( !$this->user->hasValidationToken(  $request->input( "reset_code") ) )
            throw new \Exception( "Invalid code or expired code!");

        if( $this->user->phone !== $request->input( "current_phone"))
            throw new \Exception( "Old Phone does not match!");

        $this->user->phone = \request( 'new_phone' );
        $this->user->clearValidationToken();

        $this->user->save();

        return $this->me();

    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function updateEmail(Request $request)
    {
        $this->validatedRules([
             'current_email' => 'required|exists:users,email',
             'new_email' => 'required|unique:users,email',
            'reset_code' => 'required|string',
        ]);

        if( !$this->user->hasValidationToken(  $request->input( "reset_code") ) )
                throw new \Exception( "Invalid code or expired code!");

        if( $this->user->email !== $request->input( "current_email"))
                throw new \Exception( "Old email does not match!");

        $this->user->email = \request( 'new_email' );
        $this->user->clearValidationToken();

        $this->user->save();

        return $this->me();
    }

    public function createResetToken()
    {
         return new OkResponse([
            "expires_in_minutes" => Carbon::now()->diffInMinutes( $this->user->createValidationToken()->password_reset_token_expiry )
        ]);
    }

    /**
     * Deletes the account
     * @return OkResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function deleteMe(Request $request)
    {
        $this->validatedRules([
            'password' => 'required|string|max:20',
        ]);

        if (!Hash::check($request->input('password'), $this->user->getAuthPassword()))
            throw new \Exception("Your current password is wrong!");


        return DB::transaction(function () {

            // first delete role
            $this->user->deleteRole( Role::Client );
            $this->user->refresh();

            // if no more roles, delete account
            if( !$this->user->roles->count() ) {
                $this->user->delete();
                return new OkResponse();
            }

            return new OkResponse($this->user->presentForDev());

        });
    }
}
