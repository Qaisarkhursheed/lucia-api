<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class CreateDbTimezoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('db_timezone', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('offset_seconds')->nullable();
            $table->integer('offset_minutes')->nullable();
            $table->string('offset_gmt', 100)->nullable();
            $table->string('offset_tzid', 100)->nullable();
            $table->string('offset_tzab', 100)->nullable();
            $table->string('offset_tzfull', 100)->nullable();
            $table->string('country_name', 200)->nullable();
            $table->string('timezone_id', 150)->unique('uq_db_timezone_unique');
        });

        Artisan::call( 'sync:timezones' );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('db_timezone');
    }
}
