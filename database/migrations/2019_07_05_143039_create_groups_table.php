<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->bigIncrements('group_id');
            $table->bigInteger('comp_id')->unsigned()->index(); 
            $table->string('group_sno');
            $table->string('group_name');
            $table->enum('group_status',['0','1']);
            $table->integer('created_by');
            $table->integer('updated_by');            
            $table->timestamps();
        });
        Schema::table('groups', function (Blueprint $table) {
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
        Schema::dropIfExists('groups');
    }
}
