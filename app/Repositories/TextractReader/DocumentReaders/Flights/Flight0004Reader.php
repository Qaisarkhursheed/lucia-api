<?php

namespace App\Repositories\TextractReader\DocumentReaders\Flights;

use App\ModelsExtended\ItineraryFlightSegment;
use App\Repositories\TextractReader\DocumentTypeDetector;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 *  Delta Airlines Receipt
 */
class Flight0004Reader extends Flight0002Reader
{
    private array $dates;

    private array $seats;
    private array $ticketNumbers;

    /**
     * @inheritDoc
     */
    public function canRead(array $jsonArray): bool
    {
        return DocumentTypeDetector::passesThresholdOnLinesStartingWith( $jsonArray, [
            "Delta Air Lines", "DELTA",
            "OUR COMMITMENT TO CLEANLINESS IS HERE TO STAY",
            "Visit delta.com or use the Fly Delta app to view",
        ],80);
    }

    protected function getTitle(): string
    {
        return $this->getKeyValueStartingWith('Name');
    }


    protected function getConfirmationNumber(): string
    {
        return Arr::last(explode(" ",trim(self::getLineStartingWithIndex( $this->lines, "CONFIRMATION #" ))));
    }

    protected function getPrice(): ?string
    {
        return $this->getKeyValueStartingWith('TICKET AMOUNT');
    }

    protected function getRawSegments(): array
    {
        $this->storeDays();
        $this->storeSeats();
        $this->storeTicketNumbers();

        return [$this->lines];
    }

    protected function processRawSegment(array $segment): ItineraryFlightSegment
    {
        $departure_day = Carbon::createFromFormat(self::DATE_FORMAT__DD__MMM__YYYY, $this->dates[0]   );
        $departure_time = $this->dates[1]?? "00:00";

        $arrival_day = $this->readFailoverDate($this->dates[2], $departure_day);
        $arrival_time = $this->dates[3]?? "00:00";


        $flightSegment = new ItineraryFlightSegment();
        $flightSegment->airline = "DELTA";
        $flightSegment->airline_operator = $flightSegment->airline;
        $flightSegment->flight_number = trim($this->getKeyValueStartingWith("FLIGHT"));

        $flightSegment->flight_from = $this->trimFlightOrigin( $this->getKeyValueStartingWith("DEPART") );
        $flightSegment->flight_to = $this->trimFlightOrigin( $this->getKeyValueStartingWith("ARRIVE") );

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

    /**
     * @param string $origin
     * @return string
     */
    private function trimFlightOrigin(string $origin): string
    {
        return (string) Str::of($origin)->substr(0,
            stripos($origin, (string)Str::of( $origin )->match("([0-9])"))
        )->trim();
    }

    protected function getFlightPassengers(array $segments): array
    {
       return   [
           [
               'name' => trim($this->getKeyValueStartingWith( 'Name' )),
               'seat' => $this->seats[0],
               'class' => "Economy",
               'ticket_number'=> $this->ticketNumbers[0],
           ]
       ];
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
            $bIndex = self::getLineIndexStartingWithIndex($this->lines, "DEPART", 0);
            if( $bIndex !== self::INDEX_NOT_FOUND )
            {
                $valBefore = $this->lines[$bIndex-1];

                $this->dates[] = Str::of( $valBefore )->substr(-5, 2) . " " .
                    Str::of( $valBefore )->substr(-3). " " .
                    $this->innerDateUTC->year;

                $valBefore = $this->getKeyValueStartingWith("DEPART");
                preg_match( self::HR_12_REGEX, (string) Str::of( $valBefore )->replace(": ", ":")->replace(" ", ""), $matches );
                $this->dates[] = Arr::first($matches);


                $valBefore = $this->getKeyValueStartingWith("ARRIVE");
                $this->dates[] = Str::of( $valBefore )->trim()->substr(-5, 2) . " " .
                    Str::of( $valBefore )->substr(-3). " " .
                    $this->innerDateUTC->year;

                preg_match( self::HR_12_REGEX, (string) Str::of( $valBefore )->replace(": ", ":")->replace(" ", ""), $matches );
                $this->dates[] = Arr::first($matches);

            }
    }

    private function storeTicketNumbers()
    {
        // store dates
        $this->ticketNumbers = [
              $this->getKeyValueStartingWith('Ticket #')
        ];
    }

    private function storeSeats()
    {
        // store seats
        $this->seats = [
            $this->getKeyValueStartingWith("SEAT")
        ];
    }

    /**
     * @param string $val
     * @param Carbon $failover
     * @return Carbon|false
     */
    private function readFailoverDate(string $val, Carbon $failover)
    {
        try {
            return Carbon::createFromFormat(self::DATE_FORMAT__DD__MMM__YYYY, $val   );
        }catch(\Exception $exception){
            return $failover;
        }
    }
}
