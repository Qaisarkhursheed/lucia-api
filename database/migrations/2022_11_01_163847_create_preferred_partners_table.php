<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreferredPartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

            Schema::create('preferred_partners', function (Blueprint $table) {
                $table->increments('id');
                $table->string('company_name', 255)->nullable(false);
                $table->string('contact_person_name',100)->nullable(false);
                $table->string('contact_email',50)->nullable(false);
                $table->string('monthly_price',100)->nullable(false);
                $table->string('annual_price',100)->nullable(false);
                $table->string('website',100)->nullable(true);
                $table->string('logo',100)->nullable(true);
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
        Schema::dropIfExists('preferred_partners');
    }
}
