<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentmarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('studentmarks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('department_name', 100);
            $table->string('program_name', 30);
            $table->string('regulation', 45);
            $table->string('course_code', 45);
            $table->string('course_title', 45);
            $table->string('staff_code', 45);
            $table->string('section', 45);
            $table->string('staff_name', 255);
            $table->json('direct_attainment');
            $table->json('co')->nullable();
            $table->json('indirect_attainment')->nullable();
            $table->json('consolidated_co')->nullable();
            $table->double('co_avarage', 4, 2)->nullable();
            $table->json('feed_back')->nullable();
            $table->timestamps();
            $table->unique(['regulation', 'course_code', 'staff_code', 'section'], 'exammarks_UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('studentmarks');
    }
}
