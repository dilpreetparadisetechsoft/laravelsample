<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('purchase_order', function (Blueprint $table) {
            $table->bigIncrements('purchase_order_id');
            $table->bigInteger('estimate_id')->unsigned()->index(); 
            $table->bigInteger('customer_id')->unsigned()->index(); 
            $table->bigInteger('network_id')->unsigned()->index(); 
            $table->bigInteger('comp_id')->unsigned()->index(); 
            $table->string('purchase_order_no');
            $table->text('invoice_to')->nullable();
            $table->text('equipment_req')->nullable();
            $table->integer('terms_of_pay_insurance_files')->nullable();
            $table->text('desc_of_job')->nullable();
            $table->integer('no_of_technicians')->nullable();
            $table->integer('no_of_days')->nullable();
            $table->string('terms_of_payment')->nullable();
            $table->integer('duration_of_project')->nullable();
            $table->date('date');
            $table->date('expected_start_date');
            $table->date('expected_end_date');
            $table->decimal('total_amount', 10, 2);
            $table->integer('time_material')->nullable();
            $table->integer('confirmation_of_ack');
            $table->enum('purchase_order_status', ['1','0']);
            $table->string('address');
            $table->string('pdf_name')->nullable();
            $table->enum('po_approved', ['1','0']);
            $table->integer('res_id')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
        });

        Schema::table('purchase_order', function (Blueprint $table) {
            $table->foreign('estimate_id')->references('estimate_id')->on('estimate')->onDelete('cascade');
            $table->foreign('customer_id')->references('customer_id')->on('customer')->onDelete('cascade');
            $table->foreign('network_id')->references('network_id')->on('networks')->onDelete('cascade');
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
        Schema::dropIfExists('purchase_order');
    }
}
