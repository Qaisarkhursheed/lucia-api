<?php

namespace App\Console\Commands\Tests;

use App\Jobs\OcrImportJob;
use App\Mail\Auth\AccountApprovedMail;
use App\ModelsExtended\BookingOcr;
use App\ModelsExtended\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SampleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tests:echo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Just a simple hello';

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
        $this->info( "hello from other side! ");
//        Log::info( "hello from other side! ");
//
//        $ocr = BookingOcr::getById( 33 );
//        OcrImportJob::processImportation( $ocr );

//        Mail::send(new AccountApprovedMail(User::getAgent("ibukunoreofe@gmail.com")));
        return 0;
    }

}
