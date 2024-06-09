<?php

namespace Database\Seeders;

use App\ModelsExtended\ApplicationProduct;
use App\ModelsExtended\ApplicationProductPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ApplicationProductSeeder extends Seeder
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

    public static function createOrUpdate()
    {
        ApplicationProduct::upsert(
            [
                [ "id" => ApplicationProduct::LUCIA, "name" => "Lucia", "description" => "Letslucia Subscription",  ],
            ],
            [
                "id"
            ]
        );

        ApplicationProductPrice::upsert(
            [
                [
                    "id" => ApplicationProductPrice::LUCIA_EXPERIENCE_MONTHLY,
                    "description" => "LUCIA EXPERIENCE MONTHLY",
                    "unit_amount" => 99,
                    "recurring" => "month",
                    "application_product_id" => ApplicationProduct::LUCIA
                ],
                [
                    "id" => ApplicationProductPrice::LUCIA_EXPERIENCE_YEARLY,
                    "description" => "LUCIA EXPERIENCE YEARLY",
                    "unit_amount" => 55,
                    "recurring" => "year",
                    "application_product_id" => ApplicationProduct::LUCIA
                ],
                [
                    "id" => ApplicationProductPrice::LUCIA_COPILOT_ONLY_MONTHLY,
                    "description" => "LUCIA COPILOT ONLY MONTHLY",
                    "unit_amount" => 45,
                    "recurring" => "month",
                    "application_product_id" => ApplicationProduct::LUCIA
                ],
            ],
            [
                "id"
            ]
        );

        // Set up where necessary
        Artisan::call( 'payments:fetch-params' );
//        Artisan::call( 'payments:setup-customer' );

    }
}
