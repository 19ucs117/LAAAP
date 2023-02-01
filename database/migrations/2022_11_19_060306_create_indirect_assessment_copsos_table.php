<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndirectAssessmentCopsosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indirect_assessment_copsos', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('regulation');
            $table->uuid('department_name');
            $table->uuid('program_name');
            $table->uuid('staff_id');
            $table->json('indirect_assessment');
            $table->json('indirect_assessment_avarage');
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
        Schema::dropIfExists('indirect_assessment_copsos');
    }
}
