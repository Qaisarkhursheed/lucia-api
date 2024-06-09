<?php

namespace App\Repositories\TextractReader\DocumentReaders\Flights;

use App\ModelsExtended\Airline;
use App\ModelsExtended\Airport;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\HotelRoom;
use App\ModelsExtended\ItineraryFlight;
use App\ModelsExtended\ItineraryFlightSegment;
use App\ModelsExtended\ItineraryHotel;
use App\ModelsExtended\ItineraryInsurance;
use App\ModelsExtended\ItineraryTransport;
use App\ModelsExtended\TransitType;
use App\Repositories\TextractReader\DocumentTypeDetector;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 *  Tres Technologies
 */
class Flight0006Reader extends Flight0002Reader
{
    /**
     * @inheritDoc
     */
    public function canRead(array $jsonArray): bool
    {
        return DocumentTypeDetector::passesThresholdOnLinesStartingWith( $jsonArray, [
            "Tres Technologies", "Sub Reservations",
            "Tour",
            "Travel Category",
        ],71);
    }

    protected function getConfirmationNumber(): string
    {
        return self::getKeyValueStartingWith("Confirmation");
    }

    protected function getPrice(): ?string
    {
        return self::getKeyValueStartingWith("Total Fare");
    }

    protected function getRawSegments(): array
    {
        $segments = [];

        $this->extractHotelSegment();
        $this->extractInsuranceSegment();
        $this->extractTransferSegment();

        $loopIndex = 0;
        foreach ( $this->tables as $table )
        {
            if( count($table) > 1  &&
                Str::of(strtolower( $table[1][0]["column1"] ))->startsWith("air"   ))
            {
                // flight reading
                $innerLoop = $loopIndex+1;
                while (Str::of(strtolower( $this->tables[$innerLoop][0][0]["column1"] ))->startsWith("depart date"))
                {
                    foreach ($this->tables[$innerLoop] as $flightRow)
                    {
                        if( Str::of(strtolower( $flightRow[0]["column1"] ))->startsWith("depart date") ) continue;
                        $segments[] = $flightRow;
                    }

                    $innerLoop++;
                }
            }
            $loopIndex++;
        }

        return $this->compressFlightSegments($segments);
    }

    protected function processRawSegment(array $segment): ItineraryFlightSegment
    {
        $departure_day = $this->carbonParseFromString(
            (string)Str::of($segment["departure"])->replace("  ", " ")->trim()->explode(",")->first(),
            "n/j/y", Carbon::now()
        );
        $departure_time = trim((string)Str::of($segment["departure"])->replace("  ", " ")->trim()->explode(",")->last());

        $arrival_day = $this->carbonParseFromString(
            (string)Str::of($segment["arrival"])->replace("  ", " ")->trim()->explode(",")->first(),
            "n/j/y", Carbon::now()
        );
        $arrival_time = trim((string)Str::of($segment["arrival"])->replace("  ", " ")->trim()->explode(",")->last());

        $airline = Airline::findByIata(trim($segment["provider_code"]));

        $from_airport = Airport::findByIata(trim($segment["from_code"]));
        $to_airport = Airport::findByIata(trim($segment["to_code"]));

        $flightSegment = new ItineraryFlightSegment();
        $flightSegment->airline = $airline->name;
        $flightSegment->airline_operator = "";
        $flightSegment->flight_number = trim($segment["provider_code"]) . trim($segment["flight_number"]);

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

        $flightSegment->cabin = trim($segment["class"]);

        // store seat details
        return $flightSegment;
    }

    protected function getFlightPassengers(array $segments): array
    {
        // search through the lines for where it starts with
        // Travelers:

        return collect($this->lines)->filter(function (string $v){
           return Str::of($v)->startsWith("Travelers:");
        })->map(function ( string $v) use ($segments){
            $name= Str::of($v)->ltrim("Travelers:")
                ->trim(" ")->replace("/", " ");
            $xName = $name->explode(" ");
            $lastName = $xName->count()? $xName->first(): "";
            $otherName = $xName->count() > 1 ? $xName->skip(1)->implode(" "): "";

            return [
                'name' => trim( $otherName . " " . $lastName ),
                'seat' => null,
                'class' => $segments[0]->cabin,
                'frequent_flyer_number' => null,
                'ticket_number'=> $this->getKeyValueStartingWith("Confirmation"),
            ];
        })->values()->toArray();
    }

