<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExammarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exammarks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('batch_id');
            $table->uuid('department_id');
            $table->uuid('course_id');
            $table->string('section');
            $table->json('assessment');
            $table->uuid('saved_by');
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_codes')
                                                          ->onUpdate('cascade')
                                                          ->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')
                                                          ->onUpdate('cascade')
                                                          ->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('batch_details')
                                                          ->onUpdate('cascade')
                                                          ->onDelete('cascade');
            $table->unique(['course_id','section', 'batch_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exammarks');
    }
}
