<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssignTaskCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assign_task_comments', function (Blueprint $table) {
            $table->bigIncrements('task_comment_id');
            $table->bigInteger('task_id')->unsigned()->index(); 
            $table->bigInteger('user_id')->unsigned()->index();
            $table->string('tags')->nullable();
            $table->text('comments')->nullable();
            $table->string('file')->nullable();
            $table->string('created_by')->default(0);
            $table->timestamps();
        });
        Schema::table('assign_task_comments', function (Blueprint $table) {
            $table->foreign('task_id')->references('task_id')->on('assign_task')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assign_task_comments');
    }
}
