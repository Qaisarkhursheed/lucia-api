<?php

namespace Database\Seeders;

use App\Models\AdvisorRequestType;
use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AdvisorRequestTypeSeeder extends Seeder
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

    private function createOrUpdate()
    {
        AdvisorRequestType::upsert(
            [
                ["id" => 1, "description" => "Help with planning", "explanation"=> "Destination/supplier research, hotel quotes, make proposals, etc.", "amount" => 100 , "copilot_remarks" => "Planning (destination/supplier research, rate quotes, make proposals, etc)" ],
                ["id" => 2, "description" => "Booking", "explanation"=> "Booking restaurants, tours, spa appointments,sabre/gds, etc.", "amount" => 50 , "copilot_remarks" => "Booking (booking restaurants, tours, spa appointments, and transfers, GDS bookings, ticketing flights, etc)" ],
                ["id" => 3, "description" => "Post Booking", "explanation"=> "Communication w/suppliers, change or confirm reservations, etc.", "amount" => 200 , "copilot_remarks" => "Post-Booking (Communicating with suppliers, change or confirm reservations, invoicing, commission tracking, etc)" ],
                ["id" => 4, "description" => "URGENT REQUEST", "explanation"=> "Help with urgent task such as Flights, Hotels and more.", "amount" => 300 , "copilot_remarks" => "Urgent Requests: Higher $$ but 1-3 hour turnaround time" ],
            ],
            [
                "id"
            ]
        );
    }
}
