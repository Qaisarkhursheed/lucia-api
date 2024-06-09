<?php

namespace Database\Factories\Itinerary;

use App\ModelsExtended\ItineraryPassenger;
use App\ModelsExtended\PassengerType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class ItineraryPassengerFactory extends Factory
{
    protected $model = ItineraryPassenger::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'passenger_type_id' => Arr::random( PassengerType::all()->toArray() ) [ "id" ],
        ];
    }
}
