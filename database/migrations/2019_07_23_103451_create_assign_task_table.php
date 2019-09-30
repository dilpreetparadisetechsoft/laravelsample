<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssignTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assign_task', function (Blueprint $table) {
            $table->bigIncrements('task_id');
            $table->bigInteger('estimate_id')->unsigned()->index();
            $table->bigInteger('customer_id')->nullable();
            $table->bigInteger('comp_id')->unsigned()->index();
            $table->string('task_sno');
            $table->string('title');
            $table->text('representative');
            $table->date('start_date');
            $table->date('due_date');
            $table->enum('level',['Immediate','Important','Urgent','Moderate As Per Due Date']);
            $table->text('notes');
            $table->integer('inspection');
            $table->integer('customer_check');
            $table->string('unit');
            $table->string('guest_email');
            $table->enum('status', ['Waiting','Completed','WIP']);
            $table->enum('type', ['pickup','task']);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
        });

        Schema::table('assign_task', function (Blueprint $table) {
            $table->foreign('estimate_id')->references('estimate_id')->on('estimate')->onDelete('cascade');
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
        Schema::dropIfExists('assign_task');
    }
}
