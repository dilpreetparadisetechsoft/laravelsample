<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkScopeRecommendationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_scope_recommendations', function (Blueprint $table) {
            $table->bigIncrements('wrk_scp_rec_id');
            $table->bigInteger('wrk_scp_id')->unsigned()->index();
            $table->bigInteger('comp_id')->unsigned()->index(); 
            $table->string('wrk_scp_rec_sno');
            $table->text('wrk_scp_rec_name');
            $table->enum('wrk_scp_rec_status', ['0','1']);
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
        Schema::dropIfExists('work_scope_recommendations');
    }
}
