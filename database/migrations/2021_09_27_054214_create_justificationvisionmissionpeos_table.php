<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJustificationvisionmissionpeosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('justificationvisionmissionpeos', function (Blueprint $table) {
          $table->uuid('id')->primary();
          $table->uuid('school_id');
          $table->json('mappingJustification');
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
        Schema::dropIfExists('justificationvisionmissionpeos');
    }
}
