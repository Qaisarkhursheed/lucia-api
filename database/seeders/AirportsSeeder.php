<?php

namespace Database\Seeders;

use App\Models\Airport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class AirportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ( $this->getData() as $key => $item) {
            if( ! optional( $item )->iata ) continue;

            Airport::updateOrInsert( [ 'iata' => $item->iata ], Arr::only(  (array) $item , [
                'fs',
                'iata',
                'icao',
                'faa',
                'name',
                'city',
                'countryCode',
                'countryName',
                'regionName',
                'timeZoneRegionName',
                'weatherZone',
                'utcOffsetHours',
                'latitude',
                'longitude',
                'elevationFeet',
                'classification',
                'active',
                'weatherUrl',
                'delayIndexUrl'
            ] ));
        }
    }

    /**
     * @return array
     */
    private function getData(): array
    {
        return json_decode(
            file_get_contents(
                base_path( 'database/seeders/Airports.json' )
            ) )->airports;
    }
}
