<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignstaffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assignstaffs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('department_id');
            $table->uuid('program_id');
            $table->uuid('course_id');
            $table->uuid('batch_id');
            $table->string('section');
            $table->string('user_id');
            $table->uuid('assigned_by');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')
                                                          ->onUpdate('cascade')
                                                          ->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('batch_details')
                                                          ->onUpdate('cascade')
                                                          ->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('course_codes')
                                                          ->onUpdate('cascade')
                                                          ->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('programs')
                                                          ->onUpdate('cascade')
                                                          ->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')
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
        Schema::dropIfExists('assignstaffs');
    }
}
