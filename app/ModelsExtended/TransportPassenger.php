<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;

class TransportPassenger extends \App\Models\TransportPassenger implements IDeveloperPresentationInterface
{

    /**
     * @inheritDoc
     */
    public function presentForDev(): array
    {
        return [
            'id' => $this->id,

            'itinerary_transport_id' => $this->itinerary_transport_id,
            'itinerary_passenger_id' => $this->itinerary_passenger_id,
            'seat' => $this->seat,
            'class' => $this->class,
            'frequent_flyer_number' => $this->frequent_flyer_number,
            'ticket_number' => $this->ticket_number,

            'name' => $this->itinerary_passenger->name,
            'passenger_type_id' => $this->itinerary_passenger->passenger_type_id,

            'passenger_type' => $this->itinerary_passenger->passenger_type->description,
        ];
    }
}
