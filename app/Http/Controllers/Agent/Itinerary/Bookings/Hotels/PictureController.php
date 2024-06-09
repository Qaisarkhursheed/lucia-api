<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\Hotels;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierPictureCompatible;
use App\Http\Controllers\Agent\Itinerary\Bookings\HotelItemsController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 *  // we need to determine which pictures we are uploading here
    // Linked or Global
 * @property  ISupplierPictureCompatible $model
 */
class PictureController extends HotelItemsController
{
    protected int $MAXIMUM_PICTURES = 6;

    public function __construct()
    {
        parent::__construct( "hotel_picture_id" );
    }

    /**
     * @return Builder[]|Collection|ISupplierPictureCompatible[]
     */
    public function fetchAll()
    {
        return $this->getHotel()->getSupplierAttribute()->getPictures()->map->presentForDev();
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        return $this->invokeImageStoreForSupplierCompatible( $request, $this->getHotel()->getSupplierAttribute());
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        $this->getHotel()->getSupplierAttribute()->deletePicture( $this->model->getId() );
    }

    /**
     * @inheritDoc
     */
    public function loadModel( $route_param_value, bool $withRelations = true )
    {
        $this->model = $this->getHotel()->getSupplierAttribute()->getPicture($route_param_value);
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
