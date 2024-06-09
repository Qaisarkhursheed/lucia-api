<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvisorTaskCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advisor_task_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advisor_request_task_id')->nullable();
            // $table->foreign('advisor_request_task_id')
            //     ->references('id')
            //     ->on('advisor_request_task')
            //     ->onDelete('cascade');
             $table->unsignedBigInteger('category_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table->dropForeign(['advisor_request_task_id']);
        Schema::dropIfExists('advisor_task_categories');
    }
}
