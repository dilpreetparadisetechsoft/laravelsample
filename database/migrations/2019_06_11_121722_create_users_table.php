<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->string('uuid');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('password');
            $table->string('image')->nullable();
			$table->bigInteger('dep_id')->nullable()->unsigned()->index();
            $table->string('verify_code')->nullable();
            $table->integer('active');
			$table->bigInteger('role_id')->unsigned()->index();
            $table->integer('report_to')->nullable();
			$table->bigInteger('comp_id')->unsigned()->index();
            $table->integer('branch_id')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