    protected function getNotes(): ?string
    {
        return null;
    }

    /**
     * @param array $segments
     * @return array
     */
    private function compressFlightSegments(array $segments): array
    {
        //^ array:11 [
        //  0 => array:1 [
        //    "column1" => "Depart Date / Time "
        //  ]
        //  1 => array:1 [
        //    "column2" => "Depart City Name "
        //  ]
        //  2 => array:1 [
        //    "column3" => "Depart City Code "
        //  ]
        //  3 => array:1 [
        //    "column4" => "Arrive Date / Time "
        //  ]
        //  4 => array:1 [
        //    "column5" => "Arrive City Name "
        //  ]
        //  5 => array:1 [
        //    "column6" => "Arrive City Code "
        //  ]
        //  6 => array:1 [
        //    "column7" => "Flight No "
        //  ]
        //  7 => array:1 [
        //    "column8" => "Provider Code "
        //  ]
        //  8 => array:1 [
        //    "column9" => "Class of Service "
        //  ]
        //  9 => array:1 [
        //    "column10" => "Connection "
        //  ]
        //  10 => array:1 [
        //    "column11" => "Mileage "
        //  ]
        //]

        $result = [];
        foreach ($segments as $row)
        {
            if( strlen( $row[0]["column1"]) > 7) // 12 full, 7 part
            {
                $result[] = [
                    "departure" => $row[0]["column1"],
                    "from_city" => $row[1]["column2"],
                    "from_code" => $row[2]["column3"],
                    "arrival" => $row[3]["column4"],
                    "to_city" => $row[4]["column5"],
                    "to_code" => $row[5]["column6"],
                    "flight_number" => $row[6]["column7"],
                    "provider_code" => $row[7]["column8"],
                    "class" => $row[8]["column9"],
                ];
            }else if( count($result)) {
                $last_index = count($result)-1;
                $iRow = $result[$last_index];
                $result[$last_index] = [
                    "departure" =>  $iRow["departure"] . " " .  $row[0]["column1"],
                    "from_city" =>  $iRow["from_city"] . " " .  $row[1]["column2"],
                    "from_code" =>  $iRow["from_code"] . " " .  $row[2]["column3"],
                    "arrival" =>  $iRow["arrival"] . " " .  $row[3]["column4"],
                    "to_city" =>  $iRow["to_city"] . " " .  $row[4]["column5"],
                    "to_code" =>  $iRow["to_code"] . " " .  $row[5]["column6"],
                    "flight_number" =>  $iRow["flight_number"] . " " .  $row[6]["column7"],
                    "provider_code" =>  $iRow["provider_code"] . " " .  $row[7]["column8"],
                    "class" =>  $iRow["class"] . " " .  $row[8]["column9"],
                ];
            }
        }

        return $result;
    }

    private function extractHotelSegment()
    {
        $hotelSegment = self::extractBetweenLinesExclusiveUsingStartingWith( $this->lines, "Hotel", "Service Fee" );
        if( count($hotelSegment) > 3 )
        {
            // reindex
            $hotelSegment = array_values($hotelSegment);

            try {

                $property = self::getLineStartingWithIndex( $hotelSegment , "Property");
                $property =  strip_tags(html_entity_decode($property)); // clean

                $booking = new ItineraryHotel();
                $booking->booking_category_id = BookingCategory::Hotel;
                $booking->itinerary_id = $this->itinerary->id;
                $booking->sorting_rank = self::DEFAULT_SORTING_RANK;
                $booking->custom_header_title = trim(Str::of( $property )->explode(":")->skip(1)->first());

                $booking->confirmation_reference =  self::getKeyValueStartingWith("Confirmation");

                $dates = Str::of($hotelSegment[0])->explode("M") ;
                $booking->check_in_date =  $this->carbonParseFromString(
                    (string)Str::of($dates->first())->explode(",")->first(),
                    "n/j/y", Carbon::now()
                );

                $booking->check_out_date = $this->carbonParseFromString(
                    (string)Str::of($dates->get(1))->trim()->explode(",")->first(),
                    "n/j/y", Carbon::now()
                );

                $booking->check_in_time =   Str::of($dates->first())->explode(",")->last() . "M";
                $booking->check_out_time = Str::of($dates->get(1))->explode(",")->last() . "M";



                $room = new HotelRoom();
                $room->bedding_type = Str::of(self::getLineStartingWithIndex($hotelSegment, "Room Description"))->
                                        explode(";")
                                        ->first();
                $room->bedding_type = trim(Str::of($room->bedding_type)->explode(":")->last());

                $room->room_type = $room->bedding_type;
                $room->guest_name = null;
                $room->number_of_guests = 1;

                $value = Str::of($hotelSegment[1] );
                $room->currency_id = $this->detectCurrencyTypeID( (string)$value );
                $room->room_rate = $this->parseCurrencyToFloat($value->substr(1));

                $booking->hotel_rooms->push($room);

                $this->hotels[] = $booking;

            }catch (\Exception $exception){
                info("Error parsing hotel under flight 6: " . $exception->getMessage());
            }
        }
    }

