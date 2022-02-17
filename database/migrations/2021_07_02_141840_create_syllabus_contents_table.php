<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSyllabusContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('syllabus_contents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('syllabus_id');
            $table->integer('unit');
            $table->longText('content');
            $table->integer('hours');
            $table->json('COs');
            $table->json('Cognitive_level');
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
        Schema::dropIfExists('syllabus_contents');
    }
}
