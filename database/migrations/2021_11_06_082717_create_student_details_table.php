<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('department_id');
            $table->uuid('program_id');
            $table->uuid('batch_id');
            $table->string('departmentNumber');
            $table->string('name');
            $table->string('section');
            $table->timestamps();
            $table->foreign('program_id')->references('id')->on('programs')
                                                          ->onUpdate('cascade')
                                                          ->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')
                                                          ->onUpdate('cascade')
                                                          ->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('batch_details')
                                                        ->onUpdate('cascade')
                                                        ->onDelete('cascade');
            $table->unique(['department_id', 'program_id', 'departmentNumber']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_details');
    }
}
