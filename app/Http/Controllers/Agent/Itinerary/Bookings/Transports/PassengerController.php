<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\Transports;

use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Agent\Itinerary\Bookings\TransportItemsController;
use App\ModelsExtended\TransportPassenger;
use App\ModelsExtended\ItineraryPassenger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PassengerController extends TransportItemsController
{
    public function __construct()
    {
        parent::__construct( "transport_passenger_id" );
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        $query = TransportPassenger::with( "itinerary_passenger", "itinerary_passenger.passenger_type"  )
            ->whereHas( "itinerary_transport.itinerary" , function ( Builder $builder ) {
                $builder->where( "itinerary.user_id", auth()->id() );
            })
            ->where( "itinerary_transport_id", $this->getTransportId() );

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
            'ticket_number' => 'required|string|max:50',
            'frequent_flyer_number' => 'nullable|string|max:50',
        ]);

       $transport = $this->getTransport();
       return $transport->transport_passengers()->create(
           array_merge(
               [
                  "itinerary_passenger_id" => ItineraryPassenger::updateOrCreate( $transport->itinerary, $request->all() )->id,
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
        $query =  TransportPassenger::query()
            ->whereHas( "itinerary_transport.itinerary" , function ( Builder $builder ) {
                $builder->where( "itinerary.user_id", auth()->id() );
            })
            ->where( "itinerary_transport_id", $this->getTransportId() )
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
        return TransportPassenger::query();
    }
}
