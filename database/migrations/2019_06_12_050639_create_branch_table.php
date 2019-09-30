<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBranchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branch', function (Blueprint $table) {
            $table->bigIncrements('branch_id');
            $table->string('branch_sno');
            $table->bigInteger('comp_id')->unsigned()->index();
            $table->string('branch_name');
            $table->string('reg_id')->nullable()->comment('branch registartion ID');
            $table->enum('branch_status',['0','1'])->comment('0 deactive or 1 active');
			$table->bigInteger('loc_id')->unsigned()->index();
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
        Schema::dropIfExists('branch');
    }
}
