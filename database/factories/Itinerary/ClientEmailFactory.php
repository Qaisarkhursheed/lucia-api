<?php

namespace Database\Factories\Itinerary;

use App\Models\ClientEmail;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientEmailFactory extends Factory
{
    protected $model = ClientEmail::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->email,
        ];
    }
}
