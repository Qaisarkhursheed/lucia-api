<?php

namespace Database\Factories\Itinerary\Bookings\Flights;

use App\Models\FlightPassenger;
use Database\Factories\Itinerary\ItineraryPassengerFactory;
use Illuminate\Support\Arr;

class FlightPassengerFactory extends ItineraryPassengerFactory
{
    protected $model = FlightPassenger::class;

    public function definition(): array
    {
        return [
            'seat' => $this->faker->randomLetter() . $this->faker->randomDigit(),
            'class' => Arr::random( [ "Business", "Economic", "First Class",  ]),
            'frequent_flyer_number' => $this->faker->swiftBicNumber(),
            'ticket_number'=> $this->faker->creditCardNumber()
        ];
    }

}
