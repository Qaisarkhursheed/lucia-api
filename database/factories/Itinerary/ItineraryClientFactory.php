<?php

namespace Database\Factories\Itinerary;

use App\Models\ItineraryClient;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItineraryClientFactory extends Factory
{
    protected $model = ItineraryClient::class;

    public function definition(): array
    {
        return [
            'name'  => $this->faker->name ,
            'phone'  => $this->faker->phoneNumber
        ];
    }
}
