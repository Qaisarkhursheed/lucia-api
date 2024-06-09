<?php

namespace App\Console\Commands\Sync;

use App\ModelsExtended\Interfaces\ICanCreateGoogleCalendarEventInterface;
use App\ModelsExtended\Itinerary;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class SyncItineraryWithGoogle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:itinerary-to-google {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will sync itinerary information to google calendar';

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
        $user_id = $this->argument( 'user_id' );

        $itineraries = Itinerary::with('user')
            ->whereNull('deleted_at')
            ->when( $user_id , function ( Builder $builder ) use ( $user_id ){
                $builder->where( "user_id", $user_id );
            } )
//            ->whereHas( 'user' , function ( Builder $builder ){
//                $builder->whereNotNull( "users.google_authentication_token");
//            } )
          ->get();

        $this->withProgressBar( $itineraries , function ( Itinerary $itinerary ){
            $this->createCalendarEvents( $itinerary );
        });


        $this->info( "\n-------------------------------------" );
        $this->info( "COMPLETED" );

        return true;
    }

    /**
     * @param Itinerary $itinerary
     * @return void
     */
    private function createCalendarEvents(Itinerary $itinerary)
    {
        // not needed
//        $itinerary->createCalendarEvent();
        foreach ( $itinerary->getAllBookingsOnItinerary() as $booking )
        {
            if( $booking instanceof ICanCreateGoogleCalendarEventInterface )
                $booking->createCalendarEvent();
        }
    }
}
