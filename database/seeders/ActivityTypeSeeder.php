<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use Illuminate\Database\Seeder;

class ActivityTypeSeeder extends Seeder
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
        ActivityType::upsert(
            [
                [ "id" => ActivityType::MESSAGE, "description" => "Messages" ],
                [ "id" => ActivityType::ADVISOR_REQUEST, "description" => "Advisor Request" ],
                [ "id" => ActivityType::MEETING, "description" => "Audio or Video Meeting" ],
            ],
            [
                "id"
            ]
        );
    }
}
