<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToProductPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('application_product_prices', function (Blueprint $table) {
           $table->dropForeign("fk_application_product_prices_product_id");
           $table->dropUnique("uq_application_product_prices_unique");
            $table->string("description", 150)->nullable(true);
        });

        \Illuminate\Support\Facades\DB::statement("update lucia_db.application_product_prices set description = recurring ");

        Schema::table('application_product_prices', function (Blueprint $table) {
            $table->string("description", 150)->nullable(false)->change();
            $table->unique(["description", "application_product_id"], "uq_application_product_prices_unique");
            $table->foreign('application_product_id', 'fk_application_product_prices_product_id')->references('id')->on('application_products')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('application_product_prices', function (Blueprint $table) {
            $table->dropForeign("fk_application_product_prices_product_id");
            $table->dropUnique("uq_application_product_prices_unique");
            $table->dropColumn("description");
            $table->unique(["recurring", "application_product_id"], "uq_application_product_prices_unique");
            $table->foreign('application_product_id', 'fk_application_product_prices_product_id')->references('id')->on('application_products')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

    }
}
