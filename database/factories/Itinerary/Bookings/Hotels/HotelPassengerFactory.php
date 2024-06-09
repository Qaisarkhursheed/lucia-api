<?php

namespace Database\Factories\Itinerary\Bookings\Hotels;

use App\Models\HotelPassenger;
use Database\Factories\Itinerary\ItineraryPassengerFactory;

class HotelPassengerFactory extends ItineraryPassengerFactory
{
    protected $model = HotelPassenger::class;

    public function definition(): array
    {
        return [
            'room' => $this->faker->word
        ];
    }


}
