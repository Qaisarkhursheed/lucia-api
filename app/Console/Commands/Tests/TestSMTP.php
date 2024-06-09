<?php

namespace App\Console\Commands\Tests;

use App\Mail\SampleMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TestSMTP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tests:smtp {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make sure smtp is working by sending a dummy email';

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

            Mail::send( new SampleMail( $this->argument( "email" ) ) );

            $this->info( "Mail sent \n" );

        }catch ( \Exception $ex ){

            Log::error(  $ex->getMessage(), $ex->getTrace() );
            $this->error( $ex->getMessage(), $ex->getTraceAsString()  );
        }

        return 0;
    }

}
