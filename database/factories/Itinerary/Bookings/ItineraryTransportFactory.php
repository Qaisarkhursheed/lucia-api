<?php

namespace Database\Factories\Itinerary\Bookings;

use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryTransport;
use Database\Factories\ItineraryFactory;

class ItineraryTransportFactory extends ItineraryFactory
{
    protected $model = ItineraryTransport::class;

    public function definition(): array
    {
        $this->faker->addProvider(new \Faker\Provider\Fakecar( $this->faker ));

        return [
            'price' => $this->price,
            'transport_from' => $this->faker->streetAddress,
            'transport_to' => $this->faker->streetAddress,
            'vehicle' => $this->faker->vehicle,

            'departure_datetime'  => $this->start_date,
            'arrival_datetime'  => $this->end_date,
            'notes'  => $this->faker->sentence(9),
            'booking_category_id' => BookingCategory::Transportation,
        ];
    }
}
