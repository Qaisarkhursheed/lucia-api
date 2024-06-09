<?php

namespace App\Console\Commands\Fix;

use App\ModelsExtended\ItineraryCruise;
use App\Repositories\Maps\GoogleMaps\GoogleMapAddressAnalyzer;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class UpdateGeoLocationCruise extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:update-geolocation-cruise';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will update geolocation on cruises information';

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
        $this->updateOldRecords();

        $this->info( "\n-------------------------------------" );
        $this->info( "COMPLETED" );

        return true;
    }

    /**
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function updateOldRecords()
    {
        $records = ItineraryCruise::query()
            ->whereNull('departure_latitude')
            ->whereHas("itinerary", function (Builder $builder) {
                $builder->whereNull("itinerary.deleted_at");
            })
            ->get();

        $this->withProgressBar($records, function (ItineraryCruise $record) {

            try {
                $depart = new GoogleMapAddressAnalyzer( $record->departure_port_city );
                $arrive = new GoogleMapAddressAnalyzer( $record->arrival_port_city );

                $record->departure_latitude = $depart->getLat();
                $record->departure_longitude = $depart->getLng();

                $record->arrival_latitude = $arrive->getLat();
                $record->arrival_longitude = $arrive->getLng();

                $record->updateQuietly();
            }catch (\Exception $exception ){
                $this->error("Error processing this cruise Id: " . $record->id );
                Log::error(  "Error processing this cruise Id: " . $record->id  , $exception->getTrace() );
            }
        });
    }
}
