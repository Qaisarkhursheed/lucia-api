<?php

namespace App\Repositories\TextractReader\DocumentReaders\Flights;

use App\ModelsExtended\Airport;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\Interfaces\IBookingModelInterface;
use App\ModelsExtended\ItineraryFlight;
use App\ModelsExtended\ItineraryFlightSegment;
use App\ModelsExtended\ItineraryHotel;
use App\ModelsExtended\ItineraryInsurance;
use App\ModelsExtended\ItineraryPassenger;
use App\ModelsExtended\ItineraryTransport;
use App\ModelsExtended\ServiceSupplier;
use App\ModelsExtended\User;
use App\Repositories\TextractReader\DocumentReaders\DocumentReaderAbstract;
use App\Repositories\TextractReader\DocumentTypeDetector;
use App\Repositories\TextractReader\IDocumentReader;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Flight0001Reader extends DocumentReaderAbstract implements IDocumentReader
{
    /**
     * @var array|ItineraryHotel[]
     */
    protected array $hotels = [];

    /**
     * @var array|ItineraryTransport
     */
    protected array $transports = [];

    /**
     * @var array|ItineraryInsurance
     */
    protected array $insurances = [];

    /**
     * @var ItineraryFlight
     */
    protected ItineraryFlight $flight;

    /**
     * @param array|ItineraryFlightSegment[] $segments
     * @param array $passengers
     * @return array
     */
    function saveFlights(array $segments, array $passengers): array
    {
        $Flights = [];
        $airlineCodes =
            Str::of($this->getKeyValueStartingWith("AIRLINE RESERVATION CODE"))
                ->trim()
                ->explode(",");
        foreach ($this->packSegmentsIntoLayovers($segments) as $index => $segmentArray) {
            $bFlight = clone $this->flight;

            // reacquire confirmation_number
            $bFlight->confirmation_number = $airlineCodes->count() && $airlineCodes->count() - 1 > $index ?
                $airlineCodes->toArray()[$index] : ($airlineCodes->count() ? $airlineCodes->first() : $bFlight->confirmation_number);


            $bFlight = $this->itinerary->itinerary_flights()->create($bFlight->toArray());
            foreach ($segmentArray as $segment)
                $bFlight->itinerary_flight_segments()->create($segment->toArray());

            foreach ($passengers as $passenger)
                $bFlight->flight_passengers()->create(
                    array_merge(
                        [
                            "itinerary_passenger_id" => ItineraryPassenger::updateOrCreate($bFlight->itinerary, Arr::only($passenger, ["name"]))->id,
                        ],
                        $passenger
                    )
                );

            $Flights[] = $bFlight;
        }
        return $Flights;
    }

    /**
     * @return array
     */
    function saveTransports(): array
    {
        $Bookings = [];
        if( !count($this->transports) ) return $Bookings;
        foreach ($this->transports as $index => $segmentArray) {
            $Bookings[] = $this->itinerary->itinerary_transports()->create($segmentArray->toArray());
        }

        return $Bookings;
    }

    /**
     * @return array
     */
    function saveInsurances(): array
    {
        $Bookings = [];
        if( !count($this->insurances) ) return $Bookings;
        foreach ($this->insurances as $index => $segmentArray) {

            $booking  = $this->itinerary->itinerary_insurances()->create($segmentArray->toArray());
            $supplier = ServiceSupplier::createOrUpdateFromRequest (
                $booking->custom_header_title, null, null, null,null,
                BookingCategory::Insurance, User::DEFAULT_ADMIN
            );

            $booking->insurance_supplier()->create([
                'name' => $supplier->name?? $segmentArray->custom_header_title,
                'address' => $supplier->address,
                'phone' => $supplier->phone,
                'website' => $supplier->website,
                'email' => $supplier->email,
                'save_to_library' => false,
            ]);

            $Bookings[] = $booking;
        }

        return $Bookings;
    }

    /**
     * @return array
     */
    function saveHotels(): array
    {
        $Bookings = [];
        if( !count($this->hotels) ) return $Bookings;
        foreach ($this->hotels as $index => $segmentArray) {

            $hotel = $this->itinerary->itinerary_hotels()->create($segmentArray->toArray());
            $supplier = ServiceSupplier::createOrUpdateFromRequest (
                $segmentArray->custom_header_title, null, null, null,null,
                BookingCategory::Hotel, User::DEFAULT_ADMIN
            );

            $hotel->hotel_supplier()->create([
                'name' => $supplier->name?? $segmentArray->custom_header_title,
                'address' => $supplier->address,
                'phone' => $supplier->phone,
                'website' => $supplier->website,
                'email' => $supplier->email,
                'save_to_library' => true,
            ]);


            $hotel->hotel_rooms()->create($segmentArray->hotel_rooms->first()->toArray());

            if( $segmentArray->hotel_amenities->count())
                $hotel->hotel_amenities()->saveMany($segmentArray->hotel_amenities );


            $Bookings[] = $hotel;
        }

        return $Bookings;
    }

    /**
     * returns [ $segments, $passengers ]
     *
     * @param array $jsonArray
     * @return array
     * @throws \Exception
     */
    protected function readFlightsCore(array $jsonArray): array
    {
        parent::read($jsonArray);

        $this->flight = new ItineraryFlight();

        $this->flight->booking_category_id = BookingCategory::Flight;
        $this->flight->itinerary_id = $this->itinerary->id;
        $this->flight->sorting_rank = self::DEFAULT_SORTING_RANK;

        $this->setFlightProperties($this->itinerary->id);

        $this->flight->notes = $this->getNotes();


        $segments = $this->getItineraryFlightSegments();
        $passengers = $this->getFlightPassengers($segments);

        return [ $segments, $passengers ];
    }

    /**
     * @inheritDoc
     * @return ItineraryFlight[]
     * @throws \Exception
     */
    public function read(array $jsonArray): array
    {
       $args = $this->readFlightsCore($jsonArray);
       return $this->saveReader($args[0], $args[1]);
    }

    /**
     * @inheritDoc
     */
    public function canRead(array $jsonArray): bool
    {
        //--- DETECTING PDF2 -- 100 % match
        //has line starting with [PREPARED FOR"]
        //has line starting with [DEPARTURE: ]
        //"Departing At:"
        //"Arriving At:"
        //RESERVATION CODE
        //"Passenger Name:"
        return DocumentTypeDetector::passesThresholdOnLinesStartingWith( $jsonArray, [
            "PREPARED FOR", "DEPARTURE:", "Departing At:", "Arriving At:", "RESERVATION CODE", "Passenger Name:"
        ],80);
    }

    /**
     * @return string
     */
    protected function getTitle(): string
    {
        return implode(' - ', [ $this->lines[0], $this->lines[1] ] );
    }

    /**
     * @return $this
     */
    protected function setInnerDateUTC()
    {
        $this->innerDateUTC = Carbon::createFromFormat( self::DATE_FORMAT__DD__MMM__YYYY, Str::of( $this->lines[0] )->substr(0,11));
        return $this;
    }

    /**
     * @return string
     */
    protected function getConfirmationNumber(): string
    {
       $index = $this->getLineIndexStartingWith( $this->lines,"Reservation Code" );
       return Str::of( $this->lines[$index] )->trim()->explode(' ')->last();
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getRawSegments(): array
    {
        $currentIndex = 0 ;
        $segments = [];

        while ( $currentIndex < count($this->lines) )
        {
            $beginIndex = self::getLineIndexStartingWithIndex( $this->lines, "DEPARTURE", $currentIndex );
            if( $beginIndex !== self::INDEX_NOT_FOUND )
            {
                $endIndex = self::getLineIndexStartingWithIndex( $this->lines, "Status", $beginIndex );
                if( $endIndex === self::INDEX_NOT_FOUND ) throw new \Exception("Could not match the end of segment!");

                $segments[] = collect($this->lines)->skip($beginIndex)->take( $endIndex-$beginIndex )->toArray();

                $currentIndex = $endIndex+1;
            }else
                $currentIndex = count($this->lines);
        }

        return $segments;
    }

    /**
     * @return string|null
     */
    protected function getNotes(): ?string
    {
        $beginIndex = self::getLineIndexStartingWithIndex( $this->lines, "NOTES" );
        if( $beginIndex === self::INDEX_NOT_FOUND ) return null;

        return collect($this->lines)->skip( $beginIndex + 1 )->implode("\n");
    }

    /**
     * @param array $segment
     * @return ItineraryFlightSegment
     */
    protected function processRawSegment(array $segment): ItineraryFlightSegment
    {
        // reindex
        $segment = array_values($segment);

        $flightSegment = new ItineraryFlightSegment();

//----- Departure Time
//	- After the word ["Meals:"] next line is the time
//        $beginIndex = self::getLineIndexContaining( $segment, "Meals:" );
//        $departure_time = $segment[$beginIndex+1];
//        $arrival_time = $segment[$beginIndex+2];
//
        $beginIndex = self::getLineIndexWithRegex( $segment, self::HR_12_REGEX );
        $departure_time = $segment[$beginIndex];
        $arrival_time = $segment[$beginIndex+1];

        $this->setSegmentDepartureDayTime($segment, $departure_time, $flightSegment)
                ->setSegmentArrivalDayTime($segment, $arrival_time, $flightSegment)
                ->setSegmentAirlineInfo($segment, $flightSegment);


//----- Duration
//	-- calculate from time
        $flightSegment->duration_in_minutes = $flightSegment->departure_datetime->diffInMinutes( $flightSegment->arrival_datetime );

        $flightSegment->cabin = $this->detectCabin($segment);

        return $flightSegment;
    }

    /**
     * @param array $segment
     * @param string $time
     * @param ItineraryFlightSegment $flightSegment
     * @return $this
     */
    private function setSegmentDepartureDayTime( array $segment, string $time, ItineraryFlightSegment $flightSegment)
    {
//----- Departure Day
//	In line of the word [DEPARTURE] split by ":" and pick first part
//	trim it and split by space. take the words index 1 and 2 skiping index 0
//	combine them with year detected for this class
//	05 JUL ----
        $dateString = Str::of($segment[0])->ltrim("DEPARTURE:")
                ->trim()->explode(" ")->skip(1)
                ->take(2)->implode(" ") . " " . $this->innerDateUTC->year;

        // shift the time to the user timezone
        $flightSegment->departure_datetime = Carbon::createFromFormat(self::DATE_FORMAT__DD__MMM__YYYY, $dateString)
            ->setTimeFromTimeString($time)
            ->fromUserPreferredTimezoneToAppTimezone($this->itinerary->user);

        return $this;
    }

    /**
     * @param array $segment
     * @param string $time
     * @param ItineraryFlightSegment $flightSegment
     * @return $this
     */
    private function setSegmentArrivalDayTime( array $segment, string $time, ItineraryFlightSegment $flightSegment)
    {
//        ----- Arrival Day
//	In line of the word [ARRIVAL] split by ":" and pick first part
//	trim it and split by space. take the words index 1 and 2 skiping index 0
//	combine them with year detected for this class
//	05 JUL ----

        // same day
        $flightSegment->arrival_datetime = $flightSegment->departure_datetime;

        try {

            $beginIndex = self::getLineIndexContaining( $segment, "ARRIVAL:" );

            if( $beginIndex !== self::INDEX_NOT_FOUND )
            {
                // it can fall to next line. Confirm
                if( Str::of($segment[$beginIndex])->ltrim("ARRIVAL:")->trim()->length() < 5 )
                {
                    $dateString = Str::of($segment[$beginIndex+1])->ltrim("ARRIVAL:")
                            ->trim()->explode(" ")->skip(1)
                            ->take(2)->implode(" ") . " " . $this->innerDateUTC->year;
                }
                else{
                    $dateString = Str::of($segment[$beginIndex])->ltrim("ARRIVAL:")
                            ->trim()->explode(" ")->skip(1)
                            ->take(2)->implode(" ") . " " . $this->innerDateUTC->year;
                }

                // shift the time to the user timezone
                $flightSegment->arrival_datetime = Carbon::createFromFormat( self::DATE_FORMAT__DD__MMM__YYYY, $dateString);
            }
        }catch (\Exception $exception){
            $this->log( __FUNCTION__, $exception->getMessage() );
        }

        $flightSegment->arrival_datetime = $flightSegment->arrival_datetime
            ->setTimeFromTimeString($time)
            ->fromUserPreferredTimezoneToAppTimezone($this->itinerary->user);

        return $this;
    }

    /**
     * @param array $segment
     * @param ItineraryFlightSegment $flightSegment
     * @return $this
     */
    private function setSegmentAirlineInfo(array $segment, ItineraryFlightSegment $flightSegment)
    {
        //----- Airline
// 	Next line after the line containing [ Please verify flight ]
// 	"LUFTHANSA"
//
//----- Operated By
//	- Same as Airline
//
        $beginIndex = self::getLineIndexContaining( $segment, "Please verify flight" );
        $flightSegment->airline = ucwords(strtolower($segment[$beginIndex+1]));
        $flightSegment->airline_operator = null;

//----- Departure City
//	- Next line immediately after airline
//	- "EWR" - Use the code to get the Airport details from database
        $flightSegment->flight_from = $segment[$beginIndex+2];
        $flightSegment->flight_from = Airport::findByIata($flightSegment->flight_from)->name;

//----- Arrival City
//	- Next line immediately after Departure City
//	- "EWR" - Use the code to get the Airport details from database
        $flightSegment->flight_to = $segment[$beginIndex+3];
        $flightSegment->flight_to = Airport::findByIata($flightSegment->flight_to)->name;


        $beginIndex = self::getLineIndexContaining( $segment, "Aircraft:" );
        $flightSegment->flight_number = $segment[$beginIndex+2];

        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function getRawPassengers(): \Illuminate\Support\Collection
    {
        $passengers = collect();

        foreach ($this->tables as $table)
        {
            // each table is a segment and can be merged to each segment later when needed

            if( ! Str::of($table[0][0]["column1"])->trim()->lower()->startsWith( "passenger name" ) )
                continue;
            foreach (collect($table)->skip(1)->toArray() as $row)
            {
                $name = Str::of(  $row[0]["column1"] )->trim("\"")->trim(" ")->trim("\\")->replace("/", " ");
                $xName = $name->explode(" ");
                $lastName = $xName->count()? $xName->first(): "";
                $otherName = $xName->count() > 1 ? $xName->skip(1)->implode(" "): "";

                $passengers->push([
                    'name' => ucwords(strtolower(trim( $otherName . " " . $lastName ))),
                    'seat' => (string)Str::of(  $row[1]["column2"] )->trim("\"")->trim(" ")->trim("\\"),
                    'class' => null,
                    'frequent_flyer_number' => (string)Str::of(  $row[2]["column3"] )->trim()->replace("/", " ")->explode(" ")->first(),
                    'ticket_number' => (string)Str::of(  $row[3]["column4"] )->trim()->replace("/", " ")->explode(" ")->first(),
                ]);
            }
        }

        return $passengers->unique( fn( array $item ) => $item["name"] );
    }

    /**
     * @param array|ItineraryFlightSegment[] $segments
     * @param array $passengers
     * @return ItineraryFlight[]
     */
    private function saveReader(array $segments, array $passengers): array
    {
      return  DB::transaction(function () use ($segments, $passengers ){

          $Flights = $this->saveFlights($segments, $passengers);
          $h = $this->saveHotels();
          $t = $this->saveTransports();
          $i = $this->saveInsurances();

          return array_merge($Flights, $h, $t, $i);
        });
    }

    /**
     * @param array|ItineraryFlightSegment[] $segments
     * @return array|ItineraryFlightSegment[][]
     */
    private function packSegmentsIntoLayovers(array $segments)
    {
        $segmentArray = [];
        $block=collect();
        for ($i = 0; $i < count($segments); $i++)
        {
            if( $i==0 || $block->count()===0 )
            {
                $block->push( $segments[$i] );
            }
            else{
                $f =  $segments[$i];

                // if it is less than 24hrs layover
                if( $f->departure_datetime->diffInMinutes( $block->all()[$block->count()-1]->arrival_datetime )  < 1440 )
                {
                    // same segment
                    $block->push($segments[$i]);
                }else{
                    // not same segment, clear block
                    $segmentArray[] = $block->all();
                    $block = collect();
                    $block->push( $segments[$i] );
                }
            }
        }
        $segmentArray[] = $block->all();

        return $segmentArray;
    }

    /**
     * @param array $segment
     * @return mixed|null
     */
    private function detectCabin(array $segment)
    {
        // store cabin
        $beginIndex = self::getLineIndexContaining( $segment, "Cabin:" );
        if( $beginIndex === self::INDEX_NOT_FOUND ) return null;
        foreach (collect($segment)->skip($beginIndex+1)->take(5)->toArray() as $key => $value )
        {
            if( Str::of(  $value )->lower()->contains([ 'first class', 'first', 'economy', 'business',   ]) )
                return $value;
        }
        return null;
    }

    protected function setFlightProperties( int $itinerary_id)
    {
        $this->flight->custom_header_title = $this->getTitle();
        $this->flight->confirmation_number = $this->getConfirmationNumber();
        $this->flight->price = $this->getPrice();
        $this->flight->check_in_url = null;
    }

    /**
     * @return array|ItineraryFlightSegment[]
     * @throws \Exception
     */
    private function getItineraryFlightSegments(): array
    {
        return collect($this->getRawSegments())
            ->map( fn( array $segment ) => $this->processRawSegment( $segment ) )
            ->all();
    }

    /**
     *  [ 'name' => , 'seat' => , 'class' => null, 'ticket_number'=>, ]
     *
     * @param array|ItineraryFlightSegment[] $segments
     * @return array
     */
    protected function getFlightPassengers(array $segments): array
    {
        return $this->getRawPassengers()
            ->map( fn( array $passenger ) => array_merge( $passenger, [ "class" => $segments[0]["cabin"] ] ) )
            ->toArray();
    }

    protected function getPrice(): ?string
    {
        return null;
    }
//
//    /**
//     * @param ItineraryFlightSegment|null $firstFlight
//     * @param ItineraryFlightSegment|null $lastFlight
//     * @return string
//     */
//    private function createFlightTitle(?ItineraryFlightSegment $firstFlight , ?ItineraryFlightSegment $lastFlight): string
//    {
//        if( !$firstFlight || !$lastFlight ) return "Flight";
//
//       return sprintf( "Flight from %s to %s", $firstFlight->flight_from, $lastFlight->flight_to  );
//    }


}