    private function extractInsuranceSegment()
    {
        $segment = self::extractBetweenLinesExclusiveUsingStartingWith( $this->lines, "Insurance", "Transfer" );
        if( count($segment) > 2 )
        {
            // reindex
            $segment = array_values($segment);

            try {

                $property = self::getLineContainingWithIndex( $segment , "Description");
                $property =  strip_tags(html_entity_decode($property)); // clean

                $booking = new ItineraryInsurance();
                $booking->booking_category_id = BookingCategory::Insurance;
                $booking->itinerary_id = $this->itinerary->id;
                $booking->sorting_rank = self::DEFAULT_SORTING_RANK;
                $booking->custom_header_title = trim(Str::of( $property )->explode("Description :")->skip(1)->first());
                $booking->custom_header_title = trim(Str::of( $booking->custom_header_title )->explode(";")->first());
                $booking->confirmation_reference =  self::getKeyValueStartingWith("Confirmation");
                $booking->company = trim(Str::of( self::getLineContainingWithIndex( $segment , "Provider") )->explode(":")->last());
                $booking->effective_date = $this->itinerary->start_date;

                $this->insurances[] = $booking;

            }catch (\Exception $exception){
                info("Error parsing Insurance under flight 6: " . $exception->getMessage());
            }
        }
    }

    private function extractTransferSegment()
    {
        $segment = self::extractBetweenLinesExclusiveUsingStartingWith( $this->lines, "Transfer", "Total" );
        if( count($segment) > 2 )
        {
            // reindex
            $segment = array_values($segment);

            try {

                $property = self::getLineContainingWithIndex( $segment , "Description");
                $property =  strip_tags(html_entity_decode($property)); // clean

                $booking = new ItineraryTransport();
                $booking->booking_category_id = BookingCategory::Transportation;
                $booking->itinerary_id = $this->itinerary->id;
                $booking->sorting_rank = self::DEFAULT_SORTING_RANK;
                $booking->transit_type_id = TransitType::Transfer;
                $booking->custom_header_title = trim(Str::of( $property )->explode("Description :")->skip(1)->first());
                $booking->custom_header_title = trim(Str::of( $booking->custom_header_title )->explode(";")->first());
                $booking->confirmation_reference =  self::getKeyValueStartingWith("Confirmation");
                $booking->company = trim(Str::of( self::getLineContainingWithIndex( $segment , "Provider") )->explode(":")->last());


                $dates = Str::of($segment[0])->explode("M") ;
                $booking->departure_datetime =  $this->carbonParseFromString(
                    (string)Str::of($dates->first())->explode(",")->first(),
                    "n/j/y", Carbon::now()
                );

                $booking->arrival_datetime = $this->carbonParseFromString(
                    (string)Str::of($dates->get(1))->trim()->explode(",")->first(),
                    "n/j/y", Carbon::now()
                );

                $departure_time =   Str::of($dates->first())->explode(",")->last() . "M";
                $arrival_time = Str::of($dates->get(1))->explode(",")->last() . "M";

                // shift the time to the user timezone
                $booking->departure_datetime = $booking->departure_datetime
                    ->setTimeFromTimeString($departure_time)
                    ->fromUserPreferredTimezoneToAppTimezone($this->itinerary->user);

                // shift the time to the user timezone
                $booking->arrival_datetime = $booking->arrival_datetime
                    ->setTimeFromTimeString($arrival_time)
                    ->fromUserPreferredTimezoneToAppTimezone($this->itinerary->user);

                $this->transports[] = $booking;
            }catch (\Exception $exception){
                info("Error parsing Insurance under flight 6: " . $exception->getMessage());
            }
        }
    }
}
