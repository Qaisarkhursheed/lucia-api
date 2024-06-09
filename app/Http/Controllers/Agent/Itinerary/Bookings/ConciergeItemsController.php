<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\ModelsExtended\ItineraryConcierge;

abstract class ConciergeItemsController extends ItineraryItemsController
{
    public function __construct(string $param_name)
    {
        parent::__construct( $param_name );
    }

    /**
     * @return int|object|string|null
     */
    protected function getConciergeId()
    {
        return \request()->route( 'concierge_id' );
    }

    /**
     * @return ItineraryConcierge
     */
    protected function getConcierge()
    {
        return ItineraryConcierge::find( $this->getConciergeId() );
    }
}
