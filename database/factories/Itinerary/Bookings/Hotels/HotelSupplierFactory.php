<?php

namespace Database\Factories\Itinerary\Bookings\Hotels;

use App\Models\HotelSupplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class HotelSupplierFactory extends Factory
{
    protected $model = HotelSupplier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'website' => $this->faker->url,
            'email' => $this->faker->email,
        ];
    }
}
