<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\Tours;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierPictureCompatible;
use App\Http\Controllers\Agent\Itinerary\Bookings\IRequireSupplierExistenceInterface;
use App\Http\Controllers\Agent\Itinerary\Bookings\TourItemsController;
use App\Http\Responses\OkResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 *  // we need to determine which pictures we are uploading here
// Linked or Global
 * @property  ISupplierPictureCompatible $model
 */
class PictureController extends TourItemsController  implements IRequireSupplierExistenceInterface
{
    protected int $MAXIMUM_PICTURES = 3;

    public function __construct()
    {
        parent::__construct( "tour_picture_id" );
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        return $this->confirmHasSupplier()->getPictures()->map->presentForDev();
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        return $this->invokeImageStoreForSupplierCompatible( $request, $this->confirmHasSupplier());
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        $this->confirmHasSupplier()->deletePicture( $this->model->getId() );
        return new OkResponse();
    }

    /**
     * @inheritDoc
     */
    public function loadModel( $route_param_value, bool $withRelations = true )
    {
        $this->model = $this->confirmHasSupplier()->getPicture($route_param_value);
        return $this->model;
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        throw new \Exception("Not Implemented!");
    }


    /**
     * @return \App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\IInteractsWithServiceSupplier|\App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierCompatible
     * @throws \Exception
     */
    public function confirmHasSupplier()
    {
        $supplier = $this->getTour()->getSupplierAttribute();
        if( !$supplier ) throw new \Exception("You can not manage pictures because there is no supplier attached!");
        return $supplier;
    }
}
