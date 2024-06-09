<?php

namespace App\Http\Controllers\Admin\Clients;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Enhancers\YajraPaginableTraitController;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use App\ModelsExtended\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ClientAccountsController  extends Controller implements \App\Http\Controllers\Enhancers\IYajraEloquentResultProcessorInterface
{
    use YajraPaginableTraitController;

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
     * @return array|Builder[]|\Illuminate\Database\Eloquent\Collection|JsonResponse
     * @throws ValidationException
     */
    public function fetchAll()
    {
        return $this->paginateYajra( $this );
    }

    /**
     * @return Builder
     */
    protected function getQuery(): Builder
    {
        return User::query()
            ->whereHas("roles", function (Builder $builder) {
                $builder->where("user_role.role_id", Role::Client);
            });
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
