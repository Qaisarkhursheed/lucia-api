<?php

namespace App\Repositories\Maps\GoogleMaps;

use Illuminate\Support\Facades\Log;

class GoogleMapGeoAddressAnalyzer
{
    /**
     * @var bool
     */
    private $success;
    /**
     * @var string
     */
    private $formatted_address;
    /**
     * @var string
     */
    private $postal_code;
    /**
     * @var string
     */
    private  $country;

    /**
     * @var string
     */
    private  $state;

    /**
     * @var string
     */
    private  $street;

    /**
     * @var string
     */
    private  $province;


    /**
     * GoogleMapAddressAnalyzer constructor.
     * @param $latitude float
     * @param $longitude float
     */
    public function __construct( $latitude, $longitude )
    {

        $this->success = false;

        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&key=" . env( "GOOGLE_MAP_KEY" );

        $result = json_decode( @file_get_contents($url), true);

        $this->success = isset( $result['status'] ) && $result['status'] == 'OK';

        if ( $this->success ) {

            try{

               $data = (object) $result["results"][0];
               $address_components = $data->address_components;

              $locality_component =  $this->fetchAddressComponent( $address_components, "locality" );
              $state_component =  $this->fetchAddressComponent( $address_components, "administrative_area_level_1" );
              $country_component =  $this->fetchAddressComponent( $address_components, "country" );
              $postal_component =  $this->fetchAddressComponent( $address_components, "postal_code" );

               $this->formatted_address = $data->formatted_address;
               $this->postal_code = $postal_component? $postal_component->long_name : null;
               $this->country = $country_component? $country_component->long_name : null;
               $this->province = $locality_component? $locality_component->long_name : null;
               $this->state = $state_component? $state_component->long_name : null;
               $this->street = $this->postal_code?  trim( substr( $this->formatted_address, 0, strpos( $this->formatted_address, $this->postal_code) ) , ", ") : null ;


            }catch(\Exception $exception){
                Log::info( "Error Reading Google Map Analyzer Result! Please, confirm to be sure the format is not different!" ,
                    [
                        "error" => $exception->getMessage(),
                        "data" => json_encode( $result )
                    ]);
            }
        }
    }


    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }


    /**
     * @return string
     */
    public function getFormattedAddress()
    {
        return $this->formatted_address;
    }


    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }


    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }


    public function getProvince()
    {
        return $this->province;
    }

    public function getState()
    {
        return $this->province;
    }





    /**
     * @param array $address_components
     * @param string $key
     * @return object|null
     */
    private function fetchAddressComponent(array $address_components, string $key)
    {

        if( $address_components == null || !is_array( $address_components ) || !$key ) return null;

        $component = array_filter( $address_components, function ( $item ) use($key){
                         return array_key_exists("types", $item ) && collect( $item[ "types" ])->contains( $key );
        } );

        return count( $component ) == 1 ? (object) collect($component)->first() : null ;
    }


}
