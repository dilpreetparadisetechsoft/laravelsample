<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->bigIncrements('work_order_id');
            $table->bigInteger('comp_id')->unsigned()->index();
            $table->bigInteger('estimate_id')->unsigned()->index();
            $table->bigInteger('customer_id')->unsigned()->index();
            $table->bigInteger('user_id')->unsigned()->index();
            $table->string('representative_id')->nullable();
            $table->string('wrk_sno')->nullable();
            $table->text('desc_of_job')->nullable();
            $table->date('preferred_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->integer('no_of_technicians');
            $table->string('equipment_requested')->nullable();
            $table->integer('no_of_days');
            $table->integer('duration_of_equipment');
            $table->float('total_amount', 10,2)->default(0);
            $table->text('note')->nullable();
            $table->enum('wrk_status', ['0','1']);
            $table->string('pdf_name')->nullable();
            $table->date('schedule_date')->nullable();
            $table->string('notes')->nullable();
            $table->date('schedule_on')->nullable();
            $table->integer('total_days')->default(0);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
        });
        Schema::table('work_orders', function (Blueprint $table) {
            $table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
            $table->foreign('estimate_id')->references('estimate_id')->on('estimate')->onDelete('cascade');
            $table->foreign('customer_id')->references('customer_id')->on('customer')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_orders');
    }
}
