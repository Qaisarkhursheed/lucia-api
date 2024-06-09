<?php

namespace App\Repositories\TextractReader\DocumentReaders\Hotels;

use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\CurrencyType;
use App\ModelsExtended\HotelRoom;
use App\ModelsExtended\ItineraryHotel;
use App\ModelsExtended\ServiceSupplier;
use App\ModelsExtended\User;
use App\Repositories\TextractReader\DocumentReaders\DocumentReaderAbstract;
use App\Repositories\TextractReader\DocumentTypeDetector;
use App\Repositories\TextractReader\IDocumentReader;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Hotel0001Reader extends DocumentReaderAbstract implements IDocumentReader
{

   public function read(array $jsonArray): array
   {
       parent::read($jsonArray);

       $bookings = collect($this->getRawSegments())
           ->map( fn( array $segment ) =>  $this->saveReader( $this->processRawSegment( $segment ) ) );

       return $bookings->all();
   }

    /**
     * @inheritDoc
     */
    public function canRead(array $jsonArray): bool
    {
//has line starting with ["Room Details:"]
//has line starting with [CHECK IN:]
//Cancellation Information:
//Room(s):
//Approx. Total Price:

        return DocumentTypeDetector::passesThresholdOnLines( $jsonArray, [
            "CHECK IN:", "Room Details:", "Cancellation Information:",
            "Room(s):", "Approx. Total Price:", "Confirmation:"
        ],80);
    }

    /**
     * @return $this
     */
    protected function setInnerDateUTC()
    {
        $this->innerDateUTC = Carbon::createFromFormat( self::DATE_FORMAT__DD__MMM__YYYY, Str::of( $this->lines[0] )->substr(0,11));
        return $this;
    }

    protected function getRawSegments():array
    {
        $currentIndex = 0 ;
        $segments = [];

        while ( $currentIndex < count($this->lines) )
        {
            $beginIndex = self::getLineIndexStartingWithIndex( $this->lines, "CHECK IN:", $currentIndex );
            if( $beginIndex !== self::INDEX_NOT_FOUND )
            {
                $endIndex = self::getLineIndexStartingWithIndex( $this->lines, "Status:", $beginIndex );
                if( $endIndex === self::INDEX_NOT_FOUND ) throw new \Exception("Could not match the end of segment!");

                $segments[] = collect($this->lines)->skip($beginIndex)->take( $endIndex-$beginIndex )->toArray();

                $currentIndex = $endIndex+1;
            }else
                $currentIndex = count($this->lines);
        }

        return $segments;
    }

    /**
     * @param array $segment
     * @return ItineraryHotel
     */
    protected function processRawSegment(array $segment):ItineraryHotel
    {
        // reindex
        $segment = array_values($segment);

        $booking = $this->createItineraryHotel( $segment[self::getLineIndexStartingWithIndex($segment, "Room Details:")-1] );

        $booking->confirmation_reference =  $this->detectConfirmationNumber( $segment );
        $check_in_date = $this->extractDatePartFrom( $segment, "CHECK IN")  ;
        $booking->check_in_date =  Carbon::createFromFormat( self::DATE_FORMAT__DD__MMM__YYYY, $check_in_date );

        // I changed to OUT: because Check out: is breaking into 2 lines
        $check_out_date =  $this->extractDatePartFrom($segment, "OUT");
        $booking->check_out_date = $booking->check_in_date;
        if( $check_out_date )
            $booking->check_out_date =  Carbon::createFromFormat( self::DATE_FORMAT__DD__MMM__YYYY, $check_out_date );

        $booking->check_in_time = null;
        $booking->check_out_time = null;

        $booking->cancel_policy = collect( self::extractBetweenLinesExclusiveUsingStartingWith( $segment,
        "Room Details", "Guarantee" ) )->implode("\n");

        $booking->hotel_rooms->push($this->createHotelRoom($segment));

        return $booking;
    }

    /**
     * @param ItineraryHotel $booking
     * @return ItineraryHotel
     */
    private function saveReader(  ItineraryHotel $booking): ItineraryHotel
    {
       return DB::transaction(function () use ($booking ){

           $hotel = $this->itinerary->itinerary_hotels()->create( $booking->toArray() );

           $supplier = ServiceSupplier::createOrUpdateFromRequest (
               $booking->custom_header_title, null, null, null,null,
               BookingCategory::Hotel, User::DEFAULT_ADMIN
           );

           // I used the long approach instead of simply
           // $booking->notes
           // because, that will try to call the notes() method if it doesn't exist as property
           $supplier->updateDescription( optional((object)$booking->toArray())->notes  );
           $hotel->hotel_supplier()->create([
               'name' => $supplier->name?? $booking->custom_header_title,
               'address' => $supplier->address,
               'phone' => $supplier->phone,
               'website' => $supplier->website,
               'email' => $supplier->email,
               'save_to_library' => true,
           ]);

           $hotel->hotel_rooms()->create($booking->hotel_rooms->first()->toArray());

           if( $booking->hotel_amenities->count())
                $hotel->hotel_amenities()->saveMany($booking->hotel_amenities );

            return $hotel;
        });
    }

    /**
     * $custom_header_title - This will be used as supplier name
     *
     * @param string $custom_header_title
     * @return ItineraryHotel
     */
    protected function createItineraryHotel(string $custom_header_title): ItineraryHotel
    {
        $booking = new ItineraryHotel();
        $booking->booking_category_id = BookingCategory::Hotel;
        $booking->itinerary_id = $this->itinerary->id;
        $booking->sorting_rank = self::DEFAULT_SORTING_RANK;
        $booking->custom_header_title = $custom_header_title;

//       ServiceSupplier::createOrUpdateFromRequest( $custom_header_title, null,null,null,
//        null, BookingCategory::Hotel, $this->itinerary->user_id
//        );

        return $booking;
    }

    /**
     * @param array $segment
     * @return ?string
     */
    private function detectConfirmationNumber(array $segment): ?string
    {
        foreach (  self::extractBetweenLinesExclusiveUsingStartingWith( $segment, "Confirmation:", "Status:" ) as $value )
        {
            if ( is_numeric( (string)Str::of($value)->substr(0,4 ) )  && is_numeric( (string)Str::of($value)->substr(-3 ) ) )
                return (string)$value;
        }
        return null;
    }

    /**
     * @param string $value
     * @return int
     */
    protected function detectCurrencyTypeID(string $value): int
    {
        if( Str::of( $value)->lower()->contains( "eur" ) ) return CurrencyType::EUR;
        if( Str::of( $value)->lower()->contains( "€" ) ) return CurrencyType::EUR;
        if( Str::of( $value)->lower()->contains( "£" ) ) return CurrencyType::GBP;
        return CurrencyType::USD;
    }

    protected function createHotelRoom(array $segment)
    {
        $room = new HotelRoom();

        $index = self::getLineIndexStartingWithIndex( $segment, "Room Details" );
        $room->bedding_type = $segment[$index+2];

        $room->room_type = $room->bedding_type;

        $room->guest_name = self::getKeyValueStartingWith("prepared for");

        $index = self::getLineIndexContaining( $segment, "Guest(s):" );
        $room->number_of_guests = intval(Str::of($segment[$index])->trim()->explode(" ")->last());

        $value = $segment[self::getLineIndexStartingWithIndex( $segment, "Approx. Total Price" )+1];
        $room->currency_id = $this->detectCurrencyTypeID( $value );
        $room->room_rate = floatval(  explode(" ", trim($value))[0] );

        return $room;
    }

    /**
     * @param array $segment
     * @param string $keyWord
     * @return string|null
     */
    private function extractDatePartFrom(array $segment, string $keyWord): ?string
    {
        try {
            $secondPart = Str::of( self::getLineContainingWithIndex($segment, $keyWord) )
                ->explode($keyWord)->last();

            $secondPartX = explode( " ", $secondPart );
            return implode(" ", [ $secondPartX[2], $secondPartX[3] ]) . " " . $this->innerDateUTC->year;
        }catch (\Exception $exception){
            Log::error( $exception->getMessage(), $exception->getTrace() );
//            dd( $segment, $keyWord, $exception );
            return  null;
        }
    }
}
