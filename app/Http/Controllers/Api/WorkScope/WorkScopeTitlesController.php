<?php

namespace App\Http\Controllers\Api\WorkScope;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\WorkScopeTitles;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class WorkScopeTitlesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = getApiCurrentUser();
        $workScope = WorkScopeTitles::where('comp_id', $currentUser->comp_id)->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Work Scops', 'response'=>compact('workScope')], 200); 
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
            'wrk_scp_status' => 'required|in:0,1',
        ];
        
        if (!$id) {
            $rules['wrk_scp_name'] = 'required|string|max:191|unique:work_scope_titles';   
        } else {
            $rules['wrk_scp_name'] = 'required|string|max:191|unique:work_scope_titles,wrk_scp_name,'.$id.',wrk_scp_id';
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
        $uuid = createUuid($request->input('wrk_scp_name').date('YmdHis'));
        $wrk_scp_sno = $uuid->string;

        $workScope = new WorkScopeTitles();
        $workScope->comp_id = $currentUser->comp_id;
        $workScope->wrk_scp_sno = $wrk_scp_sno;
        $workScope->wrk_scp_name = $request->input('wrk_scp_name');
        $workScope->wrk_scp_status = $request->input('wrk_scp_status');
        $workScope->created_by = $currentUser->user_id;
        $workScope->updated_by = $currentUser->user_id;
        $workScope->created_at = new DateTime;
        $workScope->updated_at = new DateTime;
        $workScope->save();

        return response()->json(['status'=>true, 'message'=>'Work Scope Saved','response'=>compact('workScope')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $workScope = WorkScopeTitles::find($id);
        if (empty($workScope)) {
            return response()->json(['status'=>false, 'message'=>'Work Scope is not exists in our system','response'=>[]], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Work Scope','response'=>compact('workScope')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $workScope = WorkScopeTitles::find($id);
        if (empty($workScope)) {
            return response()->json(['status'=>false, 'message'=>'Work Scope is not exists in our system','response'=>[]], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Work Scope','response'=>compact('workScope')], 200);
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
        $workScope = WorkScopeTitles::find($id);
        if (empty($workScope)) {
            return response()->json(['status'=>false, 'message'=>'Work Scope is not exists in our system','response'=>[]], 200);
        }
        $currentUser = getApiCurrentUser();
        $workScope->wrk_scp_name = $request->input('wrk_scp_name');
        $workScope->wrk_scp_status = $request->input('wrk_scp_status');
        $workScope->updated_by = $currentUser->user_id;
        $workScope->updated_at = new DateTime;
        $workScope->save(); 

        return response()->json(['status'=>true, 'message'=>'Work Scope Updated','response'=>compact('workScope')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $workScope = WorkScopeTitles::find($id);
        if (empty($workScope)) {
            return response()->json(['status'=>false, 'message'=>'Work Scope is not exists in our system','response'=>[]], 200);
        }
        $workScope->delete();
        return response()->json(['status'=>true, 'message'=>'Work Scope Deleted','response'=>compact('workScope')], 200);
    }
}
