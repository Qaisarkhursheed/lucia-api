<?php

namespace App\Http\Controllers\Agent\Itinerary;

use App\Models\TravellerEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ClientEmailController extends ItineraryItemsController
{
    const MAXIMUM_EMAILS = 5;

    public function __construct()
    {
        parent::__construct( "client_email_id" );
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return TravellerEmail::query()
            ->whereHas( "traveller.itineraries" , function ( Builder $builder ) {
                $builder->where( "itinerary.id", $this->getItineraryId() );
            })
            ->whereHas( "traveller" , function ( Builder $builder ) {
                $builder->where( "traveller.created_by_id", auth()->id() );
            });
    }

    public function getCommonRules()
    {
        return [
            'email' => 'required|email'
        ];
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function store( Request $request )
    {
        $this->validatedRules($this->getCommonRules());

        return $this->runInALock( 'add-itinerary-client-email' . $this->getItineraryId() ,
            function ( ) use( $request ){

                $itinerary = $this->getItinerary();

                if( $itinerary->traveller->traveller_emails->count() === self::MAXIMUM_EMAILS )
                    throw new \Exception( "You have reached the maximum email limit of " . self::MAXIMUM_EMAILS );

                return $itinerary->traveller->traveller_emails()->create([
                    "email" => $request->input( "email" )
                ]);

            });
    }

    /**
     * @inheritDoc
     */
    public function update(Request $request)
    {
        $this->validatedRules($this->getCommonRules());

        $this->model->update([
            "email" => $request->input( "email" )
        ]);

        return $this->model;
    }

}
