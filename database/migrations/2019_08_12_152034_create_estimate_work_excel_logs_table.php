<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateWorkExcelLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimate_work_excel_logs', function (Blueprint $table) {
            $table->bigIncrements('work_excel_log_id');
            $table->bigInteger('user_id')->unsigned()->index(); 
            $table->bigInteger('branch_id')->unsigned()->index(); 
            $table->bigInteger('comp_id')->unsigned()->index(); 
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('excel_name', 300)->nullable();
            $table->timestamps();
        });
        
        Schema::table('estimate_work_excel_logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('branch_id')->references('branch_id')->on('branch')->onDelete('cascade');
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
        Schema::dropIfExists('estimate_work_excel_logs');
    }
}
