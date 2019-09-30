<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInterestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interests', function (Blueprint $table) {
            $table->bigIncrements('int_id');
            $table->bigInteger('comp_id')->unsigned()->index(); 
            $table->string('int_sno');
            $table->string('int_name');
            $table->enum('int_status',['0','1']);
            $table->integer('created_by');
            $table->integer('updated_by');            
            $table->timestamps();
        });        
        Schema::table('interests', function (Blueprint $table) {
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
        Schema::dropIfExists('interests');
    }
}
