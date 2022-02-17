<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCognitivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cognitives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('cm_id_fk');
            $table->enum('cognitive_level',['k1','k2','k3','k4','k5','k6']);
            $table->integer('max_mark');
            $table->integer('scored_mark');
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
        Schema::dropIfExists('cognitives');
    }
}
