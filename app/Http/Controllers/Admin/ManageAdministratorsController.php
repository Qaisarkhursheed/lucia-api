<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Enhancers\CRUDEnabledController;
use App\Http\Controllers\Enhancers\YajraPaginableTraitController;
use App\Http\Responses\OkResponse;
use App\Mail\Auth\AdminAccountInvitationMail;
use App\ModelsExtended\AccountStatus;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use App\ModelsExtended\UserRole;
use App\Rules\PhoneNumberValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * @property User $model
 */
class ManageAdministratorsController extends CRUDEnabledController implements \App\Http\Controllers\Enhancers\IYajraEloquentResultProcessorInterface
{
    use YajraPaginableTraitController;

    public function __construct()
    {
        parent::__construct('admin_user_id', "users.id");

        $this->orderByColumnName = 'name';
    }

    public function fetchAll()
    {
        return $this->paginateYajra($this);
    }

    /**
     * @return array|void
     */
    public function getCommonRules()
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => ['required', 'max:30', new PhoneNumberValidationRule()],
            'address' => 'required|string|max:100',
            'email' => 'required|email|max:200',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getQuery(): Builder
    {
        return $this->getDataQuery();
    }

    /**
     * @param Builder $query
     * @return Builder|mixed
     */
    protected function filterQuery(Builder $query)
    {
        return $query->when($this->search, function (Builder $builder) {
            $search = $this->search;
            $builder->where(function (Builder $builder) use ($search) {
                $builder->where("name", 'like', "%$search%")
                    ->orWhere("email", 'like', "%$search%");
            });
        });
    }

    /**
     * @inheritDoc
     */
    public function store(Request $request)
    {
        $this->validatedRules($this->getCommonRules());

        $authController = new AuthController();
        $user = $authController->createAccountFromRequest(
            $request,
            AccountStatus::APPROVED,
            Role::Administrator,
            null
        );

        // send email
        Mail::send(new AdminAccountInvitationMail($user));

        return new OkResponse($user->refresh()->presentForDev());
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return User::with("roles", "account_status")
            ->whereHas("roles", function (Builder $builder) {
                $builder->where("user_role.role_id", Role::Administrator);
            });
    }

    public function fetch()
    {
        return $this->model->presentForDev();
    }

    /**
     * @return OkResponse
     * @throws \App\Exceptions\RecordNotFoundException
     */
    public function delete()
    {
        if (intval($this->routeParameterValue) === User::DEFAULT_ADMIN)
            throw new \Exception("You can not delete the default administrator!");

        // You can not delete yourself
        if ( $this->model->id == auth()->id() )
            throw new \Exception( "You can not delete yourself!" );

        return DB::transaction(function (){
            // first delete role
            $this->model->deleteRole( Role::Administrator );
            $this->model->refresh();

            // if no more roles, delete account
            if( !$this->model->roles->count() ) return parent::delete();

            return new OkResponse($this->model->presentForDev());
        });
    }

    /**
     * @param User[]|Collection $result
     * @return array
     */
    public function processYajraEloquentResult($result): array
    {
        return $result->map(function (User $user) {
            return [
                'name' => $user->name,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'profile_image_url' => $user->profile_image_url,
                'phone' => $user->phone,
                'location' => $user->location,
                'iata_number' => $user->iata_number,
                'agency_name' => $user->agency_name,
                'job_title' => $user->job_title,
                'default_currency' => $user->currency_type->description,
                'user_id' => $user->id,
                'account_status_id' => $user->account_status_id,
                'account_status' => $user->account_status->description,
                'preferred_timezone_tzab' => $user->preferred_timezone_tzab,
                'roles' => $user->roles->map(fn(UserRole $userRole) => $userRole->role->description)->implode(", "),
                'created_at' => $user->created_at,
            ];
        })->toArray();
    }
}
