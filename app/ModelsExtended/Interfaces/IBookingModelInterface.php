<?php

namespace App\ModelsExtended\Interfaces;

use App\ModelsExtended\Itinerary;
use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int $booking_category_id
 * @property Itinerary $itinerary
 */
interface IBookingModelInterface extends IShiftableBookingInterface, IShareableCategorizedInterface, IHasTitleInterface, IHasNotesInterface
{
    public function calendarStartDate():Carbon;

    public function calendarEndDate():Carbon;
}
