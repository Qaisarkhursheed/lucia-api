<?php

namespace Database\Factories\Itinerary\Bookings;

use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryInsurance;
use Database\Factories\ItineraryFactory;

class ItineraryInsuranceFactory extends ItineraryFactory
{
    protected $model = ItineraryInsurance::class;

    public function definition(): array
    {
        return [
            'price' => $this->price,
            'payment' => $this->payment,
            'company' => $this->faker->company,
            'confirmation_reference' => $this->confirmation_reference,
            'effective_date' => $this->start_date,
            'policy_type' => $this->faker->word,
            'cancel_policy' => $this->cancel_policy,
            'notes' => $this->notes,
            'booking_category_id' => BookingCategory::Insurance,
        ];
    }
}
