<?php

namespace Database\Seeders;

use App\ModelsExtended\AdvisorRequestStatus;
use Illuminate\Database\Seeder;

class AdvisorRequestStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AdvisorRequestStatus::upsert(
           [
               ["id" => AdvisorRequestStatus::DRAFT, "description" => "DRAFT"],
               ["id" => AdvisorRequestStatus::PAID, "description" => "PAID"],
               ["id" => AdvisorRequestStatus::ACCEPTED, "description" => "ACCEPTED"],
               ["id" => AdvisorRequestStatus::COMPLETED, "description" => "COMPLETED"],
               ["id" => AdvisorRequestStatus::REFUNDED, "description" => "REFUNDED"],
               ["id" => AdvisorRequestStatus::PENDING, "description" => "PENDING"],
               ["id" => AdvisorRequestStatus::CANCELLED, "description" => "CANCELLED"],
           ], [ "id" ]
        );
    }
}
