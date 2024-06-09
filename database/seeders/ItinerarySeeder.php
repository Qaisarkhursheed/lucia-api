<?php

namespace Database\Seeders;

use App\ModelsExtended\Itinerary;
use Illuminate\Database\Seeder;

class ItinerarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Do not create itinerary without client
//        Itinerary::factory()
//            ->count(10 )
//            ->create();
    }
}
