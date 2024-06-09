<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTodosTable extends Migration
{ /**
    * Run the migrations.
    *
    * @return void
    */
   public function up()
   {
       if (!Schema::hasColumn('todos', 'user_id')) {
           Schema::table('todos', function (Blueprint $table) {
               $table->unsignedBigInteger('user_id')->nullable();
               $table->foreign('user_id')
                   ->references('id')
                   ->on('users')
                   ->onDelete('cascade');
                $table->unsignedBigInteger('advisor_request_id')->nullable();
                $table->foreign('advisor_request_id')
                ->references('id')
                ->on('advisor_request')
                ->onDelete('cascade');
           });
       }
   }

   /**
    * Reverse the migrations.
    *
    * @return void
    */
   public function down()
   {
       if (Schema::hasColumn('todos', 'user_id')) {
           Schema::table('todos', function (Blueprint $table) {
               $table->dropForeign(['user_id']);
               $table->dropColumn('user_id');
           });
           Schema::table('todos', function (Blueprint $table) {
            $table->dropForeign(['advisor_request_id']);
            $table->dropColumn('advisor_request_id');
        });
       }
   }
}
