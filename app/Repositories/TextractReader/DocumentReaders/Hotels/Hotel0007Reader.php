<?php

namespace App\Repositories\TextractReader\DocumentReaders\Hotels;

use App\ModelsExtended\HotelAmenity;
use App\ModelsExtended\HotelRoom;
use App\ModelsExtended\ItineraryHotel;
use App\Repositories\TextractReader\DocumentTypeDetector;
use Illuminate\Support\Str;

/**
 * San Domenico Palace
 */
class Hotel0007Reader extends Hotel0002Reader
{
    /**
     * @inheritDoc
     */
    public function canRead(array $jsonArray): bool
    {
        return DocumentTypeDetector::passesThresholdOnLines( $jsonArray, [
            "San Domenico Palace", "Taormina", "Piazza San Domenico",
            "GUARANTEE, DEPOSIT AND CANCELLATION POLICIES",
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
        $booking = $this->createItineraryHotel( "San Domenico Palace, Taormina" );

        $booking->confirmation_reference =  $this->getConfirmationNumber ($segment );

        $dates = $this->getDates () ;

        $booking->check_in_date =  $dates[0];
        $booking->check_out_date = $dates[1];

        $booking->check_in_time = trim(self::getKeyValueStartingWith("Hotel check in"));
        $booking->check_out_time = trim(self::getKeyValueStartingWith("Hotel check out"));

        $booking->notes = null;

        $booking->cancel_policy = collect( self::extractBetweenLinesExclusiveUsingStartingWith( $segment,
        "GUARANTE", "Disclaimers" ) )->implode("\n");

        $booking->hotel_rooms->push($this->createHotelRoom($segment));

        $this->detectAmenities($booking, $segment);

        return $booking;
    }

    protected function createHotelRoom(array $segment)
    {
        $room = new HotelRoom();

        $index = self::getLineIndexStartingWithIndex($segment, "STATUS");
        $room->bedding_type = strlen($segment[$index+2]) > 5 ? $segment[$index+2] : $segment[$index+3];
        $room->room_type = $segment[$index+1];

        $room->guest_name = (string)Str::of(self::getLineStartingWithIndex( $segment,"Dear "))
            ->trim("Dear ")->rtrim(",");

        $index = self::getLineIndexContaining($segment, " adults");
        $room->number_of_guests = intval((string)
                                    Str::of($segment[$index])
                                        ->trim()->replace(" adults", "")
                                        ->explode(" ")->last()
                                    );

        $index = self::getLineIndexContaining($segment, "Room charge");
        $value = Str::of( $segment[$index+1] );
        $room->currency_id = $this->detectCurrencyTypeID( (string)$value );

        $room->room_rate = $this->parseCurrencyToFloat($value->trim()->explode(" ")->last());

        return $room;
    }

    /**
     * @param ItineraryHotel $booking
     * @param array $segment
     * @return ItineraryHotel
     */
    private function detectAmenities(ItineraryHotel $booking, array $segment): ItineraryHotel
    {
        $values = self::extractBetweenLinesExclusiveUsingStartingWith( $segment,
            "Hotel/Resort Credit" , "Upgrade of one category");
        if( count($values) )
            foreach ($values as $value)
                $booking->hotel_amenities->push(new HotelAmenity([ "amenity" => $value ]));

        return $booking;
    }

    /**
     * @param array $segment
     * @return string
     */
    private function getConfirmationNumber(array $segment): string
    {
        return self::getKeyValueStartingWith( "CONFIRMATION");
    }

    /**
     * @return array
     */
    private function getDates(): array
    {
        $check_in = $this->carbonParseFromString(
            trim($this->getKeyValueStartingWith("Arrival")),
            "l, F j, Y",
            $this->itinerary->start_date
        );

        $check_out = $this->carbonParseFromString(
            trim($this->getKeyValueStartingWith("Departure")),
            "l, F j, Y",
            $this->itinerary->start_date
        );

        return [
            $check_in, $check_out
        ];
    }

}
