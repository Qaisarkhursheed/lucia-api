<?php

namespace App\Http\Controllers\Admin\Agents;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Enhancers\PaginableTraitController;
use App\Http\Controllers\Enhancers\YajraPaginableTraitController;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use App\ModelsExtended\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ListAgentsController extends Controller implements \App\Http\Controllers\Enhancers\IYajraEloquentResultProcessorInterface
{
    use PaginableTraitController, YajraPaginableTraitController;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|null|User
     */
    private $user;

    public function __construct()
    {
        $this->user = auth()->user();
        $this->orderByColumnName = 'name';
    }

    /**
     * @return OkResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function basicPagination()
    {
        return new OkResponse($this->paginate($this->filterQuery($this->getQuery())));
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function gridPagination()
    {
        return $this->paginateYajra($this);
    }

    /**
     * @return Builder
     */
    protected function getQuery(): Builder
    {
        return User::with("roles")
            ->whereHas("roles", function (Builder $builder) {
                $builder->where("user_role.role_id", Role::Agent);
            })
            ->when($this->user->isLoggedInAsMasterAccount(), function ( Builder $builder){
                $builder->whereHas( "master_sub_account", function (Builder $builder){
                   $builder->where( "master_sub_account.master_account_id", $this->user->masterAccountId() );
                });
            });
    }

    /**
     * @param Builder $query
     * @return Builder|mixed
     */
    protected function filterQuery(Builder $query)
    {
        $query->when($this->search, function (Builder $builder) {
            $search = $this->search;
            $builder->where( function (Builder $builder) use ( $search ) {
                $builder->where("name", 'like', "%$search%")
                    ->orWhere("email", 'like', "%$search%");
            });
        });

        //if custom field search added
        $query->when(request()->has('global-search'), function (Builder $builder) {
            $search = request('global-search');
            $builder->where( function (Builder $builder) use ( $search ) {
                $builder->where("name", 'like', "%$search%")
                    ->orWhere("email", 'like', "%$search%");
            });
        });

        $query->when(request()->has('agent_type'), function (Builder $builder) {
            if(request('agent_type') == 1):
            $builder->where( function (Builder $builder){
                $builder->where("preferred_partner_id", null);
            });
            elseif(request('agent_type') == 2):
                $builder->where( function (Builder $builder){
                    $builder->where("preferred_partner_id",'<>', null);
                });
            endif;
        });

        $query->when(request()->has('preferred_partner_id'), function (Builder $builder) {
            if(request('preferred_partner_id')):
                $builder->where( function (Builder $builder){
                    $builder->where("preferred_partner_id", request('preferred_partner_id'));
                });
            endif;
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
                'preferred_partner_id' => ($user->partner)?$user->partner->company_name:'Normal',
                'preferred_timezone_tzab' => $user->preferred_timezone_tzab,
                'has_valid_license' => $user->agentRole()->has_valid_license,
                'subscription_plan_name' => $user->agentRole()->getSubscriptionPlanNickName(),
                'roles' => $user->roles->map(fn(UserRole $userRole) => $userRole->role->description)->implode(", "),
                'created_at' => $user->created_at,
                'master_account_title' => optional($user->master_account)->title,
                'subscription' => isset($user->last_subscription) ? json_decode($user->last_subscription, true): null,
            ];
        })->toArray();
    }
}
