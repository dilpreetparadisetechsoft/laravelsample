<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkScopeTitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_scope_titles', function (Blueprint $table) {
            $table->bigIncrements('wrk_scp_id');
            $table->bigInteger('comp_id')->unsigned()->index(); 
            $table->string('wrk_scp_sno');
            $table->string('wrk_scp_name');
            $table->enum('wrk_scp_status',['0','1']);
            $table->integer('created_by');
            $table->integer('updated_by');            
            $table->timestamps();
        });
        Schema::table('work_scope_titles', function (Blueprint $table) {
            $table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
        });
        Schema::table('work_scope_recommendations', function (Blueprint $table) {
            $table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
            $table->foreign('wrk_scp_id')->references('wrk_scp_id')->on('work_scope_titles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_scope_titles');
    }
}
