<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\ModelsExtended\ItineraryHotel;

abstract class HotelItemsController extends ItineraryItemsController
{
    public function __construct(string $param_name)
    {
        parent::__construct( $param_name );
    }

    /**
     * @return int|object|string|null
     */
    protected function getHotelId()
    {
        return \request()->route( 'hotel_id' );
    }

    /**
     * @return ItineraryHotel
     */
    protected function getHotel()
    {
        return ItineraryHotel::find( $this->getHotelId() );
    }
}
