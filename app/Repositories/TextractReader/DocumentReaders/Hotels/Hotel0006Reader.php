<?php

namespace App\Repositories\TextractReader\DocumentReaders\Hotels;

use App\ModelsExtended\HotelRoom;
use App\ModelsExtended\ItineraryHotel;
use App\Repositories\TextractReader\DocumentTypeDetector;
use Illuminate\Support\Str;

/**
 * HOTEL DE RUSSIE
 */
class Hotel0006Reader extends Hotel0002Reader
{
    /**
     * @inheritDoc
     */
    public function canRead(array $jsonArray): bool
    {
        return DocumentTypeDetector::passesThresholdOnLines( $jsonArray, [
            "HOTEL DE RUSSIE", "ROMA", "A ROCCO FORTE HOTEL", "CONFIRMATION REF",
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
        $booking = $this->createItineraryHotel( "HOTEL DE RUSSIE" );

        $booking->confirmation_reference =  $this->getConfirmationNumber ($segment );

        $dates = $this->getDates () ;

        $booking->check_in_date =  $dates[0];
        $booking->check_out_date = $dates[1];

        $booking->check_in_time = null;
        $booking->check_out_time = null;

        $booking->notes = collect( self::extractBetweenLinesInclusiveUsingStartingWith( $segment,
        "RATE POLICY", "MODIFICATION" ) )->implode("\n");

        $booking->cancel_policy = collect( self::extractBetweenLinesInclusiveUsingStartingWith( $segment,
        "MODIFICATION", "For the best" ) )->implode("\n");

        $booking->hotel_rooms->push($this->createHotelRoom($segment));

        return $booking;
    }

    protected function createHotelRoom(array $segment)
    {
        $room = new HotelRoom();

        $room->bedding_type = trim(Str::of(self::getLineStartingWithIndex( $segment,"ROOM CATEGORY"))->explode(":")->last());
        $room->room_type = $room->bedding_type;

        $room->guest_name = $this->getKeyValueStartingWith("Name");

        $room->number_of_guests = intval((string)
                                    Str::of(self::getLineStartingWithIndex($segment,"ADULTS:"))
                                        ->trim()->explode(":")->last()
                                    )
                                    +
                                    intval((string)
                                    Str::of(self::getLineStartingWithIndex($segment,"CHILDREN:"))
                                        ->trim()->explode(":")->last()
                                    );

        $value = self::getKeyValueStartingWith( "TOTAL COST" );
        $room->currency_id = $this->detectCurrencyTypeID( $value );


        $value = Str::of($value)->trim()->explode(" ")->first();
        $room->room_rate = $this->parseCurrencyToFloat($value);

        return $room;
    }

    /**
     * @param array $segment
     * @return string
     */
    private function getConfirmationNumber(array $segment): string
    {
        return self::getKeyValueStartingWith( "CONFIRMATION REF");
    }

    /**
     * @return array
     */
    private function getDates(): array
    {
        $check_in = $this->carbonParseFromString(
            trim($this->getKeyValueStartingWith("ARRIVAL")),
            self::DATE_FORMAT__DD__MM__YYYY,
            $this->itinerary->start_date
        );

        $check_out = $this->carbonParseFromString(
            trim($this->getKeyValueStartingWith("DEPARTURE")),
            self::DATE_FORMAT__DD__MM__YYYY,
            $this->itinerary->start_date
        );

        return [
            $check_in, $check_out
        ];
    }

}
