<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateInvoiceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimate_invoice_logs', function (Blueprint $table) {
            $table->bigIncrements('invoice_log_id');
            $table->bigInteger('estimate_invoice_id')->unsigned()->index(); 
            $table->enum('action', ['Paid','Bad Debt','Discount','Unpaid','Partially']);
            $table->decimal('amount', 10, 2)->default(0);
            $table->integer('discount')->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total_collection', 10, 2)->default(0);
            $table->integer('created_by');
            $table->integer('updated_by');          
            $table->timestamps();
        });

        Schema::table('estimate_invoice_logs', function (Blueprint $table) {
            $table->foreign('estimate_invoice_id')->references('estimate_invoice_id')->on('estimate_invoice')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estimate_invoice_logs');
    }
}
