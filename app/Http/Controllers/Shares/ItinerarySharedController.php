<?php

namespace App\Http\Controllers\Shares;

use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\Itinerary;

class ItinerarySharedController extends Controller
{
    public function __invoke()
    {
        $itinerary =  Itinerary::query()
            ->where( "share_itinerary_key" , request()->route( "share_itinerary_key" ) )
            ->first();
        if( ! $itinerary ) throw new RecordNotFoundException();

        return new OkResponse( $itinerary->packForFetch() );
    }
}
