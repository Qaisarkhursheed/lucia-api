<?php

namespace App\Console\Commands\Sync;

use App\Models\DbTimezone;
use App\Repositories\TimeZoneInvoker;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncTimezones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:timezones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will update timezone data in database';

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

            // Create or update time zones
            $this->createTimezones();

            $s = DbTimezone::all();

            $bar = $this->output->createProgressBar( $s->count()  );
            $bar->start();

            foreach ( $s as $v )
            {
                try {

                    $this->updateTimeZone( $v );

                } catch (\Exception $ex) {
                    Log::error(  $ex->getMessage(), $ex->getTrace() );
                }
                $bar->advance();
            }

            $bar->finish();
            $this->info("\n\n");




        }catch ( \Exception $ex ){

            Log::error(  $ex->getMessage(), $ex->getTrace() );
            $this->error( $ex->getMessage(), $ex->getTraceAsString()  );
        }


        return 0;
    }


    private function createTimezones( )
    {
        foreach( DateTimeZone::listIdentifiers(DateTimeZone::ALL) as $tid )
        {
            DbTimezone::query()->updateOrInsert(
                [
                    "timezone_id" => $tid
                ]
            );
        }
    }


    private function updateTimeZone(DbTimezone $v)
    {
        $t = new TimeZoneInvoker( $v->timezone_id );

        $v->update( [
            'country_name' => $t->getData()->data->timezone->country_name,
            'offset_seconds' => $t->getData()->data->datetime->offset_seconds,
            'offset_minutes' => $t->getData()->data->datetime->offset_minutes,
            'offset_gmt' => $t->getData()->data->datetime->offset_gmt,
            'offset_tzid' => $t->getData()->data->datetime->offset_tzid,
            'offset_tzab' => $t->getData()->data->datetime->offset_tzab,
            'offset_tzfull' => $t->getData()->data->datetime->offset_tzfull,
        ] );
    }
}
