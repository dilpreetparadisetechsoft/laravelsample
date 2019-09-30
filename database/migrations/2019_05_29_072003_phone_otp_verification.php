<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhoneOtpVerification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phone_otp_verification', function (Blueprint $table) {
            $table->bigIncrements('otp_id');
            $table->string('phone',20);
            $table->string('otp_code', 20);
            $table->time('time');
            $table->string('otp_for',20);
            $table->string('otp_status',20);
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
        Schema::dropIfExists('phone_otp_verification');
    }
}
