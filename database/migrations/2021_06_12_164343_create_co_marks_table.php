<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoMarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('co_marks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('exam_type',[1,2,3,4]);
            $table->uuid('course_id');
            $table->integer('total_mark');
            $table->timestamps();
            $table->unique(['exam_type','course_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('co_marks');
    }
}
