<?php

namespace Database\Seeders;

use App\ModelsExtended\PassengerType;
use Illuminate\Database\Seeder;

class PassengerTypeSeeder extends Seeder
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
        PassengerType::upsert(
            [
                [ "id" => PassengerType::Adult, "description" => "Adult" ],
                [ "id" => PassengerType::Child, "description" => "Child" ],
                [ "id" => PassengerType::Baby, "description" => "Baby" ],
                [ "id" => PassengerType::None, "description" => "None" ],
            ],
            [
                "id"
            ]
        );
    }
}
