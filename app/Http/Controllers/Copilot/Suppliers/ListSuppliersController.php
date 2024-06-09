<?php

namespace App\Http\Controllers\Copilot\Suppliers;

use App\ModelsExtended\ServiceSupplier;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property ServiceSupplier $model
 */
class ListSuppliersController extends \App\Http\Controllers\Agent\Suppliers\ListSuppliersController
{
    /**
     * @inheritDoc
     */
    protected function getQuery(): Builder
    {
        return ServiceSupplier::query()
            ->join("booking_category" , "booking_category.id", "=" ,"service_suppliers.booking_category_id" )
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
}
