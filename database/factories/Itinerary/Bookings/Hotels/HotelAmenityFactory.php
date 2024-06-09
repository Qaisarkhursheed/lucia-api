<?php

namespace Database\Factories\Itinerary\Bookings\Hotels;

use App\Models\HotelAmenity;
use Illuminate\Database\Eloquent\Factories\Factory;

class HotelAmenityFactory extends Factory
{
    protected $model = HotelAmenity::class;

    /**
     * @inheritDoc
     */
    public function definition()
    {
        return [
            'amenity' => $this->faker->domainName()
        ];
    }
}
