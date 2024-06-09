<?php

namespace Database\Seeders;

use App\ModelsExtended\OcrStatus;
use Illuminate\Database\Seeder;

class OcrStatusSeeder extends Seeder
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
        OcrStatus::upsert(
            [
                [ "id" => OcrStatus::QUEUED, "description" => "QUEUED" ],
                [ "id" => OcrStatus::INITIALIZED, "description" => "INITIALIZED" ],
                [ "id" => OcrStatus::RECOGNIZING, "description" => "RECOGNIZING" ],
                [ "id" => OcrStatus::FAILED_RECOGNITION, "description" => "FAILED RECOGNITION" ],
                [ "id" => OcrStatus::COMPLETED_RECOGNITION, "description" => "COMPLETED RECOGNITION" ],
                [ "id" => OcrStatus::FAILED_IMPORTATION, "description" => "FAILED IMPORTATION" ],
                [ "id" => OcrStatus::IMPORTED, "description" => "IMPORTED" ],
                [ "id" => OcrStatus::IMPORTING, "description" => "IMPORTING" ],
            ],
            [
                "id"
            ]
        );
    }
}
