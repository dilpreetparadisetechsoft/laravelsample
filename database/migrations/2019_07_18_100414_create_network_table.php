<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNetworkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('networks', function (Blueprint $table) {
            $table->bigIncrements('network_id');
            $table->bigInteger('occ_id')->unsigned()->index(); 
            $table->bigInteger('branch_id')->unsigned()->index(); 
            $table->bigInteger('pip_id')->unsigned()->index(); 
            $table->bigInteger('comp_id')->unsigned()->index(); 
            $table->bigInteger('user_id')->unsigned()->index(); 
            $table->bigInteger('group_id')->unsigned()->index(); 
            $table->bigInteger('int_id')->unsigned()->index(); 
            $table->string('network_sno');
            $table->string('comp_name');
            $table->string('address');
            $table->string('postal_code');
            $table->string('language')->nullable();
            $table->string('website');
            $table->string('email');
            $table->string('phone');
            $table->text('contact_persons');
            $table->enum('network_status', ['1','0']);
            $table->integer('created_by');
            $table->integer('updated_by');            
            $table->timestamps();
        });
        Schema::table('networks', function (Blueprint $table) {
            $table->foreign('occ_id')->references('occ_id')->on('occupations')->onDelete('cascade');
            $table->foreign('branch_id')->references('branch_id')->on('branch')->onDelete('cascade');
            $table->foreign('pip_id')->references('pip_id')->on('pipe_line_stages')->onDelete('cascade');
            $table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('group_id')->references('group_id')->on('groups')->onDelete('cascade');
            $table->foreign('int_id')->references('int_id')->on('interests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('networks');
    }
}
