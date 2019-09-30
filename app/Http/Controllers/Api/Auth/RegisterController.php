<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator, DateTime, DB, Hash, File, Config, Helpers;
use Session, Redirect;
use Illuminate\Support\Facades\Input;
use App\User;

class RegisterController extends Controller
{
    public function rules(){
        return array(
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|numeric|unique:users',
            'password' => 'required|string|min:8|max:14',
            'role_id' => 'required'
        );
    }
    public function create(Request $request)
    {
        $validation = Validator::make($request->all(), self::rules());
        if($validation->passes()){
            if(!filter_var($request->input('email'), FILTER_VALIDATE_EMAIL))
            {
                return Response()->json(['status'=>'error', 'message' => 'The email must be a valid email address.', 'response' => []], 200);
            }
            $activation_key = sha1(mt_rand(10000,99999).time().$request->input('email'));
            $uuid = createUuid($request->input('name').date('YmdHis'));
            $uuid = $uuid->string;
            User::create([
                'uuid' => $uuid,
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'activation_key' => $activation_key,
                'password' => Hash::make($request->input('password')),
                'role_id' => $request->input('role_id'),
                'active'=> $request->input('active')
            ]);

            //phoneOtpSendVarification($request->input('email'), $request->input('phone'));
            //$htmlmessage = 'Please Click on this link to varify your email.';
            //$htmlmessage .= '<br>Click <a href="'.url('varify/email/link/'.$activation_key).'">Here</a>';
            //SendEmail($request->input('email'),'Varify Email At vsure',$htmlmessage,'');
            return Response()->json(['status'=>'success', 'message' => 'Account Created Successfully, Otp has been sent you on mobile and email.', 'response' => compact('uuid')], 200);

        }else{
            return Response()->json(['status'=>'error', 'message' => $validation->getMessageBag()->first(), 'response' => []], 200);
        }
    }
    public function phoneOtpVarificationSubmit(Request $request)
    {
        $uuid = $request->input('uuid');
        if (empty($uuid)) {
            return Response()->json(['status'=>'error', 'message' => 'uuid is required', 'response' => []], 200);
        }
        $user = User::where('uuid', $uuid)->get()->first();

        if (empty($user)) {
            return Response()->json(['status'=>'error', 'message' => 'user is not exist in our system.', 'response' => []], 200);
        }
        if (empty($request->input('otp_code'))) {
            return Response()->json(['status'=>'error', 'message' => 'Your OTP is invalid', 'response' => []], 200);
        }
        $otpCode = PhoneOtpVerification::where('phone', $user->phone)->where('otp_status', 'sent')->where('otp_code', $request->input('otp_code'))->where('otp_for', 'phone_number')->get()->first();
        if (empty($otpCode)) {
            return Response()->json(['status'=>'error', 'message' => 'Your OTP is invalid', 'response' => []], 200);
        }        
        $otpTime = date('H:i:s',strtotime("+3 minutes", strtotime($otpCode->time)));
        if ($otpTime < date('H:i:s')) {
            PhoneOtpVerification::where('otp_id', $otpCode->otp_id)->update(['otp_status'=>'expired']);
            return Response()->json(['status'=>'error', 'message' => 'Your OTP is expired. Time is more then 3 minut .', 'response' => []], 200);           
        }
        PhoneOtpVerification::where('otp_id', $otpCode->otp_id)->update(['otp_status'=>'verify']);        
        User::where('user_id', $user->user_id)->update(['phone_verified_at' => new DateTime,'status'=>1]);

        return Response()->json(['status'=>'success', 'message'=>'Phone otp verified Successfully', 'response' => []], 200);

    }
    public function phoneOtpSendVarification(Request $request)
    {
        if (empty($request->input('uuid'))) {
            return Response()->json(['status'=>'error', 'message' => 'Something Went Wrong, Please try after sometime with your email id and phone number .', 'response' => []], 200);
        }
        $user = User::where('uuid', $request->input('uuid'))->get()->first();
        if (empty($user->user_id)) {
            return Response()->json(['status'=>'error', 'message' => 'Something Went Wrong, Please try after sometime. Your token is expired .', 'response' => []], 200);
        }
        phoneOtpSendVarification($user->phone, $user->email);

        return Response()->json(['status'=>'success', 'message' => 'Account Created Successfully, Otp has been sent you on mobile and email.', 'response' => []], 200);
    }
}
