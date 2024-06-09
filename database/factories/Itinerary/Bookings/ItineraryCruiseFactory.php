<?php

namespace Database\Factories\Itinerary\Bookings;

use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryCruise;
use Database\Factories\ItineraryFactory;

class ItineraryCruiseFactory extends ItineraryFactory
{
    protected $model = ItineraryCruise::class;

    public function definition(): array
    {
        return [
            'cruise_ship_name'  => $this->faker->company,

            'departure_port_city' => $this->faker->streetAddress,
            'arrival_port_city' => $this->faker->address,

            'departure_datetime'  => $this->start_date,
            'disembarkation_datetime'  => $this->end_date,
            'cancel_policy'  => $this->faker->sentence(),
            'notes'  => $this->faker->sentence(9),
            'booking_category_id' => BookingCategory::Cruise,
        ];
    }
}
