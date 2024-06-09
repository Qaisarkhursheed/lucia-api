<?php

namespace Database\Factories\Itinerary\Bookings;

use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryTour;
use Database\Factories\ItineraryFactory;

class ItineraryTourFactory extends ItineraryFactory
{
    protected $model = ItineraryTour::class;

    public function definition(): array
    {
        return [
            'price' => $this->price,
            'payment' => $this->payment,
            'confirmation_reference' => $this->confirmation_reference,
            'start_datetime' => $this->start_date,
            'end_datetime' => $this->end_date,
            'meeting_point' => $this->faker->streetAddress,
            'confirmed_by' => $this->faker->name,
            'description' => $this->cancel_policy,
            'notes' => $this->notes,
            'booking_category_id' => BookingCategory::Tour_Activity,
        ];
    }
}
