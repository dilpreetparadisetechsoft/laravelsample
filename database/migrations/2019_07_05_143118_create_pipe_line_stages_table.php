<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePipeLineStagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pipe_line_stages', function (Blueprint $table) {
            $table->bigIncrements('pip_id');
            $table->bigInteger('comp_id')->unsigned()->index(); 
            $table->string('pip_sno');
            $table->string('pip_name');
            $table->enum('pip_status',['0','1']);
            $table->integer('created_by');
            $table->integer('updated_by');            
            $table->timestamps();
        });
        Schema::table('pipe_line_stages', function (Blueprint $table) {
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
        Schema::dropIfExists('pipe_line_stages');
    }
}
