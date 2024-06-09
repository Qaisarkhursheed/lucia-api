<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsLuciaPaymentColumnToAvisorRequestPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advisor_request_payment', function (Blueprint $table) {
            $table->boolean('is_lucia_payment')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('advisor_request_payment', function (Blueprint $table) {
            $table->dropColumn('is_lucia_payment');
        });
    }
}
