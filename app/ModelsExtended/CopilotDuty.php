<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;

class CopilotDuty extends \App\Models\CopilotDuty implements IDeveloperPresentationInterface
{

    /**
     * @inheritDoc
     */
    public function presentForDev(): array
    {
        return [
            "id" => $this->id,
            "advisor_request_type_id" => $this->advisor_request_type_id,
            "advisor_request_type" => $this->advisor_request_type->description,
        ];
    }
}
