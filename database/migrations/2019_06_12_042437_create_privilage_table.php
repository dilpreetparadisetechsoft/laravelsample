<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrivilageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('privilage', function (Blueprint $table) {
            $table->bigIncrements('privilage_id');
			$table->bigInteger('module_id')->unsigned()->index();
            $table->string('branch_ids')->nullable();
            $table->enum('add', ['0','1']);
            $table->enum('edit', ['0','1']);
            $table->enum('view', ['0','1']);
            $table->enum('delete', ['0','1']);
            $table->string('type','30')->default('default');
			$table->bigInteger('user_id')->unsigned()->index();
            $table->integer('created_by');
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
        Schema::dropIfExists('privilage');
    }
}
