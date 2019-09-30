<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkOrderEquipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_order_equipments', function (Blueprint $table) {
            $table->bigIncrements('work_order_eq_id');
            $table->bigInteger('work_order_id')->unsigned()->index();
            $table->bigInteger('eq_id')->unsigned()->index();
            $table->integer('no_of_days')->default(0);
            $table->integer('no_of_quantities')->default(0);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
        });
        Schema::table('work_order_equipments', function (Blueprint $table) {
            $table->foreign('work_order_id')->references('work_order_id')->on('work_orders')->onDelete('cascade');
            $table->foreign('eq_id')->references('eq_id')->on('equipment')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_order_equipments');
    }
}
