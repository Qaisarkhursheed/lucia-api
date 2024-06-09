<?php

namespace Database\Seeders;

use App\ModelsExtended\ChatContentType;
use Illuminate\Database\Seeder;

class ChatContentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ChatContentType::upsert(
           [
               ["id" => ChatContentType::TEXT, "description" => "TEXT"],
               ["id" => ChatContentType::DOCUMENT, "description" => "DOCUMENT"],
               ["id" => ChatContentType::MEETING, "description" => "ZOOM Audio or video Meeting"],
           ], [ "id" ]
        );
    }
}
