<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MoveLicensingToRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_role', function (Blueprint $table) {
            $table->json("stripe_subscription")->nullable();
            $table->boolean("has_valid_license")->nullable(false)->default(0);
        });

        DB::statement("update lucia_db.user_role inner join lucia_db.users on user_role.user_id = users.id set user_role.has_valid_license = users.has_valid_license");
        DB::statement("update lucia_db.user_role inner join lucia_db.user_stripe_account on user_stripe_account.user_id = user_role.user_id set user_role.stripe_subscription = user_stripe_account.stripe_subscription ");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn("has_valid_license");
        });

        Schema::table('user_stripe_account', function (Blueprint $table) {
            $table->dropColumn("stripe_subscription");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean("has_valid_license")->nullable(false)->default(0);
        });

        Schema::table('user_stripe_account', function (Blueprint $table) {
            $table->json("stripe_subscription")->nullable();
        });

        Schema::table('user_role', function (Blueprint $table) {
            $table->dropColumn("has_valid_license");
            $table->dropColumn("stripe_subscription");
        });
    }
}
