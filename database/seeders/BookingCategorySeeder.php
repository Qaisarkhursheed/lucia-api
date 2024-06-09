<?php

namespace Database\Seeders;

use App\ModelsExtended\BookingCategory;
use Illuminate\Database\Seeder;

class BookingCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BookingCategory::upsert(
            [
                [ "id" => BookingCategory::Flight, "description" => "Flight" ],
                [ "id" => BookingCategory::Hotel, "description" => "Hotel" ],
                [ "id" => BookingCategory::Concierge, "description" => "Concierge" ],
                [ "id" => BookingCategory::Cruise, "description" => "Cruise" ],
                [ "id" => BookingCategory::Transportation, "description" => "Transportation" ],
                [ "id" => BookingCategory::Tour_Activity, "description" => "Tour Activity" ],
                [ "id" => BookingCategory::Insurance, "description" => "Insurance" ],
                [ "id" => BookingCategory::Other_Notes, "description" => "Other Notes" ],
                [ "id" => BookingCategory::Header, "description" => "Header" ],
                [ "id" => BookingCategory::Divider, "description" => "Divider" ],
            ], [ "id" ]
        );
    }
}
