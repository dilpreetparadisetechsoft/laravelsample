<?php
namespace App\Http\Middleware\Api;

use Closure;
use JWTAuth;
use DB;
use Session;
use Redirect;
use App\User;
use Illuminate\Support\Facades\Route;

class AuthToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if(!$user = JWTAuth::parseToken()->authenticate())
            {
                return Response()->json(['status'=>false, 'message' => 'Your Session has been expired, Please login again.', 'response' => 'login'], 200);
            }else{                
                return $next($request); 
            }
        } catch(\Tymon\JWTAuth\Exceptions\JWTException $e){
            return Response()->json(['status'=>false, 'message' => 'Your Session has been expired, Please login again.', 'response' => 'login'], 200);
        }
        return $next($request);
    }
}
