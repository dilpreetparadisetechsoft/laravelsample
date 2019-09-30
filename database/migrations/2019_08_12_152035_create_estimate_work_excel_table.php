<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateWorkExcelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimate_work_excel', function (Blueprint $table) {
            $table->bigIncrements('work_excel_id');
            $table->bigInteger('work_excel_log_id')->unsigned()->index(); 
            $table->bigInteger('estimate_id')->nullable();
            $table->bigInteger('comp_id')->unsigned()->index(); 
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('rate')->nullable();
            $table->date('start_date')->nullable();
            $table->time('start_time')->nullable();
            $table->date('end_date')->nullable();
            $table->time('end_time')->nullable();
            $table->string('total_hours')->nullable();
            $table->timestamps();
        });

        Schema::table('estimate_work_excel', function (Blueprint $table) {
            $table->foreign('work_excel_log_id')->references('work_excel_log_id')->on('estimate_work_excel_logs')->onDelete('cascade');
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
        Schema::dropIfExists('estimate_work_excel');
    }
}
