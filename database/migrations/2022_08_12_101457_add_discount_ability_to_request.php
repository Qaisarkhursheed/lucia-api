<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountAbilityToRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advisor_request', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable()->default(null)->change();
            $table->string("discount_code", 50)->nullable(true);
            $table->decimal("discount", 10,2)->nullable(false)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('advisor_request', function (Blueprint $table) {
            $table->dropColumn("discount_code");
            $table->dropColumn("discount");
        });
    }
}
