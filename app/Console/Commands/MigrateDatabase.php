<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class MigrateDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will detect if it is new database or existing one to run right command';

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
        if( Schema::hasTable('users') )
        {
            // old database
            Artisan::call( 'migrate --force --seed --schema-path no-schema' );
        }else{
            // new database
            Artisan::call( 'migrate:fresh --force --seed' );
        }

        $this->info( Artisan::output() );

        return true;
    }
}
