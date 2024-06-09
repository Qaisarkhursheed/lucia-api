<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Enhancers\CRUDEnabledController;
use App\Http\Controllers\Enhancers\YajraPaginableTraitController;
use App\Http\Responses\OkResponse;
use App\Mail\Auth\AdminAccountInvitationMail;
use App\ModelsExtended\AccountStatus;
use App\ModelsExtended\AgencyUsageMode;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use App\ModelsExtended\UserRole;
use App\Rules\PhoneNumberValidationRule;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Allows Lucia Admin to Create Master Admins
 * @property User $model
 */
class ManageMasterAccountsController extends CRUDEnabledController implements \App\Http\Controllers\Enhancers\IYajraEloquentResultProcessorInterface
{
    use YajraPaginableTraitController;

    /**
     * @var Authenticatable|null|User
     */
    private $user;

    public function __construct()
    {
        $this->user = auth()->user();
        parent::__construct( 'master_user_id', "users.id" );
        $this->orderByColumnName = 'name';
    }

    public function fetchAll()
    {
        return $this->paginateYajra( $this  );
    }

    /**
     * Blocking master accounts from same email for now
     *
     * @return array|void
     */
    public function getCommonRules()
    {
       return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'title' => 'required|string|max:150',
            'phone' => [ 'required', 'max:30', new PhoneNumberValidationRule() ],
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
            $builder->where( function (Builder $builder) use ( $search ) {
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

        return DB::transaction(function () use ($request){

            $authController = new AuthController();
            $agent = $authController->createAccountFromRequest($request,
                AccountStatus::APPROVED,
                Role::MasterAccount,
                AgencyUsageMode::LUCIA_EXPERIENCE
            );

            if( $agent->master_sub_account )
                throw new \Exception("A user can only be in one single master account!");

            if( $this->user->isLoggedInAsMasterAccount() )
            {
                //I am creating sub-account master account
                $agent->master_sub_account()->create([
                    'master_account_id' => $this->user->masterAccountId(),
                    'created_by_id' => $this->user->id,
                ]);

                $this->user->master_account->update([
                    'title' => $request->input('title'),
                ]);

            }else{

                // am creating new master accounts
                $agent->master_account()->create([
                    'title' => $request->input('title'),
                    'created_by_id' => $this->user->id,
                ]);

                // as the owner also as a subaccount under the company
                $agent->master_sub_account()->create([
                    'master_account_id' => $agent->refresh()->master_account->id,
                    'created_by_id' => $this->user->id,
                ]);

            }


            // send email
            Mail::send( new AdminAccountInvitationMail( $agent ) );

            return new OkResponse( $agent );
        });
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return User::query()
            ->whereHas("roles", function (Builder $builder) {
                $builder->where("user_role.role_id", Role::MasterAccount);
            })
            ->when($this->user->isLoggedInAsMasterAccount(), function ( Builder $builder){
                $builder->whereHas( "master_sub_account", function (Builder $builder){
                    $builder->where( "master_sub_account.master_account_id", $this->user->masterAccountId() );
                })
                 // Ability to see myself but not deletable
                ->orWhere( "users.id", $this->user->id );
            });
    }

    /**
     * @return OkResponse
     * @throws RecordNotFoundException
     * @throws \Exception
     */
    public function delete()
    {
        // if this is the creator of the master account
        // if there are other accounts other this account, do not delete

        if ( $this->model->isMasterAccountOwner() && $this->model->master_account->master_sub_accounts->count() > 1 )
            throw new \Exception( "You can not delete the master account administrator if there are more accounts under account!" );

        // You can not delete yourself
        if ( $this->model->id == $this->user->id )
            throw new \Exception( "You can not delete yourself!" );


        return DB::transaction(function () {

            // first delete role
            $this->model->deleteRole( Role::MasterAccount );
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
                'master_account_title' => $user->master_sub_account->master_account->title,
                'master_account_owner' => $user->master_sub_account->getIsAccountOwnerAttribute(),
            ];
        })->toArray();
    }
}
