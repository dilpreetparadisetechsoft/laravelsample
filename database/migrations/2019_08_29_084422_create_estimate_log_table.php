<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimate_log', function (Blueprint $table) {
            $table->bigIncrements('estimate_log_id');
			$table->bigInteger('estimate_id');
            $table->bigInteger('customer_id');
            $table->bigInteger('user_id');
            $table->bigInteger('comp_id');
            $table->bigInteger('serv_id');
            $table->bigInteger('status_id');
            $table->integer('tax_id')->nullable();
            $table->string('estimate_sno')->nullable();
            $table->string('area')->nullable();
            $table->string('address')->nullable();
            $table->integer('invoice')->default(0);
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('our_cost_value', 10, 2)->default(0);
            $table->decimal('profit', 10, 2)->default(0);
            $table->decimal('profit_val', 10, 2)->default(0);
            $table->decimal('overhead', 10, 2)->default(0);
            $table->decimal('overhead_val', 10, 2)->default(0);
            $table->integer('profit_overhead')->nullable();
            $table->integer('cog_val')->default(0);
            $table->string('tax')->nullable();
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('sub_total', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->integer('complete_val')->default(0);
            $table->string('note')->nullable();
            $table->string('line_item')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('success')->nullable();
            $table->string('expected_collection')->nullable();
            $table->enum('insurance', ['yes','no'])->default('no');
            $table->integer('ins_comp_id')->nullable();
            $table->enum('is_assigned', ['0','1'])->comment('0 deactive or 1 active');
            $table->enum('estimate_status', ['0','1'])->comment('0 deactive or 1 active');
            $table->string('po_claim')->nullable();
            $table->string('building_type')->nullable();
            $table->text('estimate_items_log')->nullable();
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
        Schema::dropIfExists('estimate_log');
    }
}
