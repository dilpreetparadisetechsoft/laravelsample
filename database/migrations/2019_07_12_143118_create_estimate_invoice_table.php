<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimate_invoice', function (Blueprint $table) {
            $table->bigIncrements('estimate_invoice_id');
            $table->bigInteger('comp_id')->unsigned()->index(); 
            $table->bigInteger('estimate_id')->unsigned()->index(); 
            $table->bigInteger('customer_id')->unsigned()->index(); 
            $table->string('invoice_no')->nullable();
            $table->string('invoice_file_name')->nullable();
            $table->decimal('invoice_amount', 10, 2);
            $table->date('invoice_date')->nullable();
            $table->enum('invoice_status',['Paid', 'Unpaid', 'Email']);
            $table->datetime('paid_on')->nullable();
            $table->text('invoice_note')->nullable();
            $table->text('payment_note')->nullable();
            $table->enum('payment_method', ['CC','Cash','Cheque','Online'])->nullable();
            $table->enum('payment_status', ['Paid','Bad Debt','Discount','Unpaid','Partially'])->nullable();
            $table->decimal('collection', 10, 2)->default(0);
            $table->decimal('bad_debts_amount', 10, 2)->default(0);
            $table->integer('discount')->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total_collection', 10, 2)->default(0);
            $table->string('pdf_name')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by');            
            $table->timestamps();
        });
        Schema::table('estimate_invoice', function (Blueprint $table) {
            $table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
            $table->foreign('estimate_id')->references('estimate_id')->on('estimate')->onDelete('cascade');
            $table->foreign('customer_id')->references('customer_id')->on('customer')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estimate_invoice');
    }
}
