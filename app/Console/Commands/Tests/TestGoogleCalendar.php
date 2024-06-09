<?php

namespace App\Console\Commands\Tests;

use App\ModelsExtended\User;
use App\Repositories\Calendars\GoogleCalendars\GoogleCalendarClient;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class TestGoogleCalendar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tests:google-calendar {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make sure google calendar is working fine';

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
     * @return int
     */
    public function handle()
    {
        try {

            $eventId = $this->createEventTimeOnGoogleCalendar();

            $this->info( "Creating Event for 5 days from now\n" );
            $this->info( "Event " . $eventId . " sent to \n" . $this->argument( "email" ) );

            $this->updateEventTimeOnGoogleCalendar( $eventId );
            $this->info( "Event " . $eventId . " Updated for \n" . $this->argument( "email" ) );

//            $this->createEventOnGoogleCalendar();

        }catch ( \Exception $ex ){

            Log::error(  $ex->getMessage(), $ex->getTrace() );
            $this->error( $ex->getMessage(), $ex->getTraceAsString()  );
        }

        return 0;
    }

    /**
     * Only creates if Accepted and user has token
     *  Return event ID
     *
     * @return string
     */
    public function createEventOnGoogleCalendar( ): string
    {
        try {

            $client = new GoogleCalendarClient( User::getCalendarSyncUser() );
            return $client->createEvent("This is a sample event to be sure",
                Carbon::now()->addDays(5),
                Carbon::now()->addDays(15),
                $this->argument( "email" ),
                "This is a general note."
            );
        }catch (\Exception $exception)
        {
            dd( "error creating itinerary on google calendar. " . $exception->getMessage(), $exception->getTrace() );
        }
    }

    /**
     * Only creates if Accepted and user has token
     *
     *  Return event ID
     *
     * @return string
     */
    public function createEventTimeOnGoogleCalendar( ): string
    {
        try {
            $utcTime = Carbon::now()->addDays(5)->setTimeFromTimeString("12:30");
            $client = new GoogleCalendarClient( User::getCalendarSyncUser() );
            return $client->createReminderTimeEvent(" (Itinerary name - Booking title) Trip to Italy - Flight to Malpensa Airport",
                $utcTime,
                $utcTime,
                $this->argument( "email" ),
                "(Address + Flight/Room/Cabin/Type of transportation + Description"
            );
        }catch (\Exception $exception)
        {
            dd( "error creating itinerary on google calendar. " . $exception->getMessage(), $exception->getTrace() );
        }
    }

    /**
     * Only creates if Accepted and user has token
     *
     *  Return event ID
     *
     * @return string
     */
    public function updateEventTimeOnGoogleCalendar( string $eventId ): string
    {
        try {
            $utcTime = Carbon::now()->addDays(5)->setTimeFromTimeString("12:30");
            $client = new GoogleCalendarClient( User::getCalendarSyncUser() );
            return $client->updateReminderTimeEvent(
                $eventId,
                " (Itinerary name - Booking title) Trip to Italy - Flight to Malpensa Airport",
                $utcTime,
                $utcTime,
                $this->argument( "email" ),
                "UPDATED: (Address + Flight/Room/Cabin/Type of transportation + Description"
            );
        }catch (\Exception $exception)
        {
            dd( "error creating itinerary on google calendar. " . $exception->getMessage(), $exception->getTrace() );
        }
    }
}
