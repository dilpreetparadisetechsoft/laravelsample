<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssignJobTechTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assign_job_tech', function (Blueprint $table) {
            $table->bigIncrements('job_tech_id');
            $table->bigInteger('assign_job_id')->unsigned()->index(); 
            $table->string('job_techinians');
            $table->date('job_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        Schema::table('assign_job_tech', function (Blueprint $table) {
            $table->foreign('assign_job_id')->references('assign_job_id')->on('assign_jobs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assign_job_tech');
    }
}
