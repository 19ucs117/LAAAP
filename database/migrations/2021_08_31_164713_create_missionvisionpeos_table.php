<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionvisionpeosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('missionvisionpeos', function (Blueprint $table) {
          $table->uuid('id')->primary();
          $table->uuid('school_id');
          $table->json('mapping');
          $table->uuid('saved_by');
          $table->boolean('submit')->default(0);
          $table->timestamps();
          $table->foreign('school_id')->references('id')->on('schools')
                                                        ->onUpdate('cascade')
                                                        ->onDelete('cascade');
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
        Schema::dropIfExists('missionvisionpeos');
    }
}
