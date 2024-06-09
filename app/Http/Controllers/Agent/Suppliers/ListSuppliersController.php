<?php

namespace App\Http\Controllers\Agent\Suppliers;

use App\Http\Controllers\Admin\Providers\SuppliersController;
use App\Http\Controllers\Enhancers\YajraPaginableTraitController;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\Role;
use App\ModelsExtended\ServiceSupplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * @property ServiceSupplier $model
 */
class ListSuppliersController extends SuppliersController
{
    use YajraPaginableTraitController;

    public function fetchAll()
    {
        return $this->paginateYajra( );
    }

    public function store(Request $request)
    {
        parent::store($request);
        $this->model->saved_suppliers()->updateOrCreate(['user_id' => auth()->id()]);
        return $this->model->presentForDev();
    }

    public function delete()
    {
        $this->model->saved_suppliers->where("user_id", "=", auth()->id() )->firstOrFail()->delete();
        return new OkResponse( );
    }

    /**
     * @inheritDoc
     */
    protected function getQuery(): Builder
    {
        return ServiceSupplier::query()
            ->join("booking_category" , "booking_category.id", "=" ,"service_suppliers.booking_category_id" )
            ->where( function (Builder $builder) {

//                $builder->where( "is_globally_accessible" , true )
//                    ->orWhereHas( "user" , function ( $builder) {
//                        $builder->where( "user.user_id" , auth()->id() );
//                    } );
//
                $builder->whereHas( "saved_suppliers" , function (Builder $builder) {
                        $builder->where( "saved_supplier.user_id" ,$this->user->id );
                    } )
                ->when(  $this->user->hasRole(Role::MasterAccount ),  function (Builder $builder) {
                    $builder->orWhereHas( "saved_suppliers" , function ( Builder $builder) {
                        $builder->whereRaw( sprintf( "saved_supplier.user_id in (select user_id from lucia_db.master_sub_account where master_account_id=%s)" , $this->user->master_sub_account->master_account_id) );
                    } );
                });
            })
            ->select(
                'service_suppliers.created_at',
                'service_suppliers.id',
                'name',
                'address',
                'phone',
                'website',
                'email',
                'service_suppliers.description',
                'booking_category.description as category',
                'booking_category_id',
                'created_by_id',
                'is_globally_accessible'
            );
    }

    /**
     * @param Builder $query
     * @return Builder|mixed
     */
    protected function filterQuery(Builder $query)
    {
        return $query->when($this->search, function (Builder $builder) {
            $search = $this->search;
            $builder->where("service_suppliers.name", 'like', "%$search%");
        });
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return $this->getQuery();
    }
}
