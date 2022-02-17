<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionvisionpsosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('missionvisionpsos', function (Blueprint $table) {
          $table->uuid('id')->primary();
          $table->uuid('department_id');
          $table->uuid('program_id');
          $table->json('mapping');
          $table->uuid('saved_by');
          $table->boolean('submit')->default(0);
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
        Schema::dropIfExists('missionvisionpsos');
    }
}
