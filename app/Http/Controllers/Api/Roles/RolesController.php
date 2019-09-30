<?php

namespace App\Http\Controllers\Api\Roles;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Roles;
use Validator, DateTime, Config, Helpers;
class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = getApiCurrentUser();
        $roles = Roles::where('created_by', $currentUser->user_id)->paginate(dataPerPage());
		return Response()->json(['status'=>true, 'message' => 'Get all roles.', 'response' => compact('roles')], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	public static function storeRules()
    {
        return [
            'role' => 'required|string|unique:roles|max:191',
        ];
    }
    public function store(Request $request)
    {
		$validator = Validator::make($request->all(), self::storeRules());
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
		
		$currentUser = getApiCurrentUser();
        $role = new Roles(); 
        $role->role = $request->input('role');
        $role->created_by = $currentUser->user_id;
		$role->created_at = new DateTime;
		$role->updated_at = new DateTime;
		
        $role->save();
        return Response()->json(['status'=>true, 'message' => 'Save roles.', 'response' => compact('role')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($role_id)
    {
        $role = Roles::find($role_id);
		if (empty($role)) {
            return Response()->json(['status'=>false, 'message' => 'Roles is not exists', 'response' => []], 200);
        }
        return Response()->json(['status'=>true, 'message' => 'Role.', 'response' => compact('role')], 200);
    }
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($role_id)
    {
       $role = Roles::find($role_id);
        if (empty($role)) {
            return Response()->json(['status'=>false, 'message' => 'Role is not exists', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Role', 'response'=>compact('role')], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $role_id)
    {
		$validator = Validator::make($request->all(), self::storeRules());
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $role = Roles::find($role_id);
        if (empty($role)) {
            return Response()->json(['status'=>false, 'message' => 'Role is not exists', 'response' => []], 200);
        }
		
        $role->role = $request->input('role');
		$role->updated_at = new DateTime;
        $role->save();
        return Response()->json(['status'=>true, 'message' => 'Update Role.', 'response' => compact('role')], 200);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($role_id)
    {
        $role = Roles::find($role_id);
        if (empty($role)) {
            return Response()->json(['status'=>false, 'message' => 'Role is not exists', 'response' => []], 200);
        }
        $role->delete();
        return response()->json(['status'=>true, 'message'=>'Deleted', 'response'=>compact('role')], 200);
    }
}
