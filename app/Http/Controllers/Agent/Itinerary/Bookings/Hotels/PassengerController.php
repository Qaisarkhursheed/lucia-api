<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\Hotels;

use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Agent\Itinerary\Bookings\HotelItemsController;
use App\ModelsExtended\HotelPassenger;
use App\ModelsExtended\ItineraryPassenger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PassengerController extends HotelItemsController
{
    public function __construct()
    {
        parent::__construct( "hotel_passenger_id" );
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        $query = HotelPassenger::with( "itinerary_passenger", "itinerary_passenger.passenger_type"  )
            ->whereHas( "itinerary_hotel.itinerary" , function ( Builder $builder ) {
                $builder->where( "itinerary.user_id", auth()->id() );
            })
            ->where( "itinerary_hotel_id", $this->getHotelId() );

        return $query->get()->map->presentForDev();
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        $this->validatedRules([
            'name' => 'required|string|max:150',
            'room' => 'nullable|string|max:50',
        ]);

       $hotel = $this->getHotel();
       return $hotel->hotel_passengers()->create(
           [
               "itinerary_passenger_id" => ItineraryPassenger::updateOrCreate( $hotel->itinerary, $request->all() )->id,
               "room" => \request( "room" )
           ]
       );
    }

    /**
     * @inheritDoc
     */
    public function update( Request $request )
    {
        $this->validatedRules([
            'name' => 'required|string|max:150',
            'room' => 'nullable|string|max:50',
        ]);

        $this->model->updateWithRelation( $request->all(), [ 'itinerary_passenger' ] );

        return $this->loadModel( $this->model->id );
    }

    /**
     * @inheritDoc
     */
    public function loadModel( $route_param_value, bool $withRelations = true )
    {
        $query =  HotelPassenger::query()
            ->whereHas( "itinerary_hotel.itinerary" , function ( Builder $builder ) {
                $builder->where( "itinerary.user_id", auth()->id() );
            })
            ->where( "itinerary_hotel_id", $this->getHotelId() )
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
        return HotelPassenger::query();
    }
}
