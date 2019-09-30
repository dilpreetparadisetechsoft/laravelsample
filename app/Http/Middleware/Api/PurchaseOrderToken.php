<?php
namespace App\Http\Middleware\Api;

use Closure;
use JWTAuth;
use DB;
use Session;
use Redirect;
use App\User;
use Illuminate\Support\Facades\Route;

class PurchaseOrderToken
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
        $index = 'purchase_order';
        try {
            if(!$user = JWTAuth::parseToken()->authenticate())
            {
                return Response()->json(['status'=>false, 'message' => 'Your Session has been expired, Please login again.', 'response' => 'login'], 200);
            }else{
                $user = getUserPermission($user);
                $modules = array_keys($user->modules);
                if (empty($modules)) {
                    return Response()->json(['status'=>false, 'message' => 'Your Session has been expired, Please login again.', 'response' => 'login'], 200);
                }
                if (!isset($user->modules[$index])) {
                    return Response()->json(['status'=>false, 'message' => 'Your Session has been expired, Please login again.', 'response' => 'login'], 200);
                }
                if (!in_array($index, $modules)) {
                    return Response()->json(['status'=>false, 'message' => 'Your are not authorize to access this url, Please contact with adminstrator.', 'response' => ''], 200);
                }

                $routeName = Route::getCurrentRoute()->getName();
                $actions = $user->modules[$index];
                if ($index.'.index' == $routeName && !in_array('view', $actions)) {
                    return Response()->json(['status'=>false, 'message' => 'Your are not authorize to access this url, Please contact with adminstrator.', 'response' => ''], 200);   
                }elseif ($index.'.store' == $routeName && !in_array('add', $actions)) {
                    return Response()->json(['status'=>false, 'message' => 'Your are not authorize to access this url, Please contact with adminstrator.', 'response' => ''], 200);   
                }elseif (($index.'.show' == $routeName || $index.'.update' == $routeName) && !in_array('edit', $actions)) {
                    return Response()->json(['status'=>false, 'message' => 'Your are not authorize to access this url, Please contact with adminstrator.', 'response' => ''], 200);   
                }elseif ($index.'.destroy' == $routeName && !in_array('delete', $actions)) {
                    return Response()->json(['status'=>false, 'message' => 'Your are not authorize to access this url, Please contact with adminstrator.', 'response' => ''], 200);   
                }
                return $next($request); 
            }
        } catch(\Tymon\JWTAuth\Exceptions\JWTException $e){
            return Response()->json(['status'=>false, 'message' => 'Your Session has been expired, Please login again.', 'response' => 'login'], 200);
        }
        return $next($request);
    }
}