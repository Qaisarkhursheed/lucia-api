<?php

namespace App\Console\Commands\Tests;

use App\Models\ItineraryHotel;
use App\Models\TravellerEmail;
use App\ModelsExtended\Itinerary;
use App\ModelsExtended\ItineraryConcierge;
use App\ModelsExtended\ItineraryCruise;
use App\ModelsExtended\ItineraryFlight;
use App\ModelsExtended\ItineraryInsurance;
use App\ModelsExtended\ItineraryTour;
use App\ModelsExtended\ItineraryTransport;
use App\ModelsExtended\TransitType;
use App\ModelsExtended\Traveller;
use App\ModelsExtended\User;
use Database\Factories\Itinerary\Bookings\Concierges\ConciergePictureFactory;
use Database\Factories\Itinerary\Bookings\Concierges\ConciergeSupplierFactory;
use Database\Factories\Itinerary\Bookings\Cruises\CruisePassengerFactory;
use Database\Factories\Itinerary\Bookings\Cruises\CruisePictureFactory;
use Database\Factories\Itinerary\Bookings\Cruises\CruiseSupplierFactory;
use Database\Factories\Itinerary\Bookings\Flights\FlightPassengerFactory;
use Database\Factories\Itinerary\Bookings\Flights\FlightPictureFactory;
use Database\Factories\Itinerary\Bookings\Flights\FlightSegmentFactory;
use Database\Factories\Itinerary\Bookings\Flights\FlightSupplierFactory;
use Database\Factories\Itinerary\Bookings\Hotels\HotelAmenityFactory;
use Database\Factories\Itinerary\Bookings\Hotels\HotelPassengerFactory;
use Database\Factories\Itinerary\Bookings\Hotels\HotelPictureFactory;
use Database\Factories\Itinerary\Bookings\Hotels\HotelSupplierFactory;
use Database\Factories\Itinerary\Bookings\Insurances\InsurancePictureFactory;
use Database\Factories\Itinerary\Bookings\Insurances\InsuranceSupplierFactory;
use Database\Factories\Itinerary\Bookings\ItineraryConciergeFactory;
use Database\Factories\Itinerary\Bookings\ItineraryCruiseFactory;
use Database\Factories\Itinerary\Bookings\ItineraryFlightFactory;
use Database\Factories\Itinerary\Bookings\ItineraryHotelFactory;
use Database\Factories\Itinerary\Bookings\ItineraryInsuranceFactory;
use Database\Factories\Itinerary\Bookings\ItineraryOtherFactory;
use Database\Factories\Itinerary\Bookings\ItineraryTourFactory;
use Database\Factories\Itinerary\Bookings\ItineraryTransportFactory;
use Database\Factories\Itinerary\Bookings\Tours\TourPictureFactory;
use Database\Factories\Itinerary\Bookings\Tours\TourSupplierFactory;
use Database\Factories\Itinerary\Bookings\Transports\TransportPassengerFactory;
use Database\Factories\Itinerary\Bookings\Transports\TransportPictureFactory;
use Database\Factories\Itinerary\Bookings\Transports\TransportSupplierFactory;
use Database\Factories\Itinerary\ClientEmailFactory;
use Database\Factories\Itinerary\ItineraryClientFactory;
use Database\Factories\Itinerary\ItineraryPassengerFactory;
use Database\Factories\Itinerary\ItineraryPictureFactory;
use Database\Factories\Itinerary\TravellerEmailFactory;
use Database\Factories\Itinerary\TravellerFactory;
use Database\Factories\ItineraryFactory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GenerateFakeItinerary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tests:generate-fake-itinerary {email} {--count=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will generate fake itinerary for specified user';

    const MAX_PASSENGERS = 10;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = User::getAgent( $this->argument( "email" ) );
        if( !$user ) {
            $this->error( "Please, enter a valid agent user email to generate itinerary for.");
            return false;
        }

        $this->info( "Generating itinerary for " . $user->name );

        DB::transaction( function ( ) use ( $user ){

            // Create Itinerary
            $itineraries = $this->createItinerary( $user );
            $this->info( "Itinerary ID " . $itineraries->first()->id . " created!"  );
            $this->info(  $itineraries->count() . " Itineraries created!"  );

        } );

        $this->info( "Completed successfully "  );

        return true;
    }

    /**
     * @return int
     */
    private function getCount()
    {
        return intval( $this->option( "count" ) );
    }

    /**
     * @param User $user
     * @param ItineraryFactory $factory
     * @return \Illuminate\Database\Eloquent\Model|Itinerary[]|Collection
     */
    private function createItinerary(User $user )
    {
        // make sure picture exists
        ItineraryPictureFactory::storeDefaultPics();

        $traveller = ItineraryFactory::createOrUpdateTraveller($user->id);

        // Create itinerary
        return  ( new ItineraryFactory() )
            ->count( $this->getCount()  )
            ->create([ "user_id" =>  $user->id, "traveller_id" => $traveller->id  ])
            ->each(function ( Itinerary $itinerary ){

                // Create Theme
                $itinerary->itinerary_theme()->create();

                // Create Emails
                if( $itinerary->traveller->traveller_emails->count() < 2 )
                    $itinerary->traveller->traveller_emails()->saveMany(
                        ( new TravellerEmailFactory() )->count( 2 )->make()
                    );

                // Create Pictures
                $itinerary->itinerary_pictures()->saveMany(
                    ( new ItineraryPictureFactory() )->count(1)->make()
                );

                // Create Passengers
                $itinerary->itinerary_passengers()->saveMany(
                    ( new ItineraryPassengerFactory() )->count(self::MAX_PASSENGERS )->make()
                );

                // Book Hotel
                $this->bookHotel( $itinerary );

                // Book Flight
                $this->bookFlight( $itinerary );

                // Book Cruise
                $this->bookCruise( $itinerary );

                // Book Transport - All types
                $this->bookTransport( $itinerary );

                // Book Concierge
                $this->bookConcierge( $itinerary );

                // Book Tour
                $this->bookTour( $itinerary );

                // Book Insurance
                $this->bookInsurance( $itinerary );

                // Book Other
                $this->bookOther( $itinerary );

            });
    }

    private function bookHotel(Itinerary $itinerary)
    {
        return  (new ItineraryHotelFactory())
            ->count( 1  )
            ->create([ "itinerary_id" =>  $itinerary->id ])
            ->each(function ( ItineraryHotel $itineraryHotel ) use ( $itinerary ){

                // create supplier
                $itineraryHotel->hotel_supplier()
                    ->save( ( new HotelSupplierFactory(  ) )->makeOne()  );

                // Create Amenities
                $itineraryHotel->hotel_amenities()->saveMany(
                    ( new HotelAmenityFactory() )->count(rand( 1, 2) )->make()
                );

                // Create Pictures
                $itineraryHotel->hotel_pictures()->saveMany(
                    ( new HotelPictureFactory() )->count(1)->make()
                );

                // Create Passengers
                $itineraryHotel->hotel_passengers()->saveMany(
                    $this->makePassengers(
                        $itinerary, new HotelPassengerFactory()
                    )
                );
            });
    }

    private function bookFlight(Itinerary $itinerary)
    {
        // make sure picture exists
        FlightPictureFactory::storeDefaultPics();

        return  (new ItineraryFlightFactory())
            ->count( 1  )
            ->create([ "itinerary_id" =>  $itinerary->id ])
            ->each(function ( ItineraryFlight $itineraryFlight ) use ( $itinerary ){

                // create Flight Segment
                $itineraryFlight->itinerary_flight_segments()
                    ->save( ( new FlightSegmentFactory(  ) )->makeOne()  );

//                // create supplier
//                $itineraryFlight->flight_supplier()
//                    ->save( ( new FlightSupplierFactory(  ) )->makeOne()  );

                // Create Pictures
                $itineraryFlight->flight_pictures()->saveMany(
                    ( new FlightPictureFactory() )->count(3)->make()->unique( 'image_url' )
                );

                // Create Passengers
                $itineraryFlight->flight_passengers()
                ->saveMany( $this->makePassengers( $itinerary, new FlightPassengerFactory() ) );
            });
    }

    private function bookCruise(Itinerary $itinerary)
    {
        return  (new ItineraryCruiseFactory())
            ->count( 1  )
            ->create([ "itinerary_id" =>  $itinerary->id ])
            ->each(function ( ItineraryCruise $itineraryCruise ) use ( $itinerary ){

                // create supplier
                $itineraryCruise->cruise_supplier()
                    ->save( ( new CruiseSupplierFactory(  ) )->makeOne()  );

                // Create Pictures
                $itineraryCruise->cruise_pictures()->saveMany(
                    ( new CruisePictureFactory() )->count(1)->make()
                );

                // Create Passengers
                $itineraryCruise->cruise_passengers()
                ->saveMany(
                    $this->makePassengers( $itinerary, new CruisePassengerFactory() )
                );
            });
    }

    private function bookTransport(Itinerary $itinerary)
    {
         (new ItineraryTransportFactory())
            ->count( 1  )
            ->create([
                "itinerary_id" =>  $itinerary->id,
                "transit_type_id" =>  TransitType::Rail
            ])
            ->each(function ( ItineraryTransport $itineraryTransport ) {
                $this->completeTransportBooking( $itineraryTransport );
            });
        (new ItineraryTransportFactory())
            ->count( 1  )
            ->create([
                "itinerary_id" =>  $itinerary->id,
                "transit_type_id" =>  TransitType::Ferry
            ])
            ->each(function ( ItineraryTransport $itineraryTransport ) {
                $this->completeTransportBooking( $itineraryTransport );
            });
        (new ItineraryTransportFactory())
            ->count( 1  )
            ->create([
                "itinerary_id" =>  $itinerary->id,
                "transit_type_id" =>  TransitType::Car
            ])
            ->each(function ( ItineraryTransport $itineraryTransport ) {
                $this->completeTransportBooking( $itineraryTransport );
            });
        (new ItineraryTransportFactory())
            ->count( 1  )
            ->create([
                "itinerary_id" =>  $itinerary->id,
                "transit_type_id" =>  TransitType::Transfer
            ])
            ->each(function ( ItineraryTransport $itineraryTransport ) {
                $this->completeTransportBooking( $itineraryTransport );
            });
        return $this;
    }

    private function completeTransportBooking(ItineraryTransport $itineraryTransport)
    {
        // create supplier
        $itineraryTransport->transport_supplier()
            ->save( ( new TransportSupplierFactory(  ) )->makeOne()  );

        // Create Pictures
        $itineraryTransport->transport_pictures()->saveMany(
            ( new TransportPictureFactory() )->count(1)->make()
        );

        // Create Passengers
        $itineraryTransport->transport_passengers()
        ->saveMany(
            $this->makePassengers( $itineraryTransport->itinerary, new TransportPassengerFactory() )
        );
        return $this;
    }

    private function bookConcierge(Itinerary $itinerary)
    {
        return  (new ItineraryConciergeFactory())
            ->count( 1  )
            ->create([ "itinerary_id" =>  $itinerary->id ])
            ->each(function ( ItineraryConcierge $itineraryConcierge ) use ( $itinerary ){

                // create supplier
                $itineraryConcierge->concierge_supplier()
                    ->save( ( new ConciergeSupplierFactory(  ) )->makeOne()  );

                // Create Pictures
                $itineraryConcierge->concierge_pictures()->saveMany(
                    ( new ConciergePictureFactory() )->count(1)->make()
                );

            });
    }

    private function bookTour(Itinerary $itinerary)
    {
        return  (new ItineraryTourFactory())
            ->count( 1  )
            ->create([ "itinerary_id" =>  $itinerary->id ])
            ->each(function ( ItineraryTour $itineraryTour ) use ( $itinerary ){

                // create supplier
                $itineraryTour->tour_supplier()
                    ->save( ( new TourSupplierFactory(  ) )->makeOne()  );

                // Create Pictures
                $itineraryTour->tour_pictures()->saveMany(
                    ( new TourPictureFactory() )->count(1)->make()
                );
            });
    }

    private function bookInsurance(Itinerary $itinerary)
    {
        return  (new ItineraryInsuranceFactory())
            ->count( 1  )
            ->create([ "itinerary_id" =>  $itinerary->id ])
            ->each(function ( ItineraryInsurance $itineraryInsurance ) use ( $itinerary ){

                // create supplier
                $itineraryInsurance->insurance_supplier()
                    ->save( ( new InsuranceSupplierFactory(  ) )->makeOne()  );

                // Create Pictures
                $itineraryInsurance->insurance_pictures()->saveMany(
                    ( new InsurancePictureFactory() )->count(1)->make()
                );
            });
    }

    private function bookOther(Itinerary $itinerary)
    {
        return  (new ItineraryOtherFactory())
            ->count( 1  )
            ->create([ "itinerary_id" =>  $itinerary->id ]);
    }

    /**
     * @param Itinerary $itinerary
     * @param Collection|Model[] $models
     * @return Model[]|Collection
     */
    private function fillRandomTransportPassengers( Itinerary $itinerary, $models )
    {
        $passengers = Arr::random( $itinerary->itinerary_passengers->toArray(), $models->count() );
        for ( $i = 0 ; $i < $models->count(); $i++ )
        {
            $models[$i]->itinerary_passenger_id =  $passengers[$i]['id'];
        }
        return $models;
    }

    /**
     * @param Itinerary $itinerary
     * @param Factory $factory
     * @return Model[]|Collection
     */
    private function makePassengers(Itinerary $itinerary, Factory $factory)
    {
      return $this->fillRandomTransportPassengers(
            $itinerary, $factory->count( rand( 1, self::MAX_PASSENGERS ) )->make()
        );
    }

}
