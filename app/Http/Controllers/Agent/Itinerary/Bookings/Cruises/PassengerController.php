<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\Cruises;

use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Agent\Itinerary\Bookings\CruiseItemsController;
use App\ModelsExtended\CruisePassenger;
use App\ModelsExtended\ItineraryPassenger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PassengerController extends CruiseItemsController
{
    public function __construct()
    {
        parent::__construct( "cruise_passenger_id" );
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        $query = CruisePassenger::with( "itinerary_passenger", "itinerary_passenger.passenger_type" )
            ->whereHas( "itinerary_cruise.itinerary" , function ( Builder $builder ) {
                $builder->where( "itinerary.user_id", auth()->id() );
            })
            ->where( "itinerary_cruise_id", $this->getCruiseId() );

        return $query->get()->map->presentForDev();
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        $this->validatedRules([
            'name' => 'required|string|max:150',
            'cabin' => 'nullable|string|max:50',
            'cabin_category' => 'nullable|string|max:50',
            'ticket_number' => 'required|string|max:50',
        ]);

       $cruise = $this->getCruise();
       return $cruise->cruise_passengers()->create(
           array_merge(
               [
                  "itinerary_passenger_id" => ItineraryPassenger::updateOrCreate( $cruise->itinerary, $request->all() )->id,
               ],
               $request->all()
           )
       );
    }

    /**
     * @inheritDoc
     * @throws RecordNotFoundException
     */
    public function update( Request $request )
    {
        $this->validatedRules([
            'name' => 'required|string|max:150',
            'cabin' => 'nullable|string|max:50',
            'cabin_category' => 'nullable|string|max:50',
            'ticket_number' => 'required|string|max:50',
        ]);

        $this->model->updateWithRelation( $request->all(), [ 'itinerary_passenger' ] );

        return $this->loadModel( $this->model->id );
    }

    /**
     * @inheritDoc
     */
    public function loadModel( $route_param_value, bool $withRelations = true )
    {
        $query =  CruisePassenger::query()
            ->whereHas( "itinerary_cruise.itinerary" , function ( Builder $builder ) {
                $builder->where( "itinerary.user_id", auth()->id() );
            })
            ->where( "itinerary_cruise_id", $this->getCruiseId() )
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
        return CruisePassenger::query();
    }
}
