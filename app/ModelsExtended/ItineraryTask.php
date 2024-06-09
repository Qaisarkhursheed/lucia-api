<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;

class ItineraryTask extends \App\Models\ItineraryTask implements IDeveloperPresentationInterface
{
    public function presentForDev(): array
    {
        return $this->only([
            'id',
            'title',
            'deadline',
            'notes',
            'is_completed'
        ]);
    }
}
