<?php

namespace Database\Factories\Itinerary\Bookings\Cruises;

use App\Models\CruisePassenger;
use Database\Factories\Itinerary\ItineraryPassengerFactory;
use Illuminate\Support\Arr;

class CruisePassengerFactory extends ItineraryPassengerFactory
{
    protected $model = CruisePassenger::class;

    public function definition(): array
    {
        return [
            'cabin' => $this->faker->randomLetter() . $this->faker->randomDigit(),
            'cabin_category' => Arr::random(["Business", "Economic", "First Class",]),
            'ticket_number' => $this->faker->swiftBicNumber(),
        ];
    }
}
