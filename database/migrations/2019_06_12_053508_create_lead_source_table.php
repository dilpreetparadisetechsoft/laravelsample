<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadSourceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_source', function (Blueprint $table) {
            $table->bigIncrements('lead_id');
             $table->bigInteger('comp_id')->unsigned()->index();
            $table->string('name');
            $table->enum('status', ['0','1'])->comment('0 deactive or 1 active');
            $table->integer('created_by');
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
        Schema::dropIfExists('lead_source');
    }
}
