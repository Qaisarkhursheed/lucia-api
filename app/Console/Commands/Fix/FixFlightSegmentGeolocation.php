<?php

namespace App\Console\Commands\Fix;

use App\ModelsExtended\Itinerary;
use App\ModelsExtended\ItineraryFlightSegment;
use App\Repositories\Maps\GoogleMaps\GoogleMapAddressAnalyzer;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class FixFlightSegmentGeolocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:flight-segment-geo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will update geo locations of all flight segments';

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
     * @throws GuzzleException
     */
    public function handle()
    {

        $this->withProgressBar( $this->getSegments() , function (ItineraryFlightSegment $segment ){
            $segment =   self::fixFlightFrom( $segment );
            $segment =   self::fixFlightTo( $segment );

            $segment->update();
        });
        $this->info( "\n-------------------------------------" );
        $this->info( "COMPLETED" );

        return true;
    }

    /**
     * @return Collection|Itinerary[]
     */
    private function getSegments()
    {
        return ItineraryFlightSegment::query()
            ->whereNull('flight_from_latitude')
            ->get();
    }


    /**
     * Please, note that this won't persist it. You need to call save
     *
     * @param ItineraryFlightSegment $segment
     * @return ItineraryFlightSegment
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function fixFlightFrom(ItineraryFlightSegment $segment): ItineraryFlightSegment
    {
        try {

            $a = new GoogleMapAddressAnalyzer($segment->flight_from);
            $segment->flight_from_longitude = $a->getLng();
            $segment->flight_from_latitude = $a->getLat();

            $a = new GoogleMapAddressAnalyzer($segment->flight_to);
            $segment->flight_to_longitude = $a->getLng();
            $segment->flight_to_latitude = $a->getLat();


        }catch (\Exception $exception)
        {
            Log::error("Segment: " . $segment->id . " message: "  . $exception->getMessage() );
        }

        return $segment;
    }

    /**
     * Please, note that this won't persist it. You need to call save
     *
     * @param ItineraryFlightSegment $segment
     * @return ItineraryFlightSegment
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function fixFlightTo(ItineraryFlightSegment $segment): ItineraryFlightSegment
    {
        try {

            $a = new GoogleMapAddressAnalyzer($segment->flight_to);
            $segment->flight_to_longitude = $a->getLng();
            $segment->flight_to_latitude = $a->getLat();

        }catch (\Exception $exception)
        {
            Log::error("Segment: " . $segment->id . " message: "  . $exception->getMessage() );
        }

        return $segment;
    }
}
