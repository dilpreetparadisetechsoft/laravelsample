<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChargeCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charge_code', function (Blueprint $table) {
            $table->bigIncrements('chg_code_id');
            $table->bigInteger('comp_id')->unsigned()->index();
            $table->string('chg_code_sno');
            $table->string('chg_code_name');
            $table->string('chg_code');
            $table->text('description')->nullable();
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->string('unit_of_measurement')->nullable();
            $table->decimal('our_cost', 10, 2)->default(0);
            $table->string('gl_code')->nullable();
            $table->string('count_in_wo')->nullable();
            $table->enum('chg_code_status', ['0','1'])->comment('0 deactive or 1 active');
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
        Schema::dropIfExists('charge_code');
    }
}
