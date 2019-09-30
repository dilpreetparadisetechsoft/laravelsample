<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Session, Redirect;
use App\User;
use Validator;
use JWTFactory;
use JWTAuth;
use App\JobStatus;
use App\Settings;
class LoginController extends Controller
{
    public function accessLogin(Request $request)
    {
        $response = User::login($request);
        if ($response['error'] == true) {
            return Response()->json(['status'=>'error', 'message' => $response['message'], 'response' => []], 200);
        }else{
            return Response()->json(['status'=>'success', 'message'=>$response['message'], 'response' => ['token' => $response['token'], 'user' => $response['user']]], 200);
        }    

    }
    public function logout(Request $request)
    {
        $authorization = $request->header('authorization');
        JWTAuth::invalidate($authorization);
        Session::forget('token');
        return Response()->json(['status'=>true, 'message'=>'Logout successfully', 'response' => []], 200);
    }
}
