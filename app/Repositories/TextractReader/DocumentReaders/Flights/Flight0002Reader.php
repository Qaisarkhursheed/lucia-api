<?php

namespace App\Repositories\TextractReader\DocumentReaders\Flights;

use App\ModelsExtended\ItineraryFlightSegment;
use App\Repositories\TextractReader\DocumentReaders\DocumentReaderAbstract;
use App\Repositories\TextractReader\DocumentTypeDetector;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Flight0002Reader extends Flight0001Reader
{
    /**
     * @inheritDoc
     */
    public function canRead(array $jsonArray): bool
    {
        //--- DETECTING -- 100 % match
        //has line starting with [PREPARED FOR"]
        //has line starting with ["eTicket Receipt"]
        //"RESERVATION CODE",
        //"ISSUE DATE"
        //"Important Legal Notices"
        //
        //OTHER NOTES
        //
        //Operated by:
        //Allowances

        return DocumentTypeDetector::passesThresholdOnLinesStartingWith( $jsonArray, [
            "PREPARED FOR", "eTicket Receipt",
            "RESERVATION CODE", "ISSUE DATE", "Important Legal Notices",
            "OTHER NOTES", "Operated by:", "Allowances"
        ],80);
    }

    protected function getTitle(): string
    {
        return "";
//        return implode(' ',
//            self::extractBetweenLinesExclusiveUsingStartingWith(
//                $this->lines,'Prepared For', 'RESERVATION CODE'
//            ));
    }

    protected function setInnerDateUTC()
    {
        return DocumentReaderAbstract::setInnerDateUTC();
    }

    protected function getConfirmationNumber(): string
    {
        return $this->getKeyValueStartingWith('RESERVATION CODE');
    }

    protected function getPrice(): ?string
    {
        $v = self::getLineIndexStartingWithIndex($this->lines, "Total");
        return  $v === self::INDEX_NOT_FOUND ? null:  $this->lines[ $v+1 ];
    }

    protected function getRawSegments(): array
    {
        $segments = [];

       foreach ( $this->tables as $table )
       {
           if( Str::of(strtolower( $table[0][0]["column1"] ))->startsWith("travel date"   ) )
           {
               $segments[] = collect($table)->skip(1)->toArray();
           }
       }

        return collect($segments)->flatten(1)->toArray();
    }

    protected function processRawSegment(array $segment): ItineraryFlightSegment
    {
        $flightSegment = new ItineraryFlightSegment();

        $line1 = explode( "-", $segment[0]["column1"] );
        $departure_day = trim($line1[0]);
        $arrival_day = count($line1) > 1 ? trim($line1[1]) : $departure_day;

        $line2 = explode( "Operated by:", $segment[1]["column2"] );

        $line2FirstPartExploded = Str::of($line2[0])->trim()->explode(" ");
        $flightSegment->airline = $line2FirstPartExploded
            ->take( $line2FirstPartExploded->count()-2 )->implode(" ");

        $flightSegment->flight_number = $line2FirstPartExploded
            ->reverse()->take(2)->reverse()->implode(" ");

        $flightSegment->airline_operator = trim($line2[1]);

        $line3 = explode( "Time", $segment[2]["column3"] );
        $flightSegment->flight_from = trim($line3[0]);
        $departure_time =  explode( "Terminal", $line3[1] )[0];


        $line4 = explode( "Time", $segment[3]["column4"] );
        $flightSegment->flight_to = trim($line4[0]);
        $arrival_time = explode( "Terminal", $line4[1] )[0];


        // shift the time to the user timezone
        $flightSegment->departure_datetime = Carbon::createFromFormat(self::DATE_FORMAT__DD__MMM__YY, $departure_day )
            ->setTimeFromTimeString($departure_time)
            ->fromUserPreferredTimezoneToAppTimezone($this->itinerary->user);

        // shift the time to the user timezone
        $flightSegment->arrival_datetime = Carbon::createFromFormat(self::DATE_FORMAT__DD__MMM__YY, $arrival_day )
            ->setTimeFromTimeString($arrival_time)
            ->fromUserPreferredTimezoneToAppTimezone($this->itinerary->user);

        //----- Duration
//	-- calculate from time
        $flightSegment->duration_in_minutes = $flightSegment->departure_datetime->diffInMinutes( $flightSegment->arrival_datetime );

        return $flightSegment;
    }

    protected function getFlightPassengers(array $segments): array
    {
        $index = self::getLineIndexStartingWithIndex( $this->lines, "Seat Number" );
        $seat_number = $index !== self::INDEX_NOT_FOUND ? trim(explode("Seat Number", $this->lines[$index])[1]) : null;

        $index = self::getLineIndexStartingWithIndex( $this->lines, "Cabin" );
        $cabin = $index !== self::INDEX_NOT_FOUND ? trim(explode("Cabin", $this->lines[$index])[1]) : null;

        $index = self::getLineIndexStartingWithIndex( $this->lines, "Airline Reservation Code" );
        $ticket_number = $index !== self::INDEX_NOT_FOUND ? trim(explode("Airline Reservation Code", $this->lines[$index])[1]) : null;

        return collect(self::extractBetweenLinesExclusiveUsingStartingWith(
            $this->lines,'Prepared For', 'RESERVATION CODE'
        ))->map(function ($p) use ($seat_number, $cabin, $ticket_number ){
            return  [
                'name' => trim(explode( "[", ltrim( $p, "CIAMPI/" ) )[0]),
                'seat' => $seat_number,
                'class' => $cabin,
                'ticket_number'=> $ticket_number,
                ];
        })->toArray();
    }

    protected function getNotes(): ?string
    {
        // extract Allowances
        $allowances =
            self::extractBetweenLinesExclusiveUsingStartingWith( $this->lines ,
            "Allowances", "Payment/Fare");

        // extract ids
        $identifications =
            self::extractBetweenLinesExclusiveUsingStartingWith( $this->lines ,
            "Positive identification", "Important Legal Notices");

        return implode("\n", array_merge(  $allowances , $identifications  ));
    }
}
