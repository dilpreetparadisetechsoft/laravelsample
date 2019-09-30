<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_docs', function (Blueprint $table) {
            $table->bigIncrements('cust_doc_id');
            $table->bigInteger('customer_id')->unsigned()->index();
            $table->bigInteger('comp_id')->unsigned()->index();
            $table->bigInteger('doc_type_id')->unsigned()->index(); 
            $table->string('cust_doc_sno');
            $table->text('description');
            $table->text('file_name');
            $table->enum('status', ['1','0']);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
        });
        Schema::table('customer_docs', function (Blueprint $table) {
            $table->foreign('customer_id')->references('customer_id')->on('customer')->onDelete('cascade');
            $table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
            $table->foreign('doc_type_id')->references('doc_type_id')->on('document_type')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_docs');
    }
}
