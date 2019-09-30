<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\UserDetail;
use App\Department;
use App\Branch;
use App\Company;
use App\Roles;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class RepresentativeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $param = $request->input('param');
        $orderKey = (!empty($request->input('orderKey'))?$request->input('orderKey'):'estimate_id');
        $orderBy = (!empty($request->input('orderBy'))?$request->input('orderBy'):'DESC');
        $size = ($request->input('size')?$request->input('size'):dataPerPage());
        $currentUser = getApiCurrentUser();
        $users = User::leftJoin('roles','roles.role_id', 'users.role_id')
                        ->where(function ($query) use($param, $currentUser){
                            $query->where('users.comp_id', $currentUser->comp_id);
                            if (!empty($param)) {
                                $query->where('first_name', 'LIKE', '%'.$param.'%');
                            }
                        })
                        ->select('users.*','roles.role')
                        ->orderBy($orderKey, $orderBy)
                        ->paginate($size);
        return response()->json(['status'=>true, 'message'=>'All Representative', 'response'=>compact('users')], 200);
    } 

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function storeRules($request)
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|numeric|unique:users',
            'dep_id' => [
                'required',
                Rule::exists('department')->where(function ($query) use($request) {
                    $query->where('dep_id', $request->input('dep_id'));
                }),
            ],
            'active' => 'required|in:0,1',
            'role_id' => [
                'required',
                Rule::exists('roles')->where(function ($query) use($request) {
                    $query->where('role_id', $request->input('role_id'));
                }),
            ],           
        ];
        if (!empty($request->input('branch_id'))) {
            $rules['branch_id'] = [
                Rule::exists('branch')->where(function ($query) use($request) {
                    $query->where('branch_id', $request->input('branch_id'));
                }),
            ];
        }
        return $rules;
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        if(!filter_var($request->input('email'), FILTER_VALIDATE_EMAIL))
        {
            return Response()->json(['status'=>'error', 'message' => 'The email must be a valid email address.', 'response' => []], 200);
        }
        $activation_key = sha1(mt_rand(10000,99999).time().$request->input('email'));
        $uuid = createUuid($request->input('first_name').date('YmdHis'));
        $uuid = $uuid->string;
        $password = randomPassword();
        $currentUser = getApiCurrentUser();
        User::create([
            'uuid' => $uuid,
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'password' => Hash::make($password),
            'dep_id' => $request->input('dep_id'),
            'verify_code' => $activation_key,
            'active' => $request->input('active'),
            'role_id' => $request->input('role_id'),
            'report_to' => $request->input('report_to'),
            'comp_id' => $currentUser->comp_id,
            'branch_id' => $request->input('branch_id'),
            'created_at' => new DateTime,
            'updated_at'=> new DateTime
        ]);
        return response()->json(['status'=>true, 'message'=>'User Created', 'response'=>[]], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        if (empty($user)) {
            return Response()->json(['status'=>false, 'message' => 'User is not exists', 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $prefix = DB::getTablePrefix();
        $departments = Department::where('created_by', $currentUser->comp_id)->get();
        $branches = Branch::where('comp_id', $currentUser->user_id)->get();
        $roles = Roles::where('created_by', $currentUser->user_id)->get();
        $role = Roles::whereRaw($prefix.'roles.role_id = '.$prefix.'users.role_id')->select('role')->limit(1);
        $respersantives = User::where('comp_id', $currentUser->comp_id)->where('user_id','!=', $currentUser->user_id)->select('user_id', 'first_name', DB::raw("({$role->toSql()}) as role_name"))->get();
        return response()->json(['status'=>true, 'message'=>'User show', 'response'=>compact('user','companies','departments','branches','roles','respersantives')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        if (empty($user)) {
            return Response()->json(['status'=>false, 'message' => 'User is not exists', 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $prefix = DB::getTablePrefix();
        $departments = Department::where('created_by', $currentUser->comp_id)->get();
        $branches = Branch::where('comp_id', $currentUser->user_id)->get();
        $roles = Roles::where('created_by', $currentUser->user_id)->get();
        $role = Roles::whereRaw($prefix.'roles.role_id = '.$prefix.'users.role_id')->select('role')->limit(1);
        $respersantives = User::where('comp_id', $currentUser->comp_id)->where('user_id','!=', $currentUser->user_id)->select('user_id', 'first_name', DB::raw("({$role->toSql()}) as role_name"))->get();
        return response()->json(['status'=>true, 'message'=>'User show', 'response'=>compact('user','companies','departments','branches','roles','respersantives')], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public static function updateRules($request)
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dep_id' => [
                'required',
                Rule::exists('department')->where(function ($query) use($request) {
                    $query->where('dep_id', $request->input('dep_id'));
                }),
            ],
            'active' => 'required|in:0,1',
            'role_id' => [
                'required',
                Rule::exists('roles')->where(function ($query) use($request) {
                    $query->where('role_id', $request->input('role_id'));
                }),
            ],
        ];
        if (!empty($request->input('branch_id'))) {
            $rules['branch_id'] = [
                Rule::exists('branch')->where(function ($query) use($request) {
                    $query->where('branch_id', $request->input('branch_id'));
                }),
            ];
        }
        return $rules;
    }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), self::updateRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }

        $user = User::find($id);
        if (empty($user)) {
            return Response()->json(['status'=>false, 'message' => 'User is not exists', 'response' => []], 200);
        }

        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        if ($request->input('password')) {
            $user->phone = Hash::make($request->input('password'));
        }
        $user->dep_id = $request->input('dep_id');
        $user->active = $request->input('active');
        $user->role_id = $request->input('role_id');
        $user->report_to = $request->input('report_to');
        $user->branch_id = $request->input('branch_id');
        $user->updated_at = new DateTime;
        $user->save();

        return response()->json(['status'=>true, 'message'=>'User Updated', 'response'=>compact('user')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (empty($user)) {
            return Response()->json(['status'=>false, 'message' => 'User is not exists', 'response' => []], 200);
        }
        if ($user->role_id == 1) {
            return Response()->json(['status'=>false, 'message' => 'You are not authorize to delete admin', 'response' => []], 200);
        }
        $user->delete();
        return response()->json(['status'=>true, 'message'=>'User Deleted', 'response'=>compact('user')], 200);
    }

}
