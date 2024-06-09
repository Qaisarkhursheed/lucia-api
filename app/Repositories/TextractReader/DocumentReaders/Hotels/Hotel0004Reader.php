<?php

namespace App\Repositories\TextractReader\DocumentReaders\Hotels;

use App\ModelsExtended\HotelRoom;
use App\ModelsExtended\ItineraryHotel;
use App\Repositories\TextractReader\DocumentReaders\DocumentReaderAbstract;
use App\Repositories\TextractReader\DocumentTypeDetector;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * VERRIDE
 */
class Hotel0004Reader extends Hotel0002Reader
{
    /**
     * @inheritDoc
     */
    public function canRead(array $jsonArray): bool
    {
        return DocumentTypeDetector::passesThresholdOnLines( $jsonArray, [
            "VERRIDE", "S V C", "verridesc.pt"
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
        $booking = $this->createItineraryHotel( "VERRIDE SVC" );

        $booking->confirmation_reference =  $this->getConfirmationNumber ($segment );

        $dates = $this->getDates ($segment) ;

        $booking->check_in_date =  $dates[0];
        $booking->check_out_date = $dates[1];

        $booking->check_in_time = null;
        $booking->check_out_time = null;

        $booking->notes = collect( self::extractBetweenLinesExclusiveUsingStartingWith( $segment,
        "The prices", "Warmest" ) )->implode("\n");

        $booking->hotel_rooms->push($this->createHotelRoom($segment));

        return $booking;
    }

    protected function createHotelRoom(array $segment)
    {
        $room = new HotelRoom();

        $index = self::getLineIndexStartingWithIndex( $segment, "From:" ) + 1;
        $room->bedding_type = $segment[$index];
        $room->room_type = $room->bedding_type;

        $index = self::getLineIndexStartingWithIndex( $segment, "Folio" ) + 1;
        $room->guest_name = $segment[$index];

        $room->number_of_guests = 1;

        $value = self::getLineStartingWithIndex( $segment, "Daily rate" );
        $room->currency_id = $this->detectCurrencyTypeID( $value );


        $value = Str::of(Str::of($value)->ltrim("Daily rate:")->explode("|")->first())
            ->trim();
        $room->room_rate = $this->parseCurrencyToFloat($value->substr(0, $value->length()-1));

        return $room;
    }

    /**
     * @param array $segment
     * @return string
     */
    private function getConfirmationNumber(array $segment): string
    {
        $val = self::getLineStartingWithIndex( $segment,"Folio");
        return (string)Str::of($val)->trim()->explode(" ")->last();
    }

    /**
     * @param array $segment
     * @return array
     */
    private function getDates(array $segment): array
    {
        $val = self::getLineStartingWithIndex( $segment,"From:");
        $chunks =  Str::of($val)->substr(5)->trim()->explode("until");

        $check_in = Carbon::createFromFormat(
            self::DATE_FORMAT__MMMM__DDT__YYYY, trim($chunks->first()) . " " . $this->innerDateUTC->year
        );

        if( $chunks->count() > 1 )
        {
            $check_out =
                Carbon::createFromFormat(
                    self::DATE_FORMAT__MMMM__DDT__YYYY,
                    (string)Str::of(Str::of( $chunks->last() )->explode("(")->first())->trim()
                );
        }else $check_out = $check_in;

       return [
           $check_in, $check_out
       ];
    }

}
