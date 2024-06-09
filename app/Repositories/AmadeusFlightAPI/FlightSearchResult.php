<?php

namespace App\Repositories\AmadeusFlightAPI;

use App\ModelsExtended\Airport;
use App\Repositories\IFlightSearchResult;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class FlightSearchResult extends IFlightSearchResult
{

    /**
     * @param ApiRequests $requests
     */
    public function __construct(ApiRequests $requests)
    {
        // build results
        $data = Arr::first( $requests->getData()->data ) ;

        parent::__construct(
            $data->flightDesignator->carrierCode .
            $data->flightDesignator->flightNumber
        );

        $this->createArrival( Arr::first( $data->flightPoints ) )
            ->createDestination( Arr::last( $data->flightPoints ) );

    }


    /**
     * @param \stdClass $point
     * @return $this
     */
    private function createArrival( \stdClass $point ): IFlightSearchResult
    {
        $airport = Airport::findByIata( $point->iataCode );

        $this->flight_from = $airport->name;
        $this->departure_date_time =
            Carbon::createFromTimeString( Arr::first( $point->departure->timings )->value );

        return $this;
    }

    /**
     *
     * @param \stdClass $point
     * @return $this
     */
    private function createDestination( \stdClass $point ): IFlightSearchResult
    {
        $airport = Airport::findByIata( $point->iataCode );

        $this->flight_to = $airport->name;
        $this->arrival_date_time =
            Carbon::createFromTimeString( Arr::first( $point->arrival->timings )->value );

        return $this;
    }
}
