<?php

namespace App\Repositories\TextractReader\DocumentReaders\Flights;

use App\ModelsExtended\ItineraryFlightSegment;
use App\Repositories\TextractReader\DocumentTypeDetector;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 *  productiontravel
 */
class Flight0005Reader extends Flight0002Reader
{

    /**
     * @var array|mixed
     */
    private $dataInfoRow;

    /**
     * @inheritDoc
     */
    public function canRead(array $jsonArray): bool
    {
        return DocumentTypeDetector::passesThresholdOnLinesStartingWith( $jsonArray, [
            "ELECTRONIC TICKET RECEIPT", "WE WISH YOU A HAPPY JOURNEY",
            "www.productiontravel.eu",
            "Tear off prior to departure",
        ],71);
    }

    protected function getConfirmationNumber(): string
    {
        return $this->lines[self::getLineIndexStartingWithIndex( $this->lines, "PNR:" )+1];
    }

    protected function getPrice(): ?string
    {
        return null;
    }

    protected function getRawSegments(): array
    {
        $segments = [];

        $indexMatch = 0;
        foreach ( $this->tables as $table )
        {
            if( Str::of(strtolower( $table[0][0]["column1"] ))->contains("passenger:"   )  )
            {
                // data info row
                $this->dataInfoRow = $table;
            }else if( Str::of(strtolower( $table[0][0]["column1"] ))->trim()->startsWith("from"   )  ){
                // real data
                foreach ( collect($table)->skip(1)->toArray() as $item )
                {
                    if( !Str::of(strtolower( $item[0]["column1"] ) )->length())
                        break;
                    $segments[] = $item;
                    $indexMatch++;
                }
                if(count($segments)) break;
            }
        }
        return $segments;
    }

    protected function processRawSegment(array $segment): ItineraryFlightSegment
    {
        $departure_day = $this->carbonParseFromString(
            trim($segment[5]["column6"]) . Carbon::now()->year ,
            self::DATE_FORMAT__DDMMMYYYY, Carbon::now()
        );

        $times = trim($segment[6]["column7"]);

        $departure_time = sprintf("%s:%s",
                Str::of(Str::of($times)->explode("/")->first())->substr(0,2),
                Str::of(Str::of($times)->explode("/")->first())->substr(2,2)
        );

        $arrival_day = $departure_day->clone();
        if( Str::of($times)->contains("+") )
            $arrival_day = $arrival_day->addDays( intval(Str::of($times)->trim()->explode("+")->last()) );

        $arrival_time =sprintf("%s:%s",
            Str::of(Str::of($times)->explode("/")->last())->trim()->substr(0,2),
            Str::of(Str::of($times)->explode("/")->last())->trim()->substr(2,2)
        );

        $flightSegment = new ItineraryFlightSegment();
        $flightSegment->airline = "";
        $flightSegment->airline_operator = "";
        $flightSegment->flight_number = trim($segment[3]["column4"] . $segment[4]["column5"]);

        $flightSegment->flight_from = trim($segment[0]["column1"]);
        $flightSegment->flight_to = trim($segment[1]["column2"]);

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
        $name = $this->lines[self::getLineIndexStartingWithIndex($this->lines, 'SELF SERVICE CODE' )-1];
        $name= Str::of($name)->trim(" ")->replace("/", " ");
        $xName = $name->explode(" ");
        $lastName = $xName->count()? $xName->first(): "";
        $otherName = $xName->count() > 1 ? $xName->skip(1)->implode(" "): "";

       return   [
           [
               'name' => trim( $otherName . " " . $lastName ),
               'seat' => null,
               'class' => null,
               'frequent_flyer_number' => null,
               'ticket_number'=> $this->getKeyValueStartingWith("Ticket"),
           ]
       ];
    }

    protected function getNotes(): ?string
    {
        return null;
    }
}
