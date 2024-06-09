<?php

namespace App\Http\Controllers\Client;

use App\ModelsExtended\Itinerary;
use App\ModelsExtended\ItineraryStatus;
use Illuminate\Database\Eloquent\Builder;

trait MyItinerariesQueryTrait
{
    protected function myItineraries()
    {
        return Itinerary::with("traveller")
            ->where( function (Builder $builder){
                // ->whereIn( "status_id", [ ItineraryStatus::Accepted, ItineraryStatus::Sent,  ] )
                $builder->where( "mark_as_client_approved", true );
            })
            ->whereHas( "traveller.traveller_emails", function (Builder $builder){
                $builder->where( "traveller_email.email", auth()->user()->email );
            });
    }
}
