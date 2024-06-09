<?php

namespace App\Http\Controllers\Agent\Itinerary;

use App\ModelsExtended\ItineraryPassenger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PassengerController extends ItineraryItemsController
{
    public function __construct()
    {
        parent::__construct( "passenger_id" );
    }

    public function fetchAll()
    {
        return $this->getDataQuery()->get()->map->presentForDev();
    }

    public function getDataQuery(): Builder
    {
        return  $this->limitBookingToItineraryOwner( ItineraryPassenger::with("passenger_type"))
            ->where( "itinerary_id", $this->getItineraryId() );
    }

    public function getCommonRules()
    {
        return [
            'name' => 'required|string|max:150',
            'passenger_type_id' => 'filled|exists:passenger_type,id',
        ];
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
       $this->validatedRules($this->getCommonRules());
       return $this->getItinerary()->itinerary_passengers()->create( $request->all() );
    }
}
