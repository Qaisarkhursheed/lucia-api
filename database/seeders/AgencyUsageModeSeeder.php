<?php

namespace Database\Seeders;

use App\ModelsExtended\AgencyUsageMode;
use Illuminate\Database\Seeder;

class AgencyUsageModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       self::createOrUpdate();
    }

    public static function createOrUpdate()
    {
        AgencyUsageMode::upsert(
            [
                [ "id" => AgencyUsageMode::CO_PILOT, "description" => "Co-pilot", 'notes' => 'You can hire and submit request to our advisors. You can change your account to a full account later.' ],
                [ "id" => AgencyUsageMode::LUCIA_EXPERIENCE, "description" => "The Lucia Experience", 'notes' => 'This includes access to all the features of lucia. You can hire advisors and create beautiful itineraries.' ],
            ],
            [
                "id"
            ]
        );
    }
}
