<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSyllabusCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('syllabus_courses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('department_id');
            $table->string('course_title');
            $table->integer('credits');
            $table->integer('hours');
            $table->enum('category', ['MC', 'AL', 'SK']);
            $table->enum('semester', [1, 2, 3, 4, 5, 6]);
            $table->uuid('created_by');
            $table->uuid('updated_by');
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
        Schema::dropIfExists('syllabus_courses');
    }
}
