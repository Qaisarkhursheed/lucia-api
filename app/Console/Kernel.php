<?php

namespace App\Console;

use App\Console\Commands\Copilot\MonitorUnclaimedRequests;
use App\Console\Commands\Fix\DeleteAccountPermanently;
use App\Console\Commands\Fix\FixBookingsRanking;
use App\Console\Commands\Fix\FixFlightSegmentGeolocation;
use App\Console\Commands\Fix\UpdateGeoLocationCruise;
use App\Console\Commands\ImportCruiseFiles;
use App\Console\Commands\MigrateDatabase;
use App\Console\Commands\Notifications\NotifyCopilotAboutStripeConnectionCommand;
use App\Console\Commands\Notifications\NotifyNewConciergeMessageCommand;
use App\Console\Commands\Notifications\NotifyNewRequestAvailableCommand;
use App\Console\Commands\Payments\DisburseCopilotPayment;
use App\Console\Commands\Payments\FetchParameters;
use App\Console\Commands\Payments\MonitorSubscriptions;
use App\Console\Commands\Sync\SyncDeleteItineraryOnGoogle;
use App\Console\Commands\Sync\SyncItineraryWithGoogle;
use App\Console\Commands\Sync\SyncTimezones;
use App\Console\Commands\Tests\CombineMJMLs;
use App\Console\Commands\Tests\GenerateFakeItinerary;
use App\Console\Commands\Tests\SampleCommand;
use App\Console\Commands\Tests\TestGoogleCalendar;
use App\Console\Commands\Tests\TestSMTP;
use App\Console\Commands\TextractOcr\OcrImportCommand;
use App\Console\Commands\TextractOcr\OcrInitializeCommand;
use App\Console\Commands\TextractOcr\OcrRecognizeCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        MonitorUnclaimedRequests::class,

        DeleteAccountPermanently::class,
        UpdateGeoLocationCruise::class,
        FixBookingsRanking::class,
        FixFlightSegmentGeolocation::class,

        NotifyNewConciergeMessageCommand::class,
        NotifyCopilotAboutStripeConnectionCommand::class,
        NotifyNewRequestAvailableCommand::class,

        TestSMTP::class,
        CombineMJMLs::class,
        TestGoogleCalendar::class,
        SampleCommand::class,

        GenerateFakeItinerary::class,
        ImportCruiseFiles::class,
        MigrateDatabase::class,

        Commands\Payments\DisburseCopilotPayment::class,

        Commands\Payments\SetupParameters::class,
        Commands\Payments\FetchParameters::class,
        Commands\Payments\SetupCustomerAccount::class,
        Commands\Payments\DetectSubscriptions::class,
        Commands\Payments\MonitorSubscriptions::class,
        Commands\Payments\MonitorConnectAccounts::class,

        SyncItineraryWithGoogle::class,
        SyncDeleteItineraryOnGoogle::class,
        SyncTimezones::class,


        OcrInitializeCommand::class,
        OcrRecognizeCommand::class,
        OcrImportCommand::class,

    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // https://laravel.com/docs/5.8/scheduling
        $schedule->command( MonitorUnclaimedRequests::class )
            ->environments(['production'])
            ->hourly( )
            ->runInBackground();   // allows other command scheduled to be able to run without this thread blocking


        $schedule->command( MonitorSubscriptions::class )
            ->environments(['production'])
            ->everyThirtyMinutes( )
            ->runInBackground();   // allows other command scheduled to be able to run without this thread blocking


        $schedule->command( FetchParameters::class )
            ->environments(['production'])
            ->dailyAt( '08:00' )
            ->runInBackground();   // allows other command scheduled to be able to run without this thread blocking

        $schedule->command( NotifyNewConciergeMessageCommand::class )
            ->environments(['production'])
            ->hourly()
            ->runInBackground();   // allows other command scheduled to be able to run without this thread blocking

        $schedule->command( NotifyCopilotAboutStripeConnectionCommand::class )
            ->environments(['production'])
            ->daily()
            ->runInBackground();   // allows other command scheduled to be able to run without this thread blocking

        // OCRS
        $schedule->command( OcrInitializeCommand::class )
//            ->environments(['production'])
            ->everyMinute()
            ->runInBackground();

        $schedule->command( OcrRecognizeCommand::class )
//            ->environments(['production'])
            ->everyMinute()
            ->runInBackground();

        $schedule->command( DisburseCopilotPayment::class )
            ->environments(['production'])
            ->hourly()
            ->runInBackground();

//
//        $schedule->command( OcrImportCommand::class )
////            ->environments(['production'])
//            ->everyMinute()
//            ->runInBackground();


        //        // Run the task every Sunday at 00:00
//        $schedule->command(SyncTimezones::class)
//            ->withoutOverlapping( 30 )  // prevents having more than one processes of this running at the same time
//            ->runInBackground()     // allows the process to continue without blocking
//            ->weekly();
//
//
    }
}

