<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerContactHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_contact_history', function (Blueprint $table) {
            $table->bigIncrements('cust_cont_history_id');
            $table->bigInteger('customer_id')->unsigned()->index();
            $table->bigInteger('comp_id')->unsigned()->index();
            $table->string('cust_cont_history_sno');
            $table->string('communication_mode');
            $table->text('note');
            $table->date('contact_date');
            $table->time('contact_time');
            $table->enum('status', ['1','0']);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
        });

        Schema::table('customer_contact_history', function (Blueprint $table) {
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
        Schema::dropIfExists('customer_contact_history');
    }
}
