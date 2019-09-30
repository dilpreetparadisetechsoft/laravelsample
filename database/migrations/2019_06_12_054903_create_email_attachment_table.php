<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailAttachmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_attachment', function (Blueprint $table) {
            $table->bigIncrements('email_attach_id');
            $table->bigInteger('comp_id')->unsigned()->index();
            $table->string('email_attach_sno');
            $table->string('email_attach_name');
            $table->string('email_attach_type');
            $table->string('email_attach_file');
            $table->enum('email_attach_status', ['0','1']);
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
        Schema::dropIfExists('email_attachment');
    }
}
