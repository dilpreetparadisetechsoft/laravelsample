<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('event_id');
            $table->bigInteger('comp_id')->unsigned()->index();
            $table->bigInteger('task_id')->nullable();
            $table->string('event_name');
            $table->date('event_date');
            $table->time('event_start_time');
            $table->time('event_end_time');
            $table->string('event_type')->nullable();
            $table->string('meeting_with');
            $table->string('event_loc');
            $table->string('guests');
            $table->string('note');
            $table->enum('status', ['1','0']);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
        });

        Schema::table('events', function (Blueprint $table) {
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
        Schema::dropIfExists('events');
    }
}
