<?php


namespace App\Repositories;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;

/**
 * This class helps to communicate with the school web app as an api
 * Class SchoolWebAppInvoker
 * @package App\Http\Controllers\API
 */
class TimeZoneInvoker extends ApiInvoker
{
    /**
     * @throws GuzzleException
     */
    public function __construct(string $timezone)
    {
        $this->queryStringRequest("api/timezone/" , [
            "token" => env("TIMEZONE_TOKEN"),
            "timezone" => $timezone
        ]);
    }

    /**
     * @param string $link
     * @return string
     */
    protected function toUrl( string $link ): string
    {
        return Str::of( "https://timezoneapi.io/"  )->rtrim('/')
            . Str::of( $link )->start("/" );
    }
}
