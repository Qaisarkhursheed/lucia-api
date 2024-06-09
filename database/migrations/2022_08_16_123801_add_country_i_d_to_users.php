<?php

use App\ModelsExtended\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountryIDToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger("country_id")->unsigned()
                ->nullable(false)
                ->default(Country::US)
                ->index("fk_users_country_id");
            $table->foreign('country_id', 'fk_users_country_id')
                ->references('id')->on('countries')
                ->onUpdate('NO ACTION')->onDelete('NO ACTION');

            $table->dropColumn("country");
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
            $table->dropForeign('fk_users_country_id');
            $table->dropColumn('country_id');
            $table->string('country', 150)->nullable(true);
        });
    }
}
