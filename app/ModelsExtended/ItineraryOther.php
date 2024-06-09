<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IBookingModelInterface;
use App\ModelsExtended\Interfaces\ICanCreateGoogleCalendarEventInterface;
use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use App\ModelsExtended\Interfaces\IShiftableBookingInterface;
use App\ModelsExtended\Traits\BelongsToItineraryTrait;
use App\ModelsExtended\Traits\CanCreateGoogleCalendarEventTrait;
use App\ModelsExtended\Traits\ItineraryBookingsSummableTrait;
use App\ModelsExtended\Traits\ShareableSortablePackagerTrait;
use Carbon\Carbon;

class ItineraryOther extends \App\Models\ItineraryOther implements IBookingModelInterface,
    ICanCreateGoogleCalendarEventInterface, IDeveloperPresentationInterface
{
    use BelongsToItineraryTrait, ItineraryBookingsSummableTrait, ShareableSortablePackagerTrait, CanCreateGoogleCalendarEventTrait;

    /**
     * @inheritDoc
     */
    public function formatForSharing():array
    {
        return  [
            'id' => $this->id,
            'title' => $this->title,
            'custom_header_title' => $this->custom_header_title,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'notes' => $this->notes,
        ];
    }

    /**
     * @inheritDoc
     */
    public function sortByKey(): Carbon
    {
        return $this->target_date;
    }

    /**
     * @inheritDoc
     */
    public function moveStartDate(Carbon $newDateLocale ): IShiftableBookingInterface
    {
        // No Time reference here for now
        $this->target_date = $newDateLocale->clone();

        return $this;
    }

    public function title(): ?string
    {
        return  $this->custom_header_title?? $this->title;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    /**
     * @inheritDoc
     */
    public function createCalendarEvent(bool $updateQuietly = true)
    {
        // TODO: make sure it is time in UTC
        // To preserve time, set to 08:00 time
        return $this->createCalendarEventDayForBooking(
            $this->target_date,
            $this->target_date,
            $updateQuietly
        );
    }
}
