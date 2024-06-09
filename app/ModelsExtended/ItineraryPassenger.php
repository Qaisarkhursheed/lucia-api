<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;

class ItineraryPassenger extends \App\Models\ItineraryPassenger implements IDeveloperPresentationInterface
{
    /**
     * Update or create passenger on hotel
     * @param \App\Models\Itinerary $itinerary
     * @param array $passenger
     * @return \Illuminate\Database\Eloquent\Model|ItineraryPassenger
     */
    public static function updateOrCreate(\App\Models\Itinerary $itinerary, array $passenger)
    {
       return $itinerary->itinerary_passengers()->updateOrCreate(
            [
                "name" => $passenger[ "name" ]
            ],
            $passenger
        );
    }

    /**
     * @inheritDoc
     */
    public function presentForDev(): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,
            'itinerary_id' => $this->itinerary_id,
            'passenger_type_id' => $this->passenger_type_id,

            'passenger_type' => $this->passenger_type->description,
        ];
    }
}
