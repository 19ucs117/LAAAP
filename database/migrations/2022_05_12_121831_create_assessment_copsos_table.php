<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentCopsosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        Schema::create('assessment_copsos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('co_id')->unique();
            $table->json('direct_attainment');
            $table->json('copso')->nullable();
            $table->double('copso_avarage', 4, 2)->nullable();
            $table->json('consolidate')->nullable();
            $table->json('feed_back')->nullable();
            $table->timestamps();
            $table->foreign('co_id')->references('id')->on('studentmarks')
                                                    ->onDelete('cascade')
                                                    ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assessment_copsos');
    }
}
