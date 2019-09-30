<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssignJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assign_jobs', function (Blueprint $table) {
            $table->bigIncrements('assign_job_id');
            $table->bigInteger('estimate_id')->unsigned()->index(); 
            $table->bigInteger('comp_id')->unsigned()->index(); 
            $table->string('assign_job_sno');
            $table->string('title');
            $table->string('assigned_user')->nullable();
            $table->string('site_address')->nullable();   
            $table->string('importance_level')->nullable();
            $table->date('start_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('no_of_days')->default(0);
            $table->integer('total_days')->default(0);
            $table->date('pick_up_date')->nullable();
            $table->enum('status', ['0','1']);
            $table->enum('job_status', ['Assigned','On_Going','Completed','Un_done']);
            $table->enum('type', ['task','pickup']);
            $table->string('notes')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
        });

        Schema::table('assign_jobs', function (Blueprint $table) {
            $table->foreign('estimate_id')->references('estimate_id')->on('estimate')->onDelete('cascade');
            $table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assign_jobs');
    }
}
