<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeColumnInAdvisorRequestActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advisor_request_activity', function (Blueprint $table) {
            $table->integer('type')->nullable();
            $table->boolean('is_seen')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('advisor_request_activity', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('is_seen');
        });
    }
}
