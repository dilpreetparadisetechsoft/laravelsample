<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kit', function (Blueprint $table) {
            $table->bigIncrements('kit_id');
            $table->bigInteger('comp_id')->unsigned()->index();
            $table->string('kit_sno');
            $table->string('kit_name');
			$table->string('chg_code_id')->nullable();
            $table->enum('kit_status', ['0','1'])->comment('0 deactive or 1 active');
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
        Schema::dropIfExists('kit');
    }
}
