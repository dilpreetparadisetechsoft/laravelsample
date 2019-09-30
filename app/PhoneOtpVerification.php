<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhoneOtpVerification extends Model
{
    protected $primaryKey = 'otp_id';

    protected $fillable = [
    	'phone', 
        'otp_code', 
        'time', 
        'otp_for', 
        'otp_status', 
        'created_at', 
        'updated_at'
    ];
}
