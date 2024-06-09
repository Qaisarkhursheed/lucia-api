<?php

namespace App\Repositories\Maps\GoogleMaps;

class GoogleMapDistanceCalculator implements IDistanceMeasurable
{
    /**
     * @var bool
     */
    private $success;
    /**
     * @var int
     */
    private $distance;
    /**
     * @var int
     */
    private $duration;
    /**
     * @var string
     */
    private  $departure;

    /**
     * @var string
     */
    private  $arrival;


    /**
     * GoogleMapDistanceCalculator constructor.
     * @param $start_latitude float
     * @param $start_longitude float
     * @param $end_latitude float
     * @param $end_longitude float
     */
    public function __construct( $start_latitude, $start_longitude, $end_latitude, $end_longitude )
    {

        $this->success = false;
        $this->distance = 0;
        $this->duration = 0;


        $origins = $start_latitude . ',' . $start_longitude;
        $destinations = $end_latitude . ',' . $end_longitude;

        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$origins&destinations=$destinations&mode=driving&language=fr-FR&sensor=false&key=" .
            env( "GOOGLE_MAP_KEY" );


        $result = json_decode( @file_get_contents($url), true);

        if (isset($result['rows'][0]['elements'][0]['status']) && $result['rows'][0]['elements'][0]['status'] == 'OK') {

            $this->success = true;

            $main_body = (object) $result;
            $this->departure = $main_body->destination_addresses[0];
            $this->arrival = $main_body->origin_addresses[0];

            $row_data = (object) $result['rows'][0]['elements'][0];
            $this->distance = $row_data->distance["value"];
            $this->duration = $row_data->duration["value"];

        }

    }


    /**
     * @return int
     */
    public function getMeters()
    {
        return $this->distance;
    }

    /**
     * @return int
     */
    public function getSeconds()
    {
        return $this->duration;
    }


    /**
     * @return string
     */
    public function getDeparture()
    {
        return $this->departure;
    }


    /**
     * @return string
     */
    public function getArrival()
    {
        return $this->arrival;
    }




}
