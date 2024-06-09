<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\Flights;

use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Agent\Itinerary\Bookings\FlightItemsController;
use App\ModelsExtended\FlightPassenger;
use App\ModelsExtended\ItineraryPassenger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PassengerController extends FlightItemsController
{
    public function __construct()
    {
        parent::__construct( "flight_passenger_id" );
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        $query = FlightPassenger::with( "itinerary_passenger", "itinerary_passenger.passenger_type" )
            ->whereHas( "itinerary_flight.itinerary" , function ( Builder $builder ) {
                $builder->where( "itinerary.id", $this->getItineraryId() )
                ->where( "itinerary.user_id", auth()->id() );
            })
            ->where( "itinerary_flight_id", $this->getFlightId() );

        return $query->get()->map->presentForDev();
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        $this->validatedRules([
            'name' => 'required|string|max:150',
            'class' => 'required|string|max:50',
            'seat' => 'required|string|max:50',
            'ticket_number' => 'nullable |string|max:50',
            'frequent_flyer_number' => 'nullable|string|max:50',
        ]);

       $flight = $this->getFlight();
       return $flight->flight_passengers()->create(
           array_merge(
               [
                  "itinerary_passenger_id" => ItineraryPassenger::updateOrCreate( $flight->itinerary, $request->all() )->id,
               ],
               $request->all()
           )
       );
    }

    /**
     * @inheritDoc
     */
    public function update( Request $request )
    {
        $this->validatedRules([
            'name' => 'required|string|max:150',
            'class' => 'required|string|max:50',
            'seat' => 'required|string|max:50',
            'ticket_number' => 'required|string|max:50',
            'frequent_flyer_number' => 'nullable|string|max:50',
        ]);

        $this->model->updateWithRelation( $request->all(), [ 'itinerary_passenger' ] );

        return $this->loadModel( $this->model->id );
    }

    /**
     * @inheritDoc
     */
    public function loadModel( $route_param_value, bool $withRelations = true )
    {
        $query =  FlightPassenger::query()
            ->whereHas( "itinerary_flight.itinerary" , function ( Builder $builder ) {
                $builder->where( "itinerary.user_id", auth()->id() )
                    ->where( "itinerary.id", $this->getItineraryId() );
            })
            ->where( "itinerary_flight_id", $this->getFlightId() )
            ->where("id", $route_param_value);

        if(  $withRelations ) $query->with( 'itinerary_passenger' );

        $this->model = $query->first();

        if( ! $this->model ) throw new RecordNotFoundException();

        return $this->model;
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return FlightPassenger::query();
    }
}
