<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;

class CopilotRatingsCategorized extends \App\Models\CopilotRatingsCategorized implements IDeveloperPresentationInterface
{

    public function presentForDev(): array
    {
        return [
            'average_rating' => $this->average_rating,
            'review_count' =>  $this->review_count
        ];
    }
}
