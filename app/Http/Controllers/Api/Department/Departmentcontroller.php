<?php

namespace App\Http\Controllers\Api\Department;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Department;
use Validator, DateTime, Config, Helpers;
class Departmentcontroller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = getApiCurrentUser();
        $departments = Department::where('created_by', $currentUser->user_id)->paginate(dataPerPage());
		return Response()->json(['status'=>true, 'message' => 'Get all Department.', 'response' => compact('departments')], 200);
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
            'name' => 'required|string|max:191|unique:department', 
        ];
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules());
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
		$currentUser = getApiCurrentUser();
        $department = new Department(); 
        $department->name = $request->input('name');
        $department->created_by = $currentUser->user_id;
		$department->created_at = new DateTime;
		$department->updated_at = new DateTime;
		$department->save();
        return Response()->json(['status'=>true, 'message' => 'Save Department.', 'response' => compact('department')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($dep_id)
    {
        $department = Department::find($dep_id);
		if (empty($department)) {
            return Response()->json(['status'=>false, 'message' => 'Department is not exists', 'response' => []], 200);
        }
        return Response()->json(['status'=>true, 'message' => 'Department', 'response' => compact('department')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($dep_id)
    {
        $department = Department::find($dep_id);
        if (empty($department)) {
            return Response()->json(['status'=>false, 'message' => 'Department is not exists', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Department', 'response'=>compact('department')], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $dep_id)
    {
        $validator = Validator::make($request->all(), self::storeRules());
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $department = Department::find($dep_id);
        if (empty($department)) {
            return Response()->json(['status'=>false, 'message' => 'Department is not exists', 'response' => []], 200);
        }
		
        $department->name = $request->input('name');
		$department->updated_at = new DateTime;
        $department->save();
        return Response()->json(['status'=>true, 'message' => 'Update Department.', 'response' => compact('department')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($dep_id)
    {
        $department = Department::find($dep_id);
        if (empty($department)) {
            return Response()->json(['status'=>false, 'message' => 'Department is not exists', 'response' => []], 200);
        }
        $department->delete();
        return response()->json(['status'=>true, 'message'=>'Deleted', 'response'=>compact('department')], 200);
    }
}
