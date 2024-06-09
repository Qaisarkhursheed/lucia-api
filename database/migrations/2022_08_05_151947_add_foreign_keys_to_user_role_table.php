<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToUserRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_role', function (Blueprint $table) {
            $table->foreign('role_id', 'fk_user_role_role_id')->references('id')->on('roles')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('user_id', 'fk_user_role_user_id')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });

        // copy current roles
        DB::statement("insert into lucia_db.user_role (user_id, role_id, created_at, updated_at)
select id, role_id, created_at, updated_at from lucia_db.users");

        // remove from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign("fk_users_role_id");
            $table->dropColumn("role_id");
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_role', function (Blueprint $table) {
            $table->dropForeign('fk_user_role_role_id');
            $table->dropForeign('fk_user_role_user_id');
        });
    }
}
