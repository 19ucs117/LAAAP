<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('department_id');
            $table->uuid('program_id');
            $table->string('course_code',10)->unique();
            $table->string('course_title');
            $table->integer('credits');
            $table->integer('hours');
            $table->enum('category',['MC','AL','SK']);
            $table->enum('semester',[1,2,3,4,5,6]);
            $table->integer('trackingNo')->default(1);
            $table->uuid('created_by');
            $table->uuid('updated_by');
            $table->timestamps();
            $table->unique(['course_code','semester','program_id']);
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
        Schema::dropIfExists('course_codes');
    }
}
