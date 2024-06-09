<?php

namespace App\Console\Commands\Sync;

use App\ModelsExtended\Interfaces\ICanCreateGoogleCalendarEventInterface;
use App\ModelsExtended\Itinerary;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class SyncDeleteItineraryOnGoogle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:delete-itinerary-on-google {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will delete all itinerary information on google calendar';

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
            ->whereNotNull('google_calendar_event_id')
            ->whereNull('deleted_at')
            ->when( $user_id , function ( Builder $builder ) use ( $user_id ){
                $builder->where( "user_id", $user_id );
            } )

          ->get();

        $this->withProgressBar( $itineraries , function ( Itinerary $itinerary ){
            $this->deleteCalendarEvents( $itinerary );
        });


        $this->info( "\n-------------------------------------" );
        $this->info( "COMPLETED" );

        return true;
    }

    private function deleteCalendarEvents(Itinerary $itinerary)
    {
        //             $itinerary->deleteCalendarEvent();
        foreach ( $itinerary->getAllBookingsOnItinerary() as $booking )
        {
            if( $booking instanceof ICanCreateGoogleCalendarEventInterface )
                $booking->deleteCalendarEvent();
        }
    }
}
