<?php

namespace Database\Seeders;

use App\ModelsExtended\FeedbackTopic;
use Illuminate\Database\Seeder;

class FeedbackTopicSeeder extends Seeder
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
        FeedbackTopic::upsert(
            [
                [ "id" => FeedbackTopic::Efficiency, "description" => "Efficiency" ],
                [ "id" => FeedbackTopic::Accuracy, "description" => "Accuracy" ],
                [ "id" => FeedbackTopic::Task_Completion, "description" => "Task Completion" ],
                [ "id" => FeedbackTopic::Friendliness, "description" => "Friendliness" ],
            ],
            [
                "id"
            ]
        );
    }
}
