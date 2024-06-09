<?php

namespace App\Repositories\TextractReader\DocumentReaders\Flights;

use App\ModelsExtended\ItineraryFlightSegment;
use App\Repositories\TextractReader\DocumentTypeDetector;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Flight0003Reader extends Flight0002Reader
{
    private array $dates;

    private array $seats;
    private array $ticketNumbers;

    /**
     * @inheritDoc
     */
    public function canRead(array $jsonArray): bool
    {
//--- DETECTING -- 100 % match
//has line starting with [PREPARED FOR"]
//has line starting with [Electronic Invoice"]
//"Departs"
//"Arrives"
//"SALES PERSON"
//"INVOICE NUMBER",
//"RECORD LOCATOR"
//"Total base fare amount"



        return DocumentTypeDetector::passesThresholdOnLinesStartingWith( $jsonArray, [
            "PREPARED FOR", "Departs",
            "Electronic Invoice", "Arrives", "SALES PERSON",
            "INVOICE NUMBER", "RECORD LOCATOR", "Total base fare amount"
        ],80);
    }

    protected function getTitle(): string
    {
        return $this->getKeyValueStartingWith('Prepared For');
    }

    protected function setInnerDateUTC()
    {
        $this->innerDateUTC =
            Carbon::createFromFormat(self::DATE_FORMAT__DD__MMM__YYYY,
                trim($this->getKeyValueStartingWith('INVOICE ISSUE DATE')) );
        return $this;
    }

    protected function getConfirmationNumber(): string
    {
        return $this->getKeyValueStartingWith('RECORD LOCATOR');
    }

    protected function getPrice(): ?string
    {
        return $this->getKeyValueStartingWith('Net Credit Card Billing');
    }

    protected function getRawSegments(): array
    {
        $segments = [];

        $this->storeDays();
        $this->storeSeats();
        $this->storeTicketNumbers();

        $indexMatch = 0;
       foreach ( $this->tables as $table )
       {
           if( Str::of(strtolower( $table[0][0]["column1"] ))->startsWith("flight"   ) )
           {
               $segments[] = [
                   "table" => $table,
                   "day" =>  $this->dates[$indexMatch],
               ];

               $indexMatch++;
           }
       }

        return $segments;
    }

    protected function processRawSegment(array $segment): ItineraryFlightSegment
    {
        $departure_day = Carbon::createFromFormat(self::DATE_FORMAT__YYYY__MMM__DD, $this->innerDateUTC->year . " " .  $segment["day"]   );
        $table = $segment["table"];

        $flightSegment = new ItineraryFlightSegment();
        $flightSegment->airline = trim($table[0][1]["column2"]);
        $flightSegment->airline_operator = $flightSegment->airline;
        $flightSegment->flight_number = Str::of($flightSegment->airline)->explode(" ")->reverse()->take(2)->reverse()->implode(" ");

        $flightSegment->flight_from = trim($table[1][1]["column2"]);
        $departure_time = trim($table[1][3]["column4"]);


        $flightSegment->flight_to = trim($table[2][1]["column2"]);
        $column4 = explode( " (", trim($table[2][3]["column4"]) ) ;
        $arrival_time = $column4[0];

        $addDaysToArrival = count($column4) > 1 && Str::of($column4[1])->contains("+1")? 1 : 0;

        $arrival_day = $addDaysToArrival > 0 ? $departure_day->clone()->addDays($addDaysToArrival) : $departure_day->clone() ;


        // shift the time to the user timezone
        $flightSegment->departure_datetime = $departure_day
            ->setTimeFromTimeString($departure_time)
            ->fromUserPreferredTimezoneToAppTimezone($this->itinerary->user);

        // shift the time to the user timezone
        $flightSegment->arrival_datetime = $arrival_day
            ->setTimeFromTimeString($arrival_time)
            ->fromUserPreferredTimezoneToAppTimezone($this->itinerary->user);

        //----- Duration
//	-- calculate from time
        $flightSegment->duration_in_minutes = $flightSegment->departure_datetime->diffInMinutes( $flightSegment->arrival_datetime );


        // store seat details


        return $flightSegment;
    }

    protected function getFlightPassengers(array $segments): array
    {
        $index = self::getLineIndexStartingWithIndex( $this->lines, "Cabin" );
        $cabin = $index !== self::INDEX_NOT_FOUND ? trim($this->lines[$index+1]) : null;

        return collect(
            // reindex
            array_values(
                self::extractBetweenLinesExclusiveUsingStartingWith($this->lines,'Prepared For', 'SALES PERSON')
                ))->map(function ($p, $key) use ( $cabin ){
            return  [
                'name' => trim(explode( "[", ltrim( $p, "CIAMPI/" ) )[0]),
                'seat' => array_key_exists( $key, $this->seats )? $this->seats[$key] : null,
                'class' => $cabin,
                'ticket_number'=> array_key_exists( $key, $this->ticketNumbers )? $this->ticketNumbers[$key] : null,
            ];
        })->toArray();
    }

    protected function getNotes(): ?string
    {
        return implode("\n", self::extractBetweenLinesExclusiveUsingStartingWith( $this->lines ,
            "Notes", "DATE:"));
    }

    private function storeDays()
    {
        // store dates
        $this->dates = [];
        $bIndex = 0;
        do{
            $bIndex = self::getLineIndexStartingWithIndex($this->lines, "DATE:", $bIndex);
            if( $bIndex !== self::INDEX_NOT_FOUND )
            {
                $this->dates[] = trim(explode( ",", $this->lines[$bIndex] )[1]);
                $bIndex++;
            }
        }while( $bIndex!== self::INDEX_NOT_FOUND);
    }


    private function storeTicketNumbers()
    {
        // store dates
        $this->ticketNumbers = [];
        $bIndex = 0;
        do{
            $bIndex = self::getLineIndexStartingWithIndex($this->lines, "Ticket Number", $bIndex);
            if( $bIndex !== self::INDEX_NOT_FOUND )
            {
                $this->ticketNumbers[] = trim($this->lines[$bIndex+1]);
                $bIndex++;
            }
        }while( $bIndex!== self::INDEX_NOT_FOUND);
    }

    private function storeSeats()
    {
        // store seats
        $this->seats = [];
        $bIndex = 0;
        do{
            $bIndex = self::getLineIndexStartingWithIndex($this->lines, "Seat(s) ", $bIndex);
            if( $bIndex !== self::INDEX_NOT_FOUND && !Str::of( $this->lines[$bIndex] )->contains("etails") )
            {
                $this->seats[] = trim(explode( " ", $this->lines[$bIndex] )[1]);
            }

            if( $bIndex !== self::INDEX_NOT_FOUND )
            {
                $bIndex++;
            }
        }while( $bIndex!== self::INDEX_NOT_FOUND);
    }
}
