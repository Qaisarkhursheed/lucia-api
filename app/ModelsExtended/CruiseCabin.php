<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;

/**
 * @property ItineraryCruise $itinerary_cruise
 */
class CruiseCabin extends \App\Models\CruiseCabin implements IDeveloperPresentationInterface
{
    public function itinerary_cruise()
    {
        return $this->belongsTo(ItineraryCruise::class);
    }

    public function presentForDev(): array
    {
        return [
            "id" => $this->id,
            "cabin_category" => $this->cabin_category,
            "guest_name" => $this->guest_name,
            "number_of_guests" => $this->number_of_guests,
            "bedding_type" => $this->bedding_type,
            "confirmation_reference" => $this->confirmation_reference,
        ];
    }
}
