<?php

namespace App\Repositories\Maps\GoogleMaps;

use App\Repositories\ApiInvoker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class GoogleMapAddressAnalyzer extends ApiInvoker
{

    private ?string $formatted_address;
    private ?float $lat;
    private ?float $lng;

    /**
     * GoogleMapAddressAnalyzer constructor.
     * @param string $address
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function __construct( string $address )
    {
        $this->loadAddress($address);
    }

    /**
     * @return float|null
     */
    public function getLat(): ?float
    {
        return $this->lat;
    }

    /**
     * @return float|null
     */
    public function getLng(): ?float
    {
        return $this->lng;
    }

    /**
     * @return string|null
     */
    public function getFormattedAddress(): ?string
    {
        return $this->formatted_address;
    }

    /**
     * @param $address
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function loadAddress($address): void
    {
        $this->queryStringRequest("maps/api/geocode/json", [
            "address" => $address,
            "key" => env("GOOGLE_MAP_GEOCODE_KEY"),
        ]);

        $this->formatted_address = null;
        $this->lat = null;
        $this->lng = null;

        if ($this->getData()->status === 'OK') {

            $result = (object) Arr::first( $this->getData()->results );

            $this->formatted_address = $result->formatted_address;
            $this->lat = $result->geometry->location->lat;
            $this->lng = $result->geometry->location->lng;
        }
    }

    /**
     * @inheritDoc
     */
    protected function toUrl(string $link): string
    {
        return Str::of( env( "GOOGLE_MAP_URL" )  )->rtrim('/')
            . Str::of( $link )->start("/" );
    }

}
