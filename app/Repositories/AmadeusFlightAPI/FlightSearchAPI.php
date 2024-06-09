<?php

namespace App\Repositories\AmadeusFlightAPI;

use App\Exceptions\APIInvocationException;
use App\Repositories\IFlightSearchAPI;
use App\Repositories\IFlightSearchResult;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;

class FlightSearchAPI implements IFlightSearchAPI
{
    /**
     * searches for flight and build result
     *
     * @param Carbon $date
     * @param string $flight_number
     * @return IFlightSearchResult
     * @throws APIInvocationException
     * @throws GuzzleException
     */
    public function search( Carbon $date, string $flight_number ): IFlightSearchResult
    {
        try {
            $request = new ApiRequests();
            return new FlightSearchResult( $request->searchFlights( Str::substr( $flight_number, 0, 2 ), intval( Str::substr( $flight_number, 2 ) ), $date ) );
        }catch (\Exception $ex){
            info(
                sprintf("searching [%s] on %s generated error: %s" ,
                $flight_number, $date->toDateString(), $ex->getMessage()
            ) ,
                $ex->getTrace()
            );
            throw new \Exception( "No result found for the search!" );
        }
    }
}
