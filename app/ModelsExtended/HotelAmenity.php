<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;

class HotelAmenity extends \App\Models\HotelAmenity implements IDeveloperPresentationInterface
{

    /**
     * @inheritDoc
     */
    public function presentForDev(): array
    {
        return [
            "id" => $this->id,
            "amenity" => $this->amenity
        ];
    }
}
