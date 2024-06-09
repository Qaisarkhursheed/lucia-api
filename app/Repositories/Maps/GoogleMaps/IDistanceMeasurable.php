<?php

namespace App\Repositories\Maps\GoogleMaps;

interface IDistanceMeasurable
{
    /**
     * @return int
     */
    function getMeters();

    /**
     * @return int
     */
    function getSeconds();

    /**
     * @return string
     */
    function getDeparture();

    /**
     * @return string
     */
    function getArrival();

}
