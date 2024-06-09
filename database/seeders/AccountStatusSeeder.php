<?php

namespace Database\Seeders;

use App\ModelsExtended\AccountStatus;
use Illuminate\Database\Seeder;

class AccountStatusSeeder extends Seeder
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
        AccountStatus::upsert(
            [
                [ "id" => AccountStatus::PENDING_APPROVAL, "description" => "PENDING APPROVAL" ],
                [ "id" => AccountStatus::APPROVED, "description" => "APPROVED" ],
                [ "id" => AccountStatus::REJECTED, "description" => "REJECTED" ],
            ],
            [
                "id"
            ]
        );
    }
}
