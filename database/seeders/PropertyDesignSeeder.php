<?php

namespace Database\Seeders;

use App\ModelsExtended\PropertyDesign;
use Illuminate\Database\Seeder;

class PropertyDesignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PropertyDesign::upsert(
           [
               [ "id" => PropertyDesign::Lucia_Style, "description" => "Lucia" ],
               [ "id" => PropertyDesign::Simple_Style, "description" => "Simple" ],
               [ "id" => PropertyDesign::Modern_Style, "description" => "Modern" ],
               [ "id" => PropertyDesign::Elegant_Style, "description" => "Elegant" ],
           ],
            [
                "id"
            ]
        );
    }
}
