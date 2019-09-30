<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOccupationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('occupations', function (Blueprint $table) {
            $table->bigIncrements('occ_id');
            $table->bigInteger('comp_id')->unsigned()->index(); 
            $table->string('occ_sno');
            $table->string('occ_name');
            $table->enum('occ_status',['0','1']);
            $table->integer('created_by');
            $table->integer('updated_by');            
            $table->timestamps();
        });
        Schema::table('occupations', function (Blueprint $table) {
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
        Schema::dropIfExists('occupations');
    }
}
