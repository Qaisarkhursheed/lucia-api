<?php

namespace Database\Factories\Itinerary\Bookings;

use App\Models\ItineraryOther;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\Priority;
use Database\Factories\ItineraryFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ItineraryOtherFactory extends ItineraryFactory
{
    protected $model = ItineraryOther::class;

    public function definition(): array
    {
        return [
            'priority_id' => Arr::random( Priority::all()->toArray() ) [ "id" ],
            'title'  => $this->faker->sentence(),
            'notes' => $this->notes,
            'booking_category_id' => BookingCategory::Other_Notes,
            'target_date' => Carbon::now()
        ];
    }
}
