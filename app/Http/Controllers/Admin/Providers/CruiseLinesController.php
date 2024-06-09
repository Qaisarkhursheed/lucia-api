<?php

namespace App\Http\Controllers\Admin\Providers;

use App\Http\Controllers\Enhancers\CRUDEnabledController;
use App\Http\Controllers\Enhancers\YajraPaginableTraitController;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ServiceSupplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CruiseLinesController  extends SuppliersController
{
    /**
     * @inheritDoc
     */
    public function store(Request $request)
    {
        // TODO: Implement store() method.
    }

    /**
     * @inheritDoc
     */
    protected function getQuery(): Builder
    {
        return ServiceSupplier::with( 'service_ships:id,name,service_supplier_id',
            'ship_ports:id,name,service_supplier_id', 'user:id,name' )
            ->where( 'booking_category_id', BookingCategory::Cruise )
            ->select(
                'id',
                'name',
                'address',
                'phone',
                'website',
                'email',
                'created_by_id',
                'is_globally_accessible',
                'created_at',
            );
    }

    /**
     * @inheritDoc
     */
    public function processYajraEloquentResult($result): array
    {
        return $result
            ->toArray();
    }
}
