<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimate_item', function (Blueprint $table) {
            $table->bigIncrements('estimate_item_id');
            $table->bigInteger('estimate_id')->unsigned()->index();
            $table->bigInteger('chg_code_id')->unsigned()->index();
            $table->bigInteger('comp_id')->unsigned()->index();
            $table->text('desc')->nullable();
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->string('unit_of_measurement')->nullable();
            $table->integer('days')->nullable();
            $table->text('remarks')->nullable();
            $table->string('uom')->nullable();
            $table->decimal('our_cost', 10, 2)->default(0);
            $table->decimal('total_charge_code', 10, 2)->default(0);
            $table->decimal('total_our_cost', 10, 2)->default(0);
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
        Schema::dropIfExists('estimate_item');
    }
}
