<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAdvisorRequestTypeIdInAdvisorRequestTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE advisor_request_task DROP FOREIGN KEY fk_advisor_request_task_type_id");
        Schema::table('advisor_request_task', function (Blueprint $table) {
            $table->string('advisor_request_type_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('advisor_request_task', function (Blueprint $table) {
            $table->string('advisor_request_type_id')->notnull()->change();
        });
    }
}
