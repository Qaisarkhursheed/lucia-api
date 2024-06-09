<?php

namespace App\Observers;

use App\Console\Commands\Fix\FixFlightSegmentGeolocation;
use App\ModelsExtended\Airport;
use App\ModelsExtended\ItineraryFlight;
use App\ModelsExtended\ItineraryFlightSegment;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class ItineraryFlightSegmentModelObserver
{
    /**
     * Perform Manipulations
     *
     * @param ItineraryFlightSegment $segment
     */
    public function saving( ItineraryFlightSegment $segment )
    {
        $this->manipulateBeforeSave($segment);
    }

    /**
     * Perform Manipulations
     *
     * @param ItineraryFlightSegment $segment
     */
    public function deleted( ItineraryFlightSegment $segment )
    {
        $this->setFlightCustomTitle($segment->itinerary_flight);
    }

    /**
     * Perform Manipulations
     *
     * @param ItineraryFlightSegment $segment
     */
    public function saved ( ItineraryFlightSegment $segment )
    {
        $this->setFlightCustomTitle($segment->itinerary_flight);
    }

    /**
     * Perform Manipulations
     *
     * @param ItineraryFlightSegment $segment
     * @throws GuzzleException
     */
    public function manipulateBeforeSave(ItineraryFlightSegment $segment)
    {
        if( $segment->getOriginal( "flight_from" ) != $segment->flight_from )
        {
            $airport = Airport::findByName( $segment->flight_from );
            if( $airport )
            {
                $segment->flight_from_iata = $airport->iata;
                $segment->flight_from_icao = $airport->icao;
                $segment->flight_from_longitude = $airport->longitude;
                $segment->flight_from_latitude = $airport->latitude;
            }else{
                FixFlightSegmentGeolocation::fixFlightFrom( $segment );
            }
        }

        if( $segment->getOriginal( "flight_to" ) != $segment->flight_to )
        {
            $airport = Airport::findByName( $segment->flight_to );
            if( $airport )
            {
                $segment->flight_to_iata = $airport->iata;
                $segment->flight_to_icao = $airport->icao;
                $segment->flight_to_longitude = $airport->longitude;
                $segment->flight_to_latitude = $airport->latitude;
            }else{
                FixFlightSegmentGeolocation::fixFlightTo( $segment );
            }
        }
    }

    /**
     * @param ItineraryFlight $flight
     * @return ItineraryFlight
     */
    public function setFlightCustomTitle(ItineraryFlight $flight): ItineraryFlight
    {
//        Log::info("Calling ...");
        if( !$flight->earliest_flight )
        {
//            Log::info("Refreshing ...");
            $flight->refresh();
        }

        if( !$flight->earliest_flight )
        {
            $flight->custom_header_title = "Flight";
        }else{
            $flight->custom_header_title = sprintf( "Flight from %s to %s", $flight->earliest_flight->flight_from, $flight->last_flight->flight_to  );
        }

        $flight->update();
        return $flight;
    }
}
