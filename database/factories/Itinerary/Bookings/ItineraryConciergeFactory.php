<?php

namespace Database\Factories\Itinerary\Bookings;

use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryConcierge;
use Database\Factories\ItineraryFactory;

class ItineraryConciergeFactory extends ItineraryFactory
{
    protected $model = ItineraryConcierge::class;

    public function definition(): array
    {
        return [
            'price' => $this->price,
            'payment' => $this->payment,
            'confirmation_reference' => $this->confirmation_reference,
            'confirmed_for' =>  $this->faker->name(),
            'confirmed_by' =>  $this->faker->name(),
            'service_type' => $this->faker->domainName(),

            'start_datetime'  => $this->start_date,
            'end_datetime'  => $this->end_date,
            'cancel_policy'  => $this->cancel_policy,
            'notes'  => $this->notes,
            'booking_category_id' => BookingCategory::Concierge,
        ];
    }
}
