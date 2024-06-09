<?php

namespace Database\Seeders;

use App\ModelsExtended\TransitType;
use Illuminate\Database\Seeder;

class TransitTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TransitType::upsert(
           [
               [ "id" => TransitType::Rail, "description" => "Rail" ],
               [ "id" => TransitType::Ferry, "description" => "Ferry" ],
               [ "id" => TransitType::Car, "description" => "Rental Car" ],
               [ "id" => TransitType::Transfer, "description" => "Transfer" ],
           ],
            [
                "id"
            ] 
        );
    }
}
