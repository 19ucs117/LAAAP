<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentPeopsosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assessment_peopsos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('academic_year');
            $table->uuid('school_id');
            $table->uuid('department_id');
            $table->uuid('program_id');
            $table->json('direct_attainment');
            $table->json('peopso')->nullable();
            $table->timestamps();
            $table->foreign('school_id')->references('id')->on('schools')
                                                    ->onDelete('cascade')
                                                    ->onUpdate('cascade');
            $table->foreign('department_id')->references('id')->on('departments')
                                                    ->onDelete('cascade')
                                                    ->onUpdate('cascade');
            $table->foreign('program_id')->references('id')->on('programs')
                                                    ->onDelete('cascade')
                                                    ->onUpdate('cascade');
            $table->unique(['academic_year', 'school_id', 'department_id', 'program_id'], 'academic_dep_prog_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assessment_peopsos');
    }
}
