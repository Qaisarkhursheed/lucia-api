<?php

namespace Database\Seeders;

use App\ModelsExtended\BeddingType;
use Illuminate\Database\Seeder;

class BeddingTypeSeeder extends Seeder
{
    private function activateOptions(): void
    {
        BeddingType::query()
            ->whereIn("id", [
                BeddingType::King,
                BeddingType::Queen,
                BeddingType::Double,
                BeddingType::Two_Kings,
                BeddingType::Two_Queens,
                BeddingType::Two_Doubles,
                BeddingType::Two_Twins,
            ])
            ->update(['is_active' => true]);
    }

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
        BeddingType::upsert(
            [
                ["id" => BeddingType::King, "description" => "King"],
                ["id" => BeddingType::Queen, "description" => "Queen"],
                ["id" => BeddingType::Twin, "description" => "Twin"],
                ["id" => BeddingType::King__Twin, "description" => "King/Twin"],
                ["id" => BeddingType::Single, "description" => "Single"],
                ["id" => BeddingType::Three_Kings, "description" => "Three Kings"],
                ["id" => BeddingType::Two_Kings, "description" => "Two Kings"],
                ["id" => BeddingType::Two_Queens, "description" => "Two Queens"],
                ["id" => BeddingType::Two_Doubles, "description" => "Two Doubles"],
                ["id" => BeddingType::Two_Twins, "description" => "Two Twins"],
                ["id" => BeddingType::Two_Singles, "description" => "Two Singles"],
                ["id" => BeddingType::Double, "description" => "Double"],
                ["id" => BeddingType::Four_Post, "description" => "Four Post"],
                ["id" => BeddingType::Matrimonial, "description" => "Matrimonial"],
                ["id" => BeddingType::French_Bed, "description" => "French Bed"],
                ["id" => BeddingType::Bunk_Beds, "description" => "Bunk Beds"],
            ],
            [
                "id"
            ]
        );

        $this->activateOptions();
        $this->reOrderOptions();
    }

    private function reOrderOptions()
    {
        BeddingType::query()->where("id", BeddingType::King)->update([ 'sort_order' => 1 ]);
        BeddingType::query()->where("id", BeddingType::Queen)->update([ 'sort_order' => 5 ]);
        BeddingType::query()->where("id", BeddingType::Double)->update([ 'sort_order' => 10 ]);
        BeddingType::query()->where("id", BeddingType::Two_Kings)->update([ 'sort_order' => 15 ]);
        BeddingType::query()->where("id", BeddingType::Two_Queens)->update([ 'sort_order' => 20 ]);
        BeddingType::query()->where("id", BeddingType::Two_Doubles)->update([ 'sort_order' => 25 ]);
        BeddingType::query()->where("id", BeddingType::Two_Twins)->update([ 'sort_order' => 30 ]);
    }
}
