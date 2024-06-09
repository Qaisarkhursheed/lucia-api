<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvisorRequestArchivedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advisor_request_archived', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('advisor_request_id')->nullable(false);
            $table->integer('copilot_id')->nullable(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advisor_request_archived');
        // Schema::table('advisor_request_archived', function (Blueprint $table) {
        //     $table->dropForeign('fk_advisor_request_archived_advisor_request_id');
        //     $table->dropForeign('fk_advisor_request_archived_copilot_id');
        // });
    }
}
