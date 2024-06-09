<?php

namespace App\Repositories;

use App\ModelsExtended\Airline;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

abstract class IFlightSearchResult implements Arrayable
{
    public function __construct( string $flight_number )
    {
        $this->flight_number = $flight_number;
        $airlineObject = Airline::findByIata( Str::substr( $flight_number, 0, 2 ) );
        $this->airline = $airlineObject->name;
        $this->airline_icao = $airlineObject->icao;
    }
    /**
     * @var string
     */
    protected string $flight_number;

    /**
     * @var string
     */
    protected string $airline;

    /**
     * @var string
     */
    protected string $airline_icao;

    /**
     * @var string
     */
    protected string $flight_from;

    /**
     * @var Carbon
     */
    protected Carbon $departure_date_time;

    /**
     * @var string
     */
    protected string $flight_to;

    /**
     * @var Carbon
     */
    protected Carbon $arrival_date_time;


    /**
     * @string
     */
    public function departureDateTimeUTC(): Carbon
    {
        return $this->departure_date_time;
    }

    /**
     * @string
     */
    public function arrivalDateTimeUTC(): Carbon
    {
        return $this->arrival_date_time;
    }

    /**
     * @string
     */
    public function flightNumber(): string
    {
        return $this->flight_number;
    }

    /**
     * @string
     */
    public function departureTerminal(): ?string
    {
        return null;
    }

    /**
     * @string
     */
    public function flightFrom(): string
    {
        return $this->flight_from;
    }

    /**
     * @string
     */
    public function flightTo(): string
    {
        return $this->flight_to;
    }

    /**
     * @return string
     */
    public function getAirline(): string
    {
        return $this->airline;
    }

    /**
     * @return string
     */
    public function getAirlineIcao(): string
    {
        return $this->airline_icao;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            "airline" => $this->getAirline(),
            "airline_icao" => $this->getAirlineIcao(),
            "flight_number" => $this->flightNumber(),
            "flight_from" => $this->flightFrom(),
            "flight_to" => $this->flightTo(),
            "departure_date_time" => $this->departureDateTimeUTC(),
            "arrival_date_time" => $this->arrivalDateTimeUTC(),
            "terminal" => $this->departureTerminal(),
        ];
    }
}
