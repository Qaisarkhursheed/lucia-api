<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\Cruises;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierPictureCompatible;
use App\Http\Controllers\Agent\Itinerary\Bookings\CruiseItemsController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 *  // we need to determine which pictures we are uploading here
// Linked or Global
 * @property  ISupplierPictureCompatible $model
 */
class PictureController extends CruiseItemsController
{
    protected int $MAXIMUM_PICTURES = 3;

    public function __construct()
    {
        parent::__construct( "cruise_picture_id" );
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        return $this->getCruise()->getSupplierAttribute()->getPictures()->map->presentForDev();
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        return $this->invokeImageStoreForSupplierCompatible( $request, $this->getCruise()->getSupplierAttribute());
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        $this->getCruise()->getSupplierAttribute()->deletePicture( $this->model->getId() );
    }

    /**
     * @inheritDoc
     */
    public function loadModel( $route_param_value, bool $withRelations = true )
    {
        $this->model = $this->getCruise()->getSupplierAttribute()->getPicture($route_param_value);
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
