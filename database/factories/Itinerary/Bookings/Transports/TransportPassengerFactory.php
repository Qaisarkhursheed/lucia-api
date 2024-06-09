<?php

namespace Database\Factories\Itinerary\Bookings\Transports;

use App\Models\TransportPassenger;
use Database\Factories\Itinerary\ItineraryPassengerFactory;
use Illuminate\Support\Arr;

class TransportPassengerFactory extends ItineraryPassengerFactory
{
    protected $model = TransportPassenger::class;

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
