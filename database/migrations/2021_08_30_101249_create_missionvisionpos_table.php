<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionvisionposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('missionvisionpos', function (Blueprint $table) {
          $table->uuid('id')->primary();
          $table->uuid('school_id');
          $table->json('mapping');
          $table->uuid('saved_by');
          $table->boolean('submit')->default(0);
          $table->timestamps();
          $table->unique('school_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('missionvisionpos');
    }
}
