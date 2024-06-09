<?php

namespace Database\Factories\Itinerary\Bookings\Flights;

use App\ModelsExtended\Airline;
use App\ModelsExtended\Airport;
use App\ModelsExtended\ItineraryFlightSegment;
use Database\Factories\Itinerary\Bookings\ItineraryFlightFactory;

class FlightSegmentFactory extends ItineraryFlightFactory
{
    protected $model = ItineraryFlightSegment::class;

    public function definition(): array
    {
        $flight_from  = $this->getRandomAirport();
        $flight_to  = $this->getRandomAirport();
        $airline  = $this->getRandomAirline();

        return [
            'flight_from' => $flight_from->name,
            'flight_to' => $flight_to->name,
            'flight_from_icao' => $flight_from->icao,
            'flight_from_latitude' => $flight_from->latitude,
            'flight_from_longitude' => $flight_from->longitude,
            'flight_to_icao' => $flight_to->icao,
            'flight_to_latitude' => $flight_to->latitude,
            'flight_to_longitude' => $flight_to->longitude,
            'airline' => $airline->name,
            'airline_operator' => $airline->name,
            'duration_in_minutes' => $this->faker->randomNumber(2),

            'flight_number' => $this->faker->swiftBicNumber,

            'departure_datetime'  => $this->start_date,
            'arrival_datetime'  => $this->end_date,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null|Airport
     */
    private function getRandomAirport()
    {
        return Airport::query()->inRandomOrder()->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null|Airline
     */
    private function getRandomAirline()
    {
        return Airline::query()->inRandomOrder()->first();
    }
}
