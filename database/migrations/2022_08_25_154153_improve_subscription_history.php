<?php

use App\ModelsExtended\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ImproveSubscriptionHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('stripe_subscription_history', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->index('fk_stripe_subscription_history_role_id')->nullable(false)->default(Role::Agent);
            $table->foreign('role_id', 'fk_stripe_subscription_history_role_id')->references('id')->on('roles')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->dropColumn('stripe_customer');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stripe_subscription_history', function (Blueprint $table) {
            $table->dropForeign('fk_stripe_subscription_history_role_id');
            $table->dropColumn('role_id');
            $table->json('stripe_customer')->nullable();
        });
    }
}
