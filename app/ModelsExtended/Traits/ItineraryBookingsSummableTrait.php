<?php

namespace App\ModelsExtended\Traits;

use Carbon\Carbon;

trait ItineraryBookingsSummableTrait
{
    static string $DATE_TIME_DAY_FORMAT = "l, F, d Y h:i A ";

    /**
     * @param Carbon|null $carbon
     * @return string
     */
    public function displayDayDateFormatUTC( ?Carbon $carbon): ?string
    {
        if( ! $carbon ) return null;
        return $carbon->toIso8601String();
//        return $carbon->format( self::$DATE_TIME_DAY_FORMAT ) . env('APP_TIMEZONE');
    }


    /**
     * @param Carbon|null $carbon
     * @return Carbon|null
     */
    public function getDateTimeLocale( ?Carbon $carbon): ?Carbon
    {
        if( ! $carbon ) return $carbon;
        return $carbon->fromAppTimezoneToUserPreferredTimezone( $this->itinerary->user );
    }

    /**
     * @inheritDoc
     */
    public function getSum(): float
    {
        if( ! optional($this)->price ) return 0; // no attribute of price
        return $this->itinerary->show_price_on_share ? $this->price : 0;
    }
}
