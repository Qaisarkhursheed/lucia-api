<?php

namespace App\Repositories\TextractReader\DocumentReaders\Flights;

use App\ModelsExtended\Airline;
use App\ModelsExtended\Airport;
use App\ModelsExtended\ItineraryFlightSegment;
use App\Repositories\TextractReader\DocumentTypeDetector;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 *  Delta Airlines PDF
 */
class Flight0007Reader extends Flight0002Reader
{
    /**
     * @inheritDoc
     */
    public function canRead(array $jsonArray): bool
    {
        return DocumentTypeDetector::passesThresholdOnLines( $jsonArray, [
            "Delta Air Lines", "DELTA",
            "FLIGHT CONFIRMATION",  "PASSENGER INFORMATION","Complete Delta Air Lines Baggage Information",
            "FLIGHTS",
        ],90);
    }

    protected function getConfirmationNumber(): string
    {
        return self::getKeyValueStartingWith("FLIGHT CONFIRMATION");
    }

    protected function getPrice(): ?string
    {
        return null;
    }

    protected function getRawSegments(): array
    {
        $segments = [];

        foreach ( $this->tables as $table )
        {
            if( count($table) > 2  &&
                Str::of(strtolower( $table[1][1]["column2"] ))->startsWith("name"   ))
            {
                // flight reading
                foreach (collect( $table)->skip(2) as $realTable)
                {
                    $segments[] = $realTable;
                }
                break;
            }
        }

        return $segments;
    }

    protected function processRawSegment(array $segment): ItineraryFlightSegment
    {
        $index = self::getLineIndexStartingWith($this->lines, "FLIGHT CONFIRMATION");
        $dates = explode(", ", $this->lines[$index+1] );
        $departure_day = $this->carbonParseFromString( (string)Str::of($dates[1])->substr(0,11),
            self::DATE_FORMAT__DD__MMM__YYYY, Carbon::now()
        );
        $arrival_day = $this->carbonParseFromString( (string)Str::of($dates[2])->substr(0,11),
            self::DATE_FORMAT__DD__MMM__YYYY, Carbon::now()
        );

        $departure_time = trim((string)Str::of(
            self::getLineStartingWithIndex($this->lines, "DEPART:"))
            ->replace("DEPART:", "")->trim());

        $arrival_time = trim((string)Str::of(
            self::getLineStartingWithIndex($this->lines, "ARRIVE:"))
            ->replace("ARRIVE:", "")->trim());

        $airline = Airline::findByIata("DL");

        $location = explode(" to ", trim(self::getKeyValueStartingWith("FLIGHT")));

        $from_airport = Airport::findByIata(trim($location[0]));
        $to_airport = Airport::findByIata(trim($location[1]));


        $flightSegment = new ItineraryFlightSegment();
        $flightSegment->airline = $airline->name;
        $flightSegment->airline_operator = "";
        $flightSegment->flight_number = (string)Str::of(
            self::getLineStartingWithIndex($this->lines, "FLIGHT DL")
        )->trim()->ltrim("FLIGHT ");

        $flightSegment->flight_from = $from_airport->name;
        $flightSegment->flight_to = $to_airport->name;

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

        $flightSegment->passenger =  [
            'name' =>  Str::of($segment[1]["column2"])->explode("SkyMiles")->first(),
            'seat' => Str::of($segment[3]["column4"])->trim()->explode(" ")->last(),
            'class' => trim($segment[3]["column4"]),
            'frequent_flyer_number' => Str::of(Str::of($segment[1]["column2"])->explode("SkyMiles #")->last())
            ->trim()->explode(" ")->first(),
            'ticket_number'=> Str::of($segment[1]["column2"])->trim()->explode(" ")->last(),
        ];

        // store seat details
        return $flightSegment;
    }

    /**
     * @param array|ItineraryFlightSegment[] $segments
     * @return array
     */
    protected function getFlightPassengers(array $segments): array
    {
        return collect($segments)->map(function (ItineraryFlightSegment $segment){
            return $segment->passenger;
        })->toArray();
    }

    protected function getNotes(): ?string
    {
        return null;
    }
}
