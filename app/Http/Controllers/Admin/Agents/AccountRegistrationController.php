<?php

namespace App\Http\Controllers\Admin\Agents;

use App\Http\Controllers\Auth\AuthController;
use App\ModelsExtended\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AccountRegistrationController extends AuthController
{
    /**
     * @var Authenticatable|User
     */
//    private $user;

    public function __construct()
    {
        parent::__construct();

//        $this->user = auth()->user();
    }

    public function getCommonRules()
    {
        return Arr::except( parent::getCommonRules(), [ 'agency_usage_mode_id'] );
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function register(Request $request)
    {
        throw new \Exception("This feature has been temporally disabled!");
//        return DB::transaction(function () use($request){
//            $agent = $this->createAgentAccountFromRequest(
//                $request,
//                AccountStatus::APPROVED,
//                AgencyUsageMode::LUCIA_EXPERIENCE
//            );
//
//            if( $this->user->isLoggedInAsMasterAccount() )
//                $agent->master_sub_account()->create([
//                    'master_account_id' => $this->user->masterAccountId(),
//                    'created_by_id' => $this->user->id,
//                ]);
//
//            // send email
//            Mail::send( new AccountInvitationMail( $agent ) );
//
//            return new OkResponse( $agent );
//        });
    }
}
