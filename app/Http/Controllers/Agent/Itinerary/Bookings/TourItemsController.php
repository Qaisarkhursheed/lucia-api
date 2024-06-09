<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\ModelsExtended\ItineraryTour;

abstract class TourItemsController extends ItineraryItemsController
{
    public function __construct(string $param_name)
    {
        parent::__construct( $param_name );
    }

    /**
     * @return int|object|string|null
     */
    protected function getTourId()
    {
        return \request()->route( 'tour_id' );
    }

    /**
     * @return ItineraryTour
     */
    protected function getTour()
    {
        return ItineraryTour::find( $this->getTourId() );
    }
}
