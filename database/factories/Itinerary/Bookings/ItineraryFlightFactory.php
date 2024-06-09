<?php

namespace Database\Factories\Itinerary\Bookings;

use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryFlight;
use Database\Factories\ItineraryFactory;

class ItineraryFlightFactory extends ItineraryFactory
{
    protected $model = ItineraryFlight::class;

    public function definition(): array
    {
        return [
            'confirmation_number' => $this->faker->randomLetter() . $this->faker->randomDigit(),
            'custom_header_title'  => $this->faker->sentence(2),
            'price'  => $this->faker->randomFloat(2),
            'check_in_url'  => $this->faker->url(),
            'cancel_policy'  => $this->faker->sentence(),
            'notes'  => $this->faker->sentence(9),
            'booking_category_id' => BookingCategory::Flight,
        ];
    }
}
