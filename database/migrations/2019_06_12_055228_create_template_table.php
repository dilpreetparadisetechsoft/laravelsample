<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template', function (Blueprint $table) {
            $table->bigIncrements('temp_id');
            $table->bigInteger('comp_id')->unsigned()->index();
            $table->string('temp_sno');
            $table->string('temp_name');
            $table->string('temp_subject')->nullable();
            $table->text('temp_content');
            $table->enum('temp_type', ['invoice','estimate']);
            $table->enum('temp_status', ['0','1'])->comment('0 deactive or 1 active');
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
        Schema::dropIfExists('template');
    }
}
