<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\ModelsExtended\ItineraryCruise;

abstract class CruiseItemsController extends ItineraryItemsController
{
    public function __construct(string $param_name)
    {
        parent::__construct( $param_name );
    }

    /**
     * @return int|object|string|null
     */
    protected function getCruiseId()
    {
        return \request()->route( 'cruise_id' );
    }

    /**
     * @return ItineraryCruise
     */
    protected function getCruise()
    {
        return ItineraryCruise::find( $this->getCruiseId() );
    }
}
