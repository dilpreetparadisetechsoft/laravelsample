<?php

namespace App\Http\Controllers\Api\LeadSource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\LeadSource;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class LeadSourceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $param = $request->input('param');
        $currentUser = getApiCurrentUser();
        $leads = LeadSource::where(function ($query) use($param, $currentUser){
                        $query->where('comp_id', $currentUser->comp_id);
                        if (!empty($param)) {
                            $query->where('name', 'LIKE', '%'.$param.'%');
                        }
                    })->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Leads', 'response'=>compact('leads')], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function storeRules($id= null)
    {
        $rules = ['status'=>'required|in:0,1'];
        if (!$id) {
            $rules['name'] = 'required|string|max:255|unique:lead_source';   
        } else {
            $rules['name'] = 'required|string|max:255|unique:lead_source,name,'.$id.',lead_id';
        }
        return $rules;
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules());
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $lead = new LeadSource();
        $lead->name = $request->input('name');
        $lead->status = $request->input('status');
        $lead->comp_id = $currentUser->comp_id;
        $lead->created_by = $currentUser->user_id;
        $lead->created_at = new DateTime;
        $lead->updated_at = new DateTime;    
        $lead->save();

        return response()->json(['status'=>true, 'message'=>'Lead Source Saved', 'response'=>compact('lead')], 200);   
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $lead = LeadSource::find($id);
        if (empty($lead)) {
            return response()->json(['status'=>false, 'message'=>'Lead Source not exists', 'response'=>[]], 200);          
        }
        return response()->json(['status'=>true, 'message'=>'Lead Source', 'response'=>compact('lead')], 200);   
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $lead = LeadSource::find($id);
        if (empty($lead)) {
            return response()->json(['status'=>false, 'message'=>'Lead Source not exists', 'response'=>[]], 200);          
        }
        return response()->json(['status'=>true, 'message'=>'Lead Source', 'response'=>compact('lead')], 200);
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
        $validator = Validator::make($request->all(), self::storeRules());
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }

        $lead = LeadSource::find($id);
        if (empty($lead)) {
            return response()->json(['status'=>false, 'message'=>'Lead Source not exists', 'response'=>[]], 200);          
        }
        $lead->name = $request->input('name');
        $lead->status = $request->input('status');
        $lead->updated_at = new DateTime;    
        $lead->save();
        return response()->json(['status'=>true, 'message'=>'Lead Source Updated', 'response'=>compact('lead')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $lead = LeadSource::find($id);
        if (empty($lead)) {
            return response()->json(['status'=>false, 'message'=>'Lead Source not exists', 'response'=>[]], 200);          
        }
        $lead->delete();
        return response()->json(['status'=>true, 'message'=>'Lead Source Deleted', 'response'=>compact('lead')], 200);
    }
}
