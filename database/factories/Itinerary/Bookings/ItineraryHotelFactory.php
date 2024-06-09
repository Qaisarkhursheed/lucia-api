<?php

namespace Database\Factories\Itinerary\Bookings;

use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryHotel;
use Database\Factories\ItineraryFactory;

class ItineraryHotelFactory extends ItineraryFactory
{
    protected $model = ItineraryHotel::class;

    public function definition(): array
    {
        return [
            'price'  => $this->price,
            'check_in_date'  => $this->start_date,
            'check_out_date'  => $this->end_date,
            'travelers'  => $this->faker->randomDigitNot(0),
            'confirmation_reference'  => $this->confirmation_reference,
            'cancel_policy'  => $this->cancel_policy,
            'notes'  => $this->notes,
            'payment'  => $this->payment,
            'booking_category_id' => BookingCategory::Hotel,
        ];
    }
}
