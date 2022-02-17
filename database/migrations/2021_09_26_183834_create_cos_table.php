<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cos', function (Blueprint $table) {
          $table->uuid('id')->primary();
          $table->uuid('department_id');
          $table->uuid('course_id');
          $table->string('labelNo');
          $table->text('description');
          $table->text('cogLevel');
          $table->uuid('saved_by');
          $table->boolean('submit')->default(0);
          $table->timestamps();
          $table->foreign('department_id')->references('id')->on('departments')
                                                          ->onUpdate('cascade')
                                                          ->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('course_codes')
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
        Schema::dropIfExists('cos');
    }
}
