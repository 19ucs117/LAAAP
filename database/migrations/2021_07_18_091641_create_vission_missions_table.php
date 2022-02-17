<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVissionMissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vission_missions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->longText('vision');
            $table->longText('mission_one');
            $table->longText('mission_two');
            $table->longText('mission_three');
            $table->longText('mission_four');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vission_missions');
    }
}
