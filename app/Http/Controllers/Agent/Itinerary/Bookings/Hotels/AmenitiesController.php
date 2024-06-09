<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\Hotels;

use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Agent\Itinerary\Bookings\HotelItemsController;
use App\Models\HotelAmenity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AmenitiesController extends HotelItemsController
{
    public function __construct()
    {
        parent::__construct( "hotel_amenity_id" );
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        $query = HotelAmenity::query()
            ->whereHas( "itinerary_hotel.itinerary" , function ( Builder $builder ) {
                $builder->where( "itinerary.user_id", auth()->id() );
            })
            ->where( "itinerary_hotel_id", $this->getHotelId() );

        return $query->get();
    }

    public function getCommonRules()
    {
        return [
            'amenity' => 'required|string|max:100',
        ];
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        $this->validatedRules($this->getCommonRules());

        $hotel = $this->getHotel();
       return $hotel->hotel_amenities()->create($request->all());
    }

    /**
     * @inheritDoc
     */
    public function update( Request $request )
    {
        $this->validatedRules($this->getCommonRules());

        $this->model->update($request->all());

        return $this->loadModel( $this->model->id );
    }

    /**
     * @inheritDoc
     */
    public function loadModel( $route_param_value, bool $withRelations = true )
    {
        $query =  HotelAmenity::query()
            ->whereHas( "itinerary_hotel.itinerary" , function ( Builder $builder ) {
                $builder->where( "itinerary.user_id", auth()->id() );
            })
            ->where( "itinerary_hotel_id", $this->getHotelId() )
            ->where("id", $route_param_value);

        if(  $withRelations ) $query->with( 'itinerary_hotel' );

        $this->model = $query->first();

        if( ! $this->model ) throw new RecordNotFoundException();

        return $this->model;
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return HotelAmenity::query();
    }
}
