<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IBookingModelInterface;
use App\ModelsExtended\Interfaces\IShiftableBookingInterface;
use App\ModelsExtended\Traits\ShareableSortablePackagerTrait;
use Carbon\Carbon;

class ItineraryDivider extends \App\Models\ItineraryDivider implements IBookingModelInterface
{
    use ShareableSortablePackagerTrait;

    public function notes(): ?string
    {
        return null;
    }

    public function title(): ?string
    {
        return $this->custom_header_title?? $this->categorizeShareableAs();
    }

    public function formatForSharing(): array
    {
        return  [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'target_date' => $this->target_date,
        ];
    }

    public function sortByKey(): Carbon
    {
        return $this->target_date;
    }

    public function moveStartDate(Carbon $newDateLocale): IShiftableBookingInterface
    {
        $this->target_date = $newDateLocale->clone();
        return $this;
    }
}
