<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\ModelsExtended\ItineraryTransport;

abstract class TransportItemsController extends ItineraryItemsController
{
    public function __construct(string $param_name)
    {
        parent::__construct( $param_name );
    }

    /**
     * @return int|object|string|null
     */
    protected function getTransportId()
    {
        return \request()->route( 'transport_id' );
    }

    /**
     * @return ItineraryTransport
     */
    protected function getTransport()
    {
        return ItineraryTransport::find( $this->getTransportId() );
    }
}
