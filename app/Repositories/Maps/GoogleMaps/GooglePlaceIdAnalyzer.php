<?php

namespace App\Repositories\Maps\GoogleMaps;

use Illuminate\Contracts\Support\Arrayable;
use SKAgarwal\GoogleApi\PlacesApi;

class GooglePlaceIdAnalyzer implements Arrayable
{
    public ?string $formatted_address;
    public ?string $international_phone_number;
    public ?string $name;
    public ?string $website;
    public ?string $place_id;
    public ?array $types;

    /**
     * @param string $placeId
     * @throws \SKAgarwal\GoogleApi\Exceptions\GooglePlacesApiException
     */
    public function __construct( string $placeId )
    {
        $googlePlaces = new PlacesApi(env( 'GOOGLE_MAP_GEOCODE_KEY' ) );
        $result =  array_to_object( $googlePlaces->placeDetails($placeId )->get("result") );

        $this->formatted_address = $result->formatted_address;
        $this->international_phone_number = optional($result)->international_phone_number;
        $this->name = $result->name;
        $this->website = optional($result)->website;
        $this->place_id = $result->place_id;
        $this->types = (array) $result->types;
    }

    public function toArray()
    {
        return (array)json_decode( json_encode( $this ) );
    }
}
