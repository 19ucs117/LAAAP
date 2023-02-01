<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('department_id');
            $table->uuid('program_id');
            $table->integer('batchNo');
            $table->integer('NoSections');
            $table->timestamps();
            $table->foreign('program_id')->references('id')->on('programs')
                                                          ->onUpdate('cascade')
                                                          ->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')
                                                          ->onUpdate('cascade')
                                                          ->onDelete('cascade');
            $table->unique(['department_id', 'program_id', 'batchNo']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batch_details');
    }
}
