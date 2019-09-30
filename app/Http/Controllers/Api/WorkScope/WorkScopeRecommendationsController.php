<?php

namespace App\Http\Controllers\Api\WorkScope;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\WorkScopeRecommendations;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class WorkScopeRecommendationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = getApiCurrentUser();
        $workScopeRecommendations = WorkScopeRecommendations::where('comp_id', $currentUser->comp_id)->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Work Scops Recommendations', 'response'=>compact('workScopeRecommendations')], 200);        
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
            'wrk_scp_rec_status' => 'required|in:0,1',
            'wrk_scp_id' => [
                'required',
                Rule::exists('work_scope_titles')->where(function ($query) use($request) {
                    $query->where('wrk_scp_id', $request->input('wrk_scp_id'));
                }),
            ],
        ];
        
        if (!$id) {
            $rules['wrk_scp_rec_name'] = 'required|string|max:191|unique:work_scope_recommendations';   
        } else {
            $rules['wrk_scp_rec_name'] = 'required|string|max:191|unique:work_scope_recommendations,wrk_scp_rec_name,'.$id.',wrk_scp_rec_id';
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
        $uuid = createUuid($request->input('wrk_scp_rec_name').date('YmdHis'));
        $wrk_scp_rec_sno = $uuid->string;
        
        $workScopeRecommendation = new WorkScopeRecommendations();
        $workScopeRecommendation->comp_id = $currentUser->comp_id;
        $workScopeRecommendation->wrk_scp_rec_sno = $wrk_scp_rec_sno;
        $workScopeRecommendation->wrk_scp_id = $request->input('wrk_scp_id');
        $workScopeRecommendation->wrk_scp_rec_name = $request->input('wrk_scp_rec_name');
        $workScopeRecommendation->wrk_scp_rec_status = $request->input('wrk_scp_rec_status');
        $workScopeRecommendation->created_by = $currentUser->user_id;
        $workScopeRecommendation->updated_by = $currentUser->user_id;
        $workScopeRecommendation->created_at = new DateTime;
        $workScopeRecommendation->updated_at = new DateTime;
        $workScopeRecommendation->save();

        return response()->json(['status'=>true, 'message'=>'Work Scope Recommendation Saved','response'=>compact('workScopeRecommendation')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $workScopeRecommendation = WorkScopeRecommendations::find($id);
        if (empty($workScopeRecommendation)) {
            return response()->json(['status'=>false, 'message'=>'Work Scope Recommendation is not exists in our system','response'=>[]], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Work Scope Recommendation','response'=>compact('workScopeRecommendation')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $workScopeRecommendation = WorkScopeRecommendations::find($id);
        if (empty($workScopeRecommendation)) {
            return response()->json(['status'=>false, 'message'=>'Work Scope Recommendation is not exists in our system','response'=>[]], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Work Scope Recommendation','response'=>compact('workScopeRecommendation')], 200);
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
        $currentUser = getApiCurrentUser();
        $workScopeRecommendation = WorkScopeRecommendations::find($id);
        if (empty($workScopeRecommendation)) {
            return response()->json(['status'=>false, 'message'=>'Work Scope Recommendation is not exists in our system','response'=>[]], 200);
        }
        $workScopeRecommendation->wrk_scp_id = $request->input('wrk_scp_id');
        $workScopeRecommendation->wrk_scp_rec_name = $request->input('wrk_scp_rec_name');
        $workScopeRecommendation->wrk_scp_rec_status = $request->input('wrk_scp_rec_status');
        $workScopeRecommendation->updated_by = $currentUser->user_id;
        $workScopeRecommendation->updated_at = new DateTime;
        $workScopeRecommendation->save();

        return response()->json(['status'=>true, 'message'=>'Work Scope Recommendation Updated','response'=>compact('workScopeRecommendation')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $workScopeRecommendation = WorkScopeRecommendations::find($id);
        if (empty($workScopeRecommendation)) {
            return response()->json(['status'=>false, 'message'=>'Work Scope Recommendation is not exists in our system','response'=>[]], 200);
        }
        $workScopeRecommendation->delete();
        return response()->json(['status'=>true, 'message'=>'Work Scope Recommendation','response'=>compact('workScopeRecommendation')], 200);
    }
}
