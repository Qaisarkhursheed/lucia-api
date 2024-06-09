<?php

namespace Database\Seeders;

use App\ModelsExtended\ServiceSupplier;
use Illuminate\Database\Seeder;

class ServiceSupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ServiceSupplier::factory()
            ->count(5)
            ->create();
    }
}
