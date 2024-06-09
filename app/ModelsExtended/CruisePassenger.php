<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;

class CruisePassenger extends \App\Models\CruisePassenger implements IDeveloperPresentationInterface
{

    /**
     * @inheritDoc
     */
    public function presentForDev(): array
    {
        return [
            'id' => $this->id,

            'itinerary_cruise_id' => $this->itinerary_cruise_id,
            'itinerary_passenger_id' => $this->itinerary_passenger_id,
            'cabin' => $this->cabin,
            'cabin_category' => $this->cabin_category,
            'ticket_number' => $this->ticket_number,

            'name' => $this->itinerary_passenger->name,
            'passenger_type_id' => $this->itinerary_passenger->passenger_type_id,

            'passenger_type' => $this->itinerary_passenger->passenger_type->description,
        ];
    }
}
