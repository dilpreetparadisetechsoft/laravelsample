<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerOutSourceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_out_source', function (Blueprint $table) {
            $table->bigIncrements('cust_out_src_id');
            $table->bigInteger('network_id')->unsigned()->index();
            $table->bigInteger('customer_id')->unsigned()->index();
            $table->bigInteger('comp_id')->unsigned()->index();
            $table->string('cust_out_src_sno');
            $table->text('note');
            $table->integer('send_email');
            $table->enum('status', ['1','0']);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
        });

        Schema::table('customer_out_source', function (Blueprint $table) {
            $table->foreign('network_id')->references('network_id')->on('networks')->onDelete('cascade');
            $table->foreign('customer_id')->references('customer_id')->on('customer')->onDelete('cascade');
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
        Schema::dropIfExists('customer_out_source');
    }
}
