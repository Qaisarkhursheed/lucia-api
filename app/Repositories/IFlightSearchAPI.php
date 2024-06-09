<?php

namespace App\Repositories;

use App\Exceptions\APIInvocationException;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;

interface IFlightSearchAPI
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
    public function search(Carbon $date, string $flight_number): IFlightSearchResult;
}
