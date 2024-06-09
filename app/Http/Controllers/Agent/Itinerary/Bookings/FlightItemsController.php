<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\ModelsExtended\ItineraryFlight;

abstract class FlightItemsController extends ItineraryItemsController
{
    public function __construct(string $param_name)
    {
        parent::__construct( $param_name );
    }

    /**
     * @return int|object|string|null
     */
    protected function getFlightId()
    {
        return \request()->route( 'flight_id' );
    }

    /**
     * @return ItineraryFlight|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     */
    protected function getFlight()
    {
        return ItineraryFlight::query()
            ->where( "itinerary_id" , $this->getItineraryId() )
            ->where( "id",  $this->getFlightId() )
            ->firstOrFail();
    }
}
