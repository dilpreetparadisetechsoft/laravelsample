<?php

namespace App\Http\Controllers\Api\Indicator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Indicator;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class IndicatorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = getApiCurrentUser();
        $indicators = Indicator::where('comp_id', $currentUser->comp_id)->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Indicator', 'response'=>compact('indicators')], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function storeRules($id= null)
    {
        $rules = [
            'ind_status' => 'required|in:0,1',
        ];
        
        if (!$id) {
            $rules['ind_name'] = 'required|string|max:191|unique:indicator';   
        } else {
            $rules['ind_name'] = 'required|string|max:191|unique:indicator,ind_name,'.$id.',ind_id';
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
        $uuid = createUuid($request->input('ind_name').date('YmdHis'));
        $ind_sno = $uuid->string;

        $indicator = new Indicator();
        $indicator->ind_sno = $ind_sno;
        $indicator->comp_id = $currentUser->comp_id;
        $indicator->ind_name = $request->input('ind_name');
        $indicator->ind_status = $request->input('ind_status');
        $indicator->created_by = $currentUser->user_id;
        $indicator->updated_by = $currentUser->user_id;
        $indicator->created_at = new DateTime;
        $indicator->updated_at = new DateTime;
        $indicator->save();

        return response()->json(['status'=>true, 'message'=>'Indicator Saved','response'=>compact('indicator')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $indicator = Indicator::find($id);
        if (empty($indicator)) {
            return response()->json(['status'=>false, 'message'=>'Indicator is not exist in our system','response'=>[]], 200);       
        }
        return response()->json(['status'=>true, 'message'=>'Indicator','response'=>compact('indicator')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $indicator = Indicator::find($id);
        if (empty($indicator)) {
            return response()->json(['status'=>false, 'message'=>'Indicator is not exist in our system','response'=>[]], 200);       
        }
        return response()->json(['status'=>true, 'message'=>'Indicator','response'=>compact('indicator')], 200);
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
        $validator = Validator::make($request->all(), self::storeRules($id));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $indicator = Indicator::find($id);
        if (empty($indicator)) {
            return response()->json(['status'=>false, 'message'=>'Indicator is not exist in our system','response'=>[]], 200);       
        }
        $currentUser = getApiCurrentUser();
        $indicator->ind_name = $request->input('ind_name');
        $indicator->ind_status = $request->input('ind_status');
        $indicator->updated_by = $currentUser->user_id;
        $indicator->updated_at = new DateTime;
        $indicator->save();
        return response()->json(['status'=>true, 'message'=>'Indicator Updated','response'=>compact('indicator')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $indicator = Indicator::find($id);
        if (empty($indicator)) {
            return response()->json(['status'=>false, 'message'=>'Indicator is not exist in our system','response'=>[]], 200);       
        }
        $indicator->delete();
        return response()->json(['status'=>true, 'message'=>'Indicator Deleted','response'=>compact('indicator')], 200);
    }
}
