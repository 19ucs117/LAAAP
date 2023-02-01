<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndirectposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indirectpos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('regulation');
            $table->uuid('school_name');
            $table->uuid('staff_id');
            $table->json('indirect_assessment');
            $table->json('indirect_assessment_100_percentage');
            $table->json('indirect_assessment_10_percentage');
            $table->timestamps();
            $table->unique(['regulation', 'school_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('indirectpos');
    }
}
