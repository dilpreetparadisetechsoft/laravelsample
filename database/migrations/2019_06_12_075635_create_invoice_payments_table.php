<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->bigIncrements('payment_id');
			$table->bigInteger('invoice_id')->unsigned()->index();
            $table->bigInteger('comp_id')->unsigned()->index();
			$table->bigInteger('estimate_id')->unsigned()->index();
            $table->decimal('invoice_amount', 10, 2)->default(0);
            $table->enum('pay_type', ['credit','debit']);
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->enum('pay_status', ['0','1'])->comment('0 deactive or 1 active');
            $table->date('pay_date');
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
        Schema::dropIfExists('invoice_payments');
    }
}
