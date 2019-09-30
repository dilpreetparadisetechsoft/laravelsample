<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator;
use JWTFactory;
use JWTAuth;
use Illuminate\Support\Facades\Auth;
use Session, Redirect, DB;
use App\LoginLogs;
use App\Roles;
use App\Privilage;
use App\Modules;


class User extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'user_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid','first_name', 'last_name','email', 'phone','password','dep_id','verify_code','active','role_id','report_to','comp_id','branch_id','remember_token','created_at','updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'verify_code','remember_token','updated_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the Roles Branch associated with the user.
     */
    protected function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
    }

    /**
     * Get the userDetail associated with the user.
     */
    protected function userDetail()
    {
        return $this->hasOne('App\UserDetail', 'user_id', 'user_id');
    }

    /**
     * Get the Roles Company associated with the user.
     */
    protected function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }

    /**
     * Get the kpis associated with the user.
     */
    protected function kpis()
    {
        return $this->hasMany('App\Kpi', 'user_id');
    }

    /**
     * Get the Department record associated with the user.
     */
    protected function department()
    {
        return $this->belongsTo('App\Department', 'dep_id');
    }

    /**
     * Get the Roles record associated with the user.
     */
    protected function role()
    {
        return $this->belongsTo('App\Roles', 'role_id');
    }
    /**
     * Get the privilage for the user.
     */
    protected function privilages()
    {
        return $this->hasMany('App\Privilage', 'user_id');
    }
    protected function login($request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:255',
            'password'=> 'required'
        ]);
        if ($validator->fails()) {
            return ['error' => true,'message'=>$validator->getMessageBag()->first(),'token'=>''];
        }
        $credentials = $request->only('email', 'password');
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return ['error' => true,'message'=>'Inavlid Credentials, Incorrect email and password','token'=>''];
            }
        } catch (JWTException $e) {
            return ['error' => true,'message'=>'Failed to create access to login','token'=>''];
        }

        $user = JWTAuth::toUser($token);
        if ($user->active != 1) {
            return ['error' => true,'message'=>'Your account is blocked by administrator.','token'=>''];   
        }
        
        LoginLogs::insert([
            'user_id' => $user->user_id,
            'token' => $token,
            'created_at' => date('Y-m-d h:i:s'),
            'updated_at' => date('Y-m-d h:i:s')
        ]);        
        
        $user->userDetail = UserDetail::where('user_id', $user->user_id)->first();
        $user = getUserPermission($user);
        $user->settings = getSettings($user->comp_id);
        JWTAuth::setToken($token);
        return ['error' => false,'message'=>'Login Successfully','token'=>$token,'user'=>$user];
    }
    protected function authenticateWithEmail($user) {         
        $token = JWTAuth::fromUser($user);

        DB::table('users_last_login')->insert([
            'user_id' => $user->user_id,
            'token' => $token,
            'created_at' => date('Y-m-d h:i:s'),
            'updated_at' => date('Y-m-d h:i:s')
        ]);
        Session::put('token', $token);
        Session::save();
        JWTAuth::setToken($token);
        return $token;
    }
    protected function logout()
    {
        JWTAuth::invalidate(session('token'));
        Session::forget('token');
        return ['error' => false,'message'=>'Logout Successfully','token'=>''];
    }
    protected function forgotPassword($request)
    {
        $user = User::where(function($query) use($request){
                    if (!empty($request->input('user_login'))) {
                        $query->orwhere('user_login', $request->input('user_login'))
                            ->orwhere('email', $request->input('user_login'))
                            ->orwhere('phone', $request->input('user_login'));
                    }
                })
                ->get()
                ->first();
        if (!empty($user)) {

            $activation_key = sha1(mt_rand(10000,99999).time().$user->email);
            $resetPasswordInsert = [];
            $resetPasswordInsert['user_id'] = $user->user_id;
            $resetPasswordInsert['email'] = $user->email;
            $resetPasswordInsert['token'] = $activation_key;
            $resetPasswordInsert['created_at'] = date('Y-m-d h:i:s');
            $resetPasswordInsert['updated_at'] = date('Y-m-d h:i:s');
            DB::table('users_password_reset')->insert($resetPasswordInsert);
            $link = url('auth/reset/forgot/password/'.$activation_key);
            $htmlmessage = 'Please click on this <a href="'.$link.'">Link</a> to reset your password';
            Helper::SendEmail($user->email,'Reset Your password at VsureCFO',$htmlmessage, '');
            Helper::SendSMS($user->phone,$htmlmessage);
            return ['error' => false,'message'=>'To reset your password link send on email'];
            
        }else{
            return ['error' => true,'message'=>'Inavlid Credentials, Incorrect email, user_login Or phone number'];
        }
    }
}
