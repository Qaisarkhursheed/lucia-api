<?php

namespace App\Observers;

use App\ModelsExtended\ItineraryCruise;
use App\Repositories\Maps\GoogleMaps\GoogleMapAddressAnalyzer;
use Illuminate\Support\Facades\Log;

class ItineraryCruiseModelObserver
{
    /**
     * Perform Manipulations
     * @param ItineraryCruise $cruise
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function saving( ItineraryCruise $cruise )
    {
        $this->manipulateBeforeSave($cruise);
    }

    /**
     * Perform Manipulations
     *
     * @param ItineraryCruise $cruise
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function manipulateBeforeSave(ItineraryCruise $cruise)
    {
        if( $cruise->getOriginal( "departure_port_city" ) != $cruise->departure_port_city )
        {
//            Log::info( "depart called .. " );
            $depart = new GoogleMapAddressAnalyzer( $cruise->departure_port_city );

            $cruise->departure_latitude = $depart->getLat();
            $cruise->departure_longitude = $depart->getLng();
        }

        if( $cruise->getOriginal( "arrival_port_city" ) != $cruise->arrival_port_city )
        {
//            Log::info( "arrive called .. " );
            $arrive = new GoogleMapAddressAnalyzer( $cruise->arrival_port_city );

            $cruise->arrival_latitude = $arrive->getLat();
            $cruise->arrival_longitude = $arrive->getLng();
        }
    }
}
