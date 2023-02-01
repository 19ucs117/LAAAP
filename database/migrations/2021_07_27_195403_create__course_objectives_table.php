<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseObjectivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('CourseObjectives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('department_id');
            $table->uuid('course_id');
            $table->longText('courseObjectives');
            $table->uuid('saved_by');
            $table->boolean('submit')->default(0);
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_codes')
                                                          ->onUpdate('cascade')
                                                          ->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')
                                                          ->onUpdate('cascade')
                                                          ->onDelete('cascade');
            $table->unique(['course_id', 'department_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('CourseObjectives');
    }
}
