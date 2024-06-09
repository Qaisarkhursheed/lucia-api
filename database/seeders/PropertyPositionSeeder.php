<?php

namespace Database\Seeders;

use App\ModelsExtended\PropertyPosition;
use Illuminate\Database\Seeder;

class PropertyPositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PropertyPosition::upsert(
           [
               [ "id" => PropertyPosition::DISABLED, "description" => "DISABLED" ],
               [ "id" => PropertyPosition::TOP_POSITION, "description" => "TOP POSITION" ],
               [ "id" => PropertyPosition::BOTTOM_POSITION, "description" => "BOTTOM POSITION" ],
           ],
            [
                "id"
            ]
        );
    }
}
