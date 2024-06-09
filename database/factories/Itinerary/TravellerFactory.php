<?php

namespace Database\Factories\Itinerary;

use App\ModelsExtended\Traveller;
use Illuminate\Database\Eloquent\Factories\Factory;

class TravellerFactory extends Factory
{
    protected $model = Traveller::class;

    public function definition(): array
    {
        return [
            'name'  => $this->faker->name ,
            'abstract_note'  => $this->faker->sentence(10) ,
            'phone'  => $this->faker->phoneNumber
        ];
    }
}
