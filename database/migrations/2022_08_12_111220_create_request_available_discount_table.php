<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestAvailableDiscountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_available_discount', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('description', 30)->unique('uq_request_available_discount_unique');
            $table->unsignedInteger('limit_to_usage_count')->default(1);
            $table->decimal('discount', 10)->unsigned()->default(0.00);
            $table->decimal('limit_purchase_amount', 10)->unsigned()->default(0.00);
            $table->boolean('is_active')->nullable(false)->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_available_discount');
    }
}
