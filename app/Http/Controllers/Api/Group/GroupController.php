<?php

namespace App\Http\Controllers\Api\Group;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Group;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = getApiCurrentUser();
        $groups = Group::where('comp_id', $currentUser->comp_id)->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Groups', 'response'=>compact('groups')], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function storeRules($request, $id= null)
    {
        $rules = [
            'group_status' => 'required|in:0,1',
        ];
        
        if (!$id) {
            $rules['group_name'] = 'required|string|max:191|unique:groups';   
        } else {
            $rules['group_name'] = 'required|string|max:191|unique:groups,group_name,'.$id.',group_id';
        }
        return $rules;
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $uuid = createUuid($request->input('group_name').date('YmdHis'));
        $group_sno = $uuid->string;
        
        $group = new Group();
        $group->comp_id = $currentUser->comp_id;
        $group->group_sno = $group_sno;
        $group->group_name = $request->input('group_name');
        $group->group_status = $request->input('group_status');
        $group->created_by = $currentUser->user_id;
        $group->updated_by = $currentUser->user_id;
        $group->created_at = new DateTime;
        $group->updated_at = new DateTime;
        $group->save();

        return response()->json(['status'=>true, 'message'=>'Group Saved','response'=>compact('group')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $group = Group::find($id);
        if (empty($group)) {
            return response()->json(['status'=>false, 'message'=>'Group is not exists in our system','response'=>[]], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Group','response'=>compact('group')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $group = Group::find($id);
        if (empty($group)) {
            return response()->json(['status'=>false, 'message'=>'Group is not exists in our system','response'=>[]], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Group','response'=>compact('group')], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), self::storeRules($request, $id));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }

        $group = Group::find($id);
        if (empty($group)) {
            return response()->json(['status'=>false, 'message'=>'Group is not exists in our system','response'=>[]], 200);
        }

        $currentUser = getApiCurrentUser();
        
        $group->comp_id = $currentUser->comp_id;
        $group->group_name = $request->input('group_name');
        $group->group_status = $request->input('group_status');
        $group->updated_by = $currentUser->user_id;
        $group->updated_at = new DateTime;
        $group->save();

        return response()->json(['status'=>true, 'message'=>'Group updated','response'=>compact('group')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $group = Group::find($id);
        if (empty($group)) {
            return response()->json(['status'=>false, 'message'=>'Group is not exists in our system','response'=>[]], 200);
        }
        $group->delete();
        return response()->json(['status'=>true, 'message'=>'Group Deleted','response'=>compact('group')], 200);
    }
}
