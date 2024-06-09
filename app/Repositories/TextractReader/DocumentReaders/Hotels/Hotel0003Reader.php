<?php

namespace App\Repositories\TextractReader\DocumentReaders\Hotels;

use App\ModelsExtended\HotelRoom;
use App\ModelsExtended\ItineraryHotel;
use App\Repositories\TextractReader\DocumentReaders\DocumentReaderAbstract;
use App\Repositories\TextractReader\DocumentTypeDetector;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * "Virgin Hotels Las Vegas <Stay.LasVegas@reservations.virginhotels.com:",
 */
class Hotel0003Reader extends Hotel0002Reader
{
    /**
     * @inheritDoc
     */
    public function canRead(array $jsonArray): bool
    {
        return DocumentTypeDetector::passesThresholdOnLines( $jsonArray, [
            "Virgin Hotels", "reservations.virginhotels.com", "info.lasvegas@vh-lv.com"
        ],80);
    }

    protected function getRawSegments(): array
    {
        return [$this->lines];
    }

    /**
     * @param array $segment
     * @return ItineraryHotel
     */
    protected function processRawSegment(array $segment):ItineraryHotel
    {
        $booking = $this->createItineraryHotel( "Virgin Hotels Las Vegas" );

        $booking->confirmation_reference =  self::getKeyValueStartingWith("Confirmation Number" );

        $check_in_date = self::getKeyValueStartingWith("Arrival Date") ;
        $check_out_date = self::getKeyValueStartingWith("Departure Date") ;

        $booking->check_in_date =  Carbon::createFromFormat( self::DATE_FORMAT__M__D__YYYY, trim($check_in_date) );
        $booking->check_out_date =  Carbon::createFromFormat( self::DATE_FORMAT__M__D__YYYY, trim($check_out_date) );

        $booking->check_in_time = null;
        $booking->check_out_time = null;

        $booking->notes = collect( self::extractBetweenLinesExclusiveUsingStartingWith( $segment,
        "Grand Total for Stay", "Your Virgin Hotels Team" ) )->implode("\n");

        $booking->hotel_rooms->push($this->createHotelRoom($segment));

        return $booking;
    }

    protected function createHotelRoom(array $segment)
    {
        $room = new HotelRoom();

        $room->bedding_type = self::getKeyValueStartingWith("Room Type");

        $room->room_type = $room->bedding_type;

        $room->guest_name = "";

        $room->number_of_guests = 1;

        $value = self::getKeyValueStartingWith("Grand Total for Stay");
        $room->currency_id = $this->detectCurrencyTypeID( $value );

        $room->room_rate = floatval(  (string)Str::of(explode(" ", trim($value))[1])->substr(1) );

        return $room;
    }
}
