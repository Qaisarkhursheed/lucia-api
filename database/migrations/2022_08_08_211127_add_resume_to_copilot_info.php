<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResumeToCopilotInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('copilot_info', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable()->default(null)->change();
            $table->string("resume_relative_url", 300)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('copilot_info', function (Blueprint $table) {
            $table->dropColumn("resume_relative_url");
        });
    }
}
