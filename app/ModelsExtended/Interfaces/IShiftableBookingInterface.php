<?php

namespace App\ModelsExtended\Interfaces;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

interface IShiftableBookingInterface
{
    /**
     * Doesn't affect timezones. It assumes it is locale date even thou the timezone maybe UTC.
     * It only uses the date day part
     * Moves the Start Date but doesn't save it in database
     *
     * @param Carbon $newDateLocale
     * @return IShiftableBookingInterface | ModelBase
     */
    public function moveStartDate( Carbon $newDateLocale ):IShiftableBookingInterface;
}
