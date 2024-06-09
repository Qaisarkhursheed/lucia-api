<?php

namespace App\Repositories\TextractReader\DocumentReaders\Hotels;

use App\ModelsExtended\Amenity;
use App\ModelsExtended\HotelAmenity;
use App\ModelsExtended\HotelRoom;
use App\ModelsExtended\ItineraryHotel;
use App\Repositories\TextractReader\DocumentReaders\DocumentReaderAbstract;
use App\Repositories\TextractReader\DocumentTypeDetector;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * vilavitaparc
 */
class Hotel0005Reader extends Hotel0002Reader
{
    /**
     * @inheritDoc
     */
    public function canRead(array $jsonArray): bool
    {
        return DocumentTypeDetector::passesThresholdOnLines( $jsonArray, [
            "vilavitaparc", "www.vilavitaparc.com", "reservas@vilavitaparc.com"
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
        $booking = $this->createItineraryHotel( "VILA VITA Parc" );

        $booking->confirmation_reference =  $this->getConfirmationNumber ($segment );

        $dates = $this->getDates () ;

        $booking->check_in_date =  $dates[0];
        $booking->check_out_date = $dates[1];

        $booking->check_in_time = null;
        $booking->check_out_time = null;

        $booking->notes = collect( self::extractBetweenLinesExclusiveUsingStartingWith( $segment,
        "Accommodation", "Virtuoso" ) )->implode("\n");

        $booking->cancel_policy = collect( self::extractBetweenLinesExclusiveUsingStartingWith( $segment,
        "Cancellations", "We remain" ) )->implode("\n");

        $booking->hotel_rooms->push($this->createHotelRoom($segment));

        $this->detectAmenities($booking, $segment);

        return $booking;
    }

    protected function createHotelRoom(array $segment)
    {
        $room = new HotelRoom();

        $room->bedding_type = $this->getKeyValueStartingWith("Accommodation");
        $room->room_type = $room->bedding_type;

        $room->guest_name = $this->getKeyValueStartingWith("Name");

        $room->number_of_guests = intval((string)
                                    Str::of($this->getKeyValueStartingWith("No. of Guests"))
                                    ->trim()->substr(0,1)
                                );

        $value = self::getKeyValueStartingWith( "Best available rate" );
        $room->currency_id = $this->detectCurrencyTypeID( $value );


        $value = Str::of(Str::of($value)->trim()->explode("per")->first())
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
        $val = self::getLineStartingWithIndex( $segment,"Reservation N");
        return (string)Str::of($val)->trim()->explode(" ")->last();
    }

    /**
     * @return array
     */
    private function getDates(): array
    {
        $val = self::getKeyValueStartingWith("Arrival Date");
        $val = Str::of( $val )->replace("..",".")->trim()->replace(".", "/");

        $check_in = Carbon::createFromFormat(self::DATE_FORMAT__D__M__YYYY, (string)$val);

        $val = self::getKeyValueStartingWith("Departure Date");
        $val = Str::of( $val )->replace("..",".")->trim()->replace(".", "/");
        $check_out = Carbon::createFromFormat(self::DATE_FORMAT__D__M__YYYY, (string)$val);

        return [
            $check_in, $check_out
        ];
    }

    /**
     * @param ItineraryHotel $booking
     * @param array $segment
     * @return ItineraryHotel
     */
    private function detectAmenities(ItineraryHotel $booking, array $segment): ItineraryHotel
    {
        $values = self::extractBetweenLinesExclusiveUsingStartingWith( $segment,
            "Virtuoso Amenities:" , "Booking Guarantee");
        if( count($values) )
            foreach ($values as $value)
                $booking->hotel_amenities->push(new HotelAmenity([ "amenity" => $value ]));

        return $booking;
    }

}
