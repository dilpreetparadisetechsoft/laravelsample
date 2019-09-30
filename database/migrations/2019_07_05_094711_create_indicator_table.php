<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndicatorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indicator', function (Blueprint $table) {
            $table->bigIncrements('ind_id');
            $table->bigInteger('comp_id')->unsigned()->index();
            $table->string('ind_sno');
            $table->string('ind_name');
            $table->enum('ind_status',['0','1']);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
        });
        Schema::table('indicator', function (Blueprint $table) {
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
        Schema::dropIfExists('indicator');
    }
}
