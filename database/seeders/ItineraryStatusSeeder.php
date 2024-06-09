<?php

namespace Database\Seeders;

use App\ModelsExtended\ItineraryStatus;
use Illuminate\Database\Seeder;

class ItineraryStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        ItineraryStatus::query()->delete();
        ItineraryStatus::upsert(
           [
               [ "id" => ItineraryStatus::Sent, "description" => "Sent" ],
               [ "id" => ItineraryStatus::Declined, "description" => "Declined" ],
               [ "id" => ItineraryStatus::Accepted, "description" => "Accepted" ],
               [ "id" => ItineraryStatus::Draft, "description" => "Draft" ],
           ],
            [
                "id"
            ]
        );
    }
}
