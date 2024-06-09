<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnRecieverIdInAdvisorRequestActivity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advisor_request_activity', function (Blueprint $table) {
            $table->bigInteger('receiver_id')->unsigned()->nullable();
            $table->integer('is_global')->default(0);

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
            $table->dropColumn('receiver_id');
            $table->dropColumn('is_global');
        });
    }
}
