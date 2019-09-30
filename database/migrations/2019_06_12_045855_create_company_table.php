<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company', function (Blueprint $table) {
            $table->bigIncrements('comp_id');
            $table->string('comp_code');
            $table->string('comp_name');
            $table->string('comp_logo')->nullable();
            $table->text('tag_line')->nullable();
            $table->string('comp_address')->nullable();
            $table->string('comp_gst_no')->nullable();
            $table->string('comp_pst_no')->nullable();
            $table->string('comp_qst_no')->nullable();
            $table->enum('comp_status', ['-1','0','1','2']);
            $table->string('finance_mail')->nullable();
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
        Schema::dropIfExists('company');
    }
}
