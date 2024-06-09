<?php

namespace App\Http\Controllers\Admin\Copilots;

use App\Events\UserStatusChangedEvent;
use App\Http\Controllers\Controller;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\AccountStatus;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountStatusController extends Controller
{
    /**
     * @var User|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    private $agent;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|User
     */
    private $user;

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function __construct(Request $request)
    {
        $this->validatedRules([
            'email' => 'required|email|max:200|exists:users,email',
        ]);

        $this->agent = User::getConceirge($request->input('email'));
        if (!$this->agent) throw new \Exception("Please, pass in a valid concierge's email");

        $this->user = auth()->user();
        \App\Http\Controllers\Admin\Agents\AccountStatusController::verifyAgentIsInMyAccount( $this->user, $this->agent );
    }

    /**
     * @return OkResponse
     * @throws ValidationException
     */
    public function fetch()
    {
        return new OkResponse($this->agent->presentForDev());
    }

    /**
     * @return OkResponse
     * @throws ValidationException
     */
    public function approve()
    {
        $this->setStatus(AccountStatus::APPROVED);
        return new OkResponse();
    }

    /**
     * @return OkResponse
     * @throws ValidationException
     */
    public function reject()
    {
        $this->setStatus(AccountStatus::REJECTED);
        return new OkResponse();
    }

    /**
     * @throws \Exception
     */
    public function deleteAccount()
    {
        return DB::transaction(function () {

            // first delete role
            $this->agent->deleteRole( Role::Concierge );
            $this->agent->copilot_duties()->delete();
            $this->agent->copilot_info()->delete();

            $this->agent->refresh();

            // if no more roles, delete account
            if( !$this->agent->roles->count() ) {
                $this->agent->delete();
                return new OkResponse();
            }

            return new OkResponse($this->agent->presentForDev());
        });

    }

    /**
     * @param int $account_status_id
     * @throws \Exception
     */
    private function setStatus(int $account_status_id)
    {
        if ($this->agent->account_status_id === $account_status_id)
            throw new \Exception("The user already has same status!");

        $this->agent->update([
            'account_status_id' => $account_status_id
        ]);

        // raise event
        event(new UserStatusChangedEvent($this->agent));
    }

}
