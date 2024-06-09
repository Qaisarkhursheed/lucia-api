<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;

class CopilotAverageFeedback extends \App\Models\CopilotAverageFeedback implements IDeveloperPresentationInterface
{
    public function presentForDev(): array
    {
        return [
            'average_rating' => round($this->average_rating,1,PHP_ROUND_HALF_UP),
            'review_count' =>  $this->review_count
        ];
    }
}
