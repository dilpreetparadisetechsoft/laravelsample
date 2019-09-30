<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer', function (Blueprint $table) {
            $table->bigIncrements('customer_id');
            $table->bigInteger('comp_id')->unsigned()->index();
			$table->bigInteger('branch_id')->unsigned()->index();
            $table->string('customer_sno');
            $table->string('customer_name');
            $table->string('customer_address')->nullable();
            $table->string('customer_postal_code')->nullable();
            $table->string('email');
            $table->string('phone', 15);
            $table->string('mobile', 15)->nullable();
            $table->bigInteger('loc_id')->unsigned()->index();
            $table->integer('build_id')->nullable();
            $table->bigInteger('status_id')->unsigned()->index();
            $table->string('language')->nullable();
            $table->string('profile')->nullable();
			$table->bigInteger('serv_id')->unsigned()->index();
			$table->bigInteger('lead_id')->unsigned()->index();
            $table->enum('cust_status', ['0','1'])->comment('0 deactive or 1 active');
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
        Schema::dropIfExists('customer');
    }
}
