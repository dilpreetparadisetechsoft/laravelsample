<?php

namespace App\Http\Controllers\Api\ChargeCode;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\ChargeCode;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class ChargeCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $param = $request->input('param');
        $currentUser = getApiCurrentUser();
        $chargecodes = ChargeCode::where(function ($query) use($param, $currentUser){
                            $query->where('comp_id', $currentUser->comp_id);
                            if (!empty($param)) {
                                $query->where('chg_code_name', 'LIKE', '%'.$param.'%');
                            }
                        })->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Charge Codes', 'response'=>compact('chargecodes')], 200);
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
            'chg_code' => 'required|string|max:191',
            'description' => 'required|string',
            'unit_price' => 'required|numeric',
            'unit_of_measurement' => 'required|string|max:191',
            'our_cost' => 'required|numeric',
            'gl_code' => 'required|string|max:191',
            'count_in_wo' => 'required|string|max:191',
            'chg_code_status' => 'required|in:0,1',
        ];
        
        if (!$id) {
            $rules['chg_code_name'] = 'required|string|max:191|unique:charge_code';   
        } else {
            $rules['chg_code_name'] = 'required|string|max:191|unique:charge_code,chg_code_name,'.$id.',chg_code_id';
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
        $uuid = createUuid($request->input('chg_code_name').date('YmdHis'));
        $chg_code_sno = $uuid->string;

        $chargecode = new ChargeCode();
        $chargecode->chg_code_sno = $chg_code_sno;
        $chargecode->comp_id = $currentUser->comp_id;
        $chargecode->chg_code_name = $request->input('chg_code_name');
        $chargecode->chg_code = $request->input('chg_code');  
        $chargecode->description = $request->input('description');
        $chargecode->unit_price = $request->input('unit_price');
        $chargecode->unit_of_measurement = $request->input('unit_of_measurement');
        $chargecode->our_cost = $request->input('our_cost');
        $chargecode->gl_code = $request->input('gl_code');
        $chargecode->count_in_wo = $request->input('count_in_wo');
        $chargecode->chg_code_status = $request->input('chg_code_status');
        $chargecode->created_by = $currentUser->user_id;
        $chargecode->updated_by = $currentUser->user_id;
        $chargecode->created_at = new DateTime;
        $chargecode->updated_at = new DateTime;
        $chargecode->save();

        return response()->json(['status'=>true, 'message'=>'Charge Code Saved','response'=>compact('chargecode')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $chargecode = ChargeCode::find($id);
        if (empty($chargecode)) {
            return response()->json(['status'=>false, 'message'=>'Charge code is not exists in our system.','response'=>[]], 200);       
        }
        return response()->json(['status'=>true, 'message'=>'Charge Code','response'=>compact('chargecode')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $chargecode = ChargeCode::find($id);
        if (empty($chargecode)) {
            return response()->json(['status'=>false, 'message'=>'Charge code is not exists in our system.','response'=>[]], 200);       
        }
        return response()->json(['status'=>true, 'message'=>'Charge Code','response'=>compact('chargecode')], 200);
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
        $chargecode = ChargeCode::find($id);
        if (empty($chargecode)) {
            return response()->json(['status'=>false, 'message'=>'Charge code is not exists in our system.','response'=>[]], 200);       
        }
        $currentUser = getApiCurrentUser();
        $chargecode->chg_code_name = $request->input('chg_code_name');
        $chargecode->chg_code = $request->input('chg_code');  
        $chargecode->description = $request->input('description');
        $chargecode->unit_price = $request->input('unit_price');
        $chargecode->unit_of_measurement = $request->input('unit_of_measurement');
        $chargecode->our_cost = $request->input('our_cost');
        $chargecode->gl_code = $request->input('gl_code');
        $chargecode->count_in_wo = $request->input('count_in_wo');
        $chargecode->chg_code_status = $request->input('chg_code_status');
        $chargecode->created_by = $currentUser->user_id;
        $chargecode->created_at = new DateTime;
        $chargecode->save();
        return response()->json(['status'=>true, 'message'=>'Charge Code','response'=>compact('chargecode')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $chargecode = ChargeCode::find($id);
        if (empty($chargecode)) {
            return response()->json(['status'=>false, 'message'=>'Charge code is not exists in our system.','response'=>[]], 200);       
        }
        $chargecode->delete();
        return response()->json(['status'=>true, 'message'=>'Charge Code','response'=>compact('chargecode')], 200);
    }
}
