<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKpiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kpi', function (Blueprint $table) {
            $table->bigIncrements('kpi_id');
            $table->bigInteger('user_id')->unsigned()->index();
			$table->bigInteger('comp_id')->unsigned()->index();
            $table->string('kpi_sno')->nullable();
            $table->string('kpi_target')->nullable();
            $table->string('kpi_indicator')->nullable();
            $table->text('kpi_month')->nullable();
            $table->string('kpi_annual')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('kpi_desc')->nullable();
            $table->string('kpi_quarterly')->nullable();
            $table->enum('status', ['0','1'])->comment('0 deactive or 1 active');
            $table->integer('created_by');
            $table->integer('updated_by');
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
        Schema::dropIfExists('kpi');
    }
}
