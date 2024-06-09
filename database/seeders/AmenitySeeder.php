<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $this->createOrUpdate();
    }

    private function createOrUpdate()
    {
        Amenity::upsert(
            [
                ["id" => 1, "description" => "Upgrade on arrival, subject to availability", "image_relative_url" => 'amenities/icon-amenities-hotel_upgrade.svg' , "important" => true ],
                ["id" => 2, "description" => "Daily breakfast for two", "image_relative_url" => 'amenities/icon-amenities-espresso_cup.svg' , "important" => true ],
                ["id" => 3, "description" => "$100 resort credit", "image_relative_url" => 'amenities/icon-amenities-cheque.svg', "important" => true ],
                ["id" => 4, "description" => "Early check-in/late check-out, subject to availability", "image_relative_url" => 'amenities/icon-amenities-room_service.svg', "important" => true ],
                ["id" => 5, "description" => "Complimentary Wi-Fi", "image_relative_url" => 'amenities/icon-amenities-wi-fi_connected.svg', "important" => true ],

                ["id" => 6, "description" => "Parking", "image_relative_url" => 'amenities/icon-amenities-parking.svg', "important" => false ],
                ["id" => 7, "description" => "24-Hour Guest Reception", "image_relative_url" => 'amenities/icon-amenities-hotel_check_in.svg', "important" => false ],
                ["id" => 8, "description" => "Pool", "image_relative_url" => 'amenities/icon-amenities-outdoor_swimming_pool.svg', "important" => false ],
                ["id" => 9, "description" => "Business Facilities", "image_relative_url" => 'amenities/icon-amenities-condo.svg', "important" => false ],
                ["id" => 10, "description" => "Transportation Information", "image_relative_url" => 'amenities/icon-amenities-transportation.svg', "important" => false ],
                ["id" => 11, "description" => "Laundry Services", "image_relative_url" => 'amenities/icon-amenities-rich.svg', "important" => false ],
                ["id" => 12, "description" => "Spa & Wellness Amenities", "image_relative_url" => 'amenities/icon-amenities-fountain.svg', "important" => false ],
                ["id" => 13, "description" => "Exercise Facilities and Accessories", "image_relative_url" => 'amenities/icon-amenities-gym.svg', "important" => false ],
                ["id" => 14, "description" => "Premium Bedding", "image_relative_url" => 'amenities/icon-amenities-bedroom.svg', "important" => false ],
                ["id" => 15, "description" => "Pet-friendly Rooms", "image_relative_url" => 'amenities/icon-amenities-pet.svg', "important" => false ],
            ],
            [
                "id"
            ]
        );
    }
}
