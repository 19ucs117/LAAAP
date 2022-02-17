<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDynamicLessonPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dynamic_lesson_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('department_id');
            $table->uuid('course_id');
            $table->integer('unit');
            $table->text('content');
            $table->integer('teachingHours');
            $table->string('cognitiveLevel');
            $table->string('cos');
            $table->integer('coAttainmentThreshold');
            $table->text('instructionalMethodologies');
            $table->text('directAssessmentMethods');
            $table->uuid('saved_by');
            $table->integer('submit')->default(0);
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_codes')
                                                            ->onUpdate('cascade')
                                                            ->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')
                                                            ->onUpdate('cascade')
                                                            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dynamic_lesson_plans');
    }
}
