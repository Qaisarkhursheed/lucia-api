<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;

class AdvisorRequestActivity extends \App\Models\AdvisorRequestActivity implements IDeveloperPresentationInterface
{
    public function presentForDev(): array
    {
        return [
            "details" => $this->details,
            "type"=> $this->type,
            "created_at" => $this->created_at->toIso8601String(),
        ];
    }
}
