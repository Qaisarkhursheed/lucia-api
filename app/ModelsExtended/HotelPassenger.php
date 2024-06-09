<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;

class HotelPassenger extends \App\Models\HotelPassenger implements IDeveloperPresentationInterface
{

    /**
     * @inheritDoc
     */
    public function presentForDev(): array
    {
        return [
            'id' => $this->id,

            'itinerary_hotel_id' => $this->itinerary_hotel_id,
            'itinerary_passenger_id' => $this->itinerary_passenger_id,
            'room' => $this->room,

            'name' => $this->itinerary_passenger->name,
            'passenger_type_id' => $this->itinerary_passenger->passenger_type_id,

            'passenger_type' => $this->itinerary_passenger->passenger_type->description,
        ];
    }
}
