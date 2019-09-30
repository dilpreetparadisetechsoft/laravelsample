<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_template', function (Blueprint $table) {
            $table->bigIncrements('email_temp_id');
            $table->bigInteger('comp_id')->unsigned()->index();
            $table->string('email_temp_sno');
            $table->string('email_temp_name');
            $table->string('email_temp_subject')->nullable();
            $table->text('email_temp_message');
            $table->string('email_place_holder')->nullable();
            $table->enum('email_temp_status', ['0','1']);
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
        Schema::dropIfExists('email_template');
    }
}
