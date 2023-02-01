<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSelectedSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('selected_subjects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('course_id');
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_codes')
                                                          ->onUpdate('cascade')
                                                          ->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('student_details')
                                                          ->onUpdate('cascade')
                                                          ->onDelete('cascade');
            
            $table->unique(['student_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('selected_subjects');
    }
}
