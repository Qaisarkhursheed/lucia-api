<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToStripeConnectReminderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stripe_connect_reminder', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_stripe_connect_reminder_user_id')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stripe_connect_reminder', function (Blueprint $table) {
            $table->dropForeign('fk_stripe_connect_reminder_user_id');
        });
    }
}
