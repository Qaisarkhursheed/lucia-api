<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\Transports;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierPictureCompatible;
use App\Http\Controllers\Agent\Itinerary\Bookings\IRequireSupplierExistenceInterface;
use App\Http\Controllers\Agent\Itinerary\Bookings\TransportItemsController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 *  BECAREFUL HERE, THis will generate error if no supplier is created at all.
 * @property  ISupplierPictureCompatible $model
 */
class PictureController extends TransportItemsController implements IRequireSupplierExistenceInterface
{
    protected int $MAXIMUM_PICTURES = 3;

    public function __construct()
    {
        parent::__construct( "transport_picture_id" );
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     * @throws \Exception
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
        $supplier = $this->getTransport()->getSupplierAttribute();
        if( !$supplier ) throw new \Exception("You can not manage pictures because there is no supplier attached!");
        return $supplier;
    }
}
