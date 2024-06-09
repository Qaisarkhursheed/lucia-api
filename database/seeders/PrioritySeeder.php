<?php

namespace Database\Seeders;

use App\ModelsExtended\Priority;
use Illuminate\Database\Seeder;

class PrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Priority::upsert(
           [
               [ "id" => Priority::Low, "description" => "Low" ],
               [ "id" => Priority::High, "description" => "High" ],
           ],
            [
                "id"
            ]
        );
    }
}
