<?php

namespace Database\Factories\Itinerary;

use App\Models\TravellerEmail;
use Illuminate\Database\Eloquent\Factories\Factory;

class TravellerEmailFactory extends Factory
{
    protected $model = TravellerEmail::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->email,
        ];
    }
}
