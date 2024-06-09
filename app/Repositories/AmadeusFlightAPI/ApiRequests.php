<?php

namespace App\Repositories\AmadeusFlightAPI;

use App\Exceptions\APIInvocationException;
use App\Repositories\ApiInvoker;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ApiRequests extends ApiInvoker
{

    /**
     * NB: this will try to log in the API
     *
     * @throws GuzzleException
     * @throws APIInvocationException
     */
    public function __construct()
    {
        $this->login();
    }

    /**
     * @inheritDoc
     */
    protected function toUrl(string $link): string
    {
        return Str::of( env( "AMADEUS_API_URL" )  )->rtrim('/')
            . Str::of( $link )->start("/" );
    }

    /**
     * @return ApiInvoker
     * @throws APIInvocationException
     * @throws GuzzleException
     */
    public function login(): ApiInvoker
    {
        if (!Cache::get(env("AMADEUS_API_URL"))) {
            if (!$this->formParamsRequest('v1/security/oauth2/token', [
                "grant_type" => "client_credentials",
                'client_id' => env('AMADEUS_API_CLIENT_ID'),
                'client_secret' => env('AMADEUS_API_CLIENT_SECRET'),
            ], 'POST')) $this->throwException();

            // fetch data
            $this->access_token = $this->getData()->access_token;
            $this->expires_in = $this->getData()->expires_in;

            // store data
            Cache::put(env("AMADEUS_API_URL"), $this->access_token, Carbon::now()->addSeconds($this->expires_in));
        }else
            $this->access_token =  Cache::get(env("AMADEUS_API_URL") );

        // return the model here
        return $this;
    }

    /**
     * @param string $carrierCode
     * @param int $flightNumber
     * @param Carbon $scheduledDepartureDate
     * @return $this
     * @throws APIInvocationException
     * @throws GuzzleException
     */
    public function searchFlights( string $carrierCode, int $flightNumber, Carbon $scheduledDepartureDate  ): ApiRequests
    {
        if (!$this->queryStringRequest('v2/schedule/flights', [
            "carrierCode" => $carrierCode,
            'flightNumber' => $flightNumber,
            'scheduledDepartureDate' => $scheduledDepartureDate->format( 'Y-m-d' ),
        ])) $this->throwException();

        // return the model here
        return $this;
    }
}
