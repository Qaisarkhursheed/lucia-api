<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\Insurances;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierPictureCompatible;
use App\Http\Controllers\Agent\Itinerary\Bookings\InsuranceItemsController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 *  // we need to determine which pictures we are uploading here
// Linked or Global
 * @property  ISupplierPictureCompatible $model
 */
class PictureController extends InsuranceItemsController
{
    protected int $MAXIMUM_PICTURES = 3;

    public function __construct()
    {
        parent::__construct( "insurance_picture_id" );
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        return $this->getInsurance()->getSupplierAttribute()->getPictures()->map->presentForDev();
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        return $this->invokeImageStoreForSupplierCompatible( $request, $this->getInsurance()->getSupplierAttribute());
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        $this->getInsurance()->getSupplierAttribute()->deletePicture( $this->model->getId() );
    }


    /**
     * @inheritDoc
     */
    public function loadModel( $route_param_value, bool $withRelations = true )
    {
        $this->model = $this->getInsurance()->getSupplierAttribute()->getPicture($route_param_value);
        return $this->model;
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        throw new \Exception("Not Implemented!");
    }
}
