<?php

namespace App\Http\Controllers\Api\Kit;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Kit;
use App\ChargeCode;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class KitController extends Controller
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
        $prefix = DB::getTablePrefix();
        $chargeCode = ChargeCode::whereRaw($prefix.'charge_code.chg_code_id = '.$prefix.'kit.chg_code_id')
                            ->select('chg_code_name')->limit(1);
        $kits = Kit::select('kit.*', DB::raw("({$chargeCode->toSql()}) as charge_name"))
                    ->where(function ($query) use($param, $currentUser){
                        $query->where('comp_id', $currentUser->comp_id);
                        if (!empty($param)) {
                            $query->where('kit_name', 'LIKE', '%'.$param.'%');
                        }
                    })
                    ->paginate(dataPerPage());

        return response()->json(['status'=>true, 'message'=>'All Kits', 'response'=>compact('kits')], 200);
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
            'kit_status' => 'required|in:1,0',
        ];
        if (!$id) {
            $rules['kit_name'] = 'required|string|max:255|unique:kit';            
        } else {
            $rules['kit_name'] = 'required|string|max:255|unique:kit,kit_name,'.$id.',kit_id';
        }        
        $rules['chg_code_id'] = [
            'required',
            Rule::exists('charge_code')->where(function ($query) use($request) {
                $query->where('chg_code_id', $request->input('chg_code_id'));
            }),
        ];       
        return $rules;
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $uuid = createUuid($request->input('kit_name').date('YmdHis'));
        $kit_sno = $uuid->string;
        $kit = new Kit();
        $kit->kit_sno = $kit_sno;
        $kit->comp_id = $currentUser->comp_id;
        $kit->kit_name = $request->input('kit_name');
        $kit->chg_code_id = $request->input('chg_code_id');
        $kit->kit_status = $request->input('kit_status');
        $kit->created_by = $currentUser->user_id;
        $kit->updated_by = $currentUser->user_id;
        $kit->created_at = new DateTime;
        $kit->updated_at = new DateTime;
        $kit->save();

        return Response()->json(['status'=>true, 'message' => 'Kit Saved', 'response' => compact('kit')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $kit = Kit::find($id);
        if (empty($kit)) {
            return Response()->json(['status'=>false, 'message' => 'Kit is not exist in our system', 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $chargeCodes = ChargeCode::where('comp_id', $currentUser->comp_id)->get();
        $chg_code_ids = explode(',', $kit->chg_code_id);
        $selectChargeCodes = ChargeCode::whereIn('chg_code_id', $chg_code_ids)->select('chg_code_id','chg_code_name')->get();
        return Response()->json(['status'=>true, 'message' => 'Kit', 'response' => compact('kit','chargeCodes','selectChargeCodes')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $kit = Kit::find($id);
        if (empty($kit)) {
            return Response()->json(['status'=>false, 'message' => 'Kit is not exist in our system', 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $chargeCodes = ChargeCode::where('comp_id', $currentUser->comp_id)->get();
        return Response()->json(['status'=>true, 'message' => 'Kit', 'response' => compact('kit','chargeCodes')], 200);
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
        
        $kit = Kit::find($id);
        if (empty($kit)) {
            return Response()->json(['status'=>false, 'message' => 'Kit is not exist in our system', 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $kit->kit_name = $request->input('kit_name');
        $kit->chg_code_id = $request->input('chg_code_id');
        $kit->kit_status = $request->input('kit_status');
        $kit->updated_by = $currentUser->user_id;
        $kit->updated_at = new DateTime;
        $kit->save();

        return Response()->json(['status'=>true, 'message' => 'Kit Updated', 'response' => compact('kit')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $kit = Kit::find($id);
        if (empty($kit)) {
            return Response()->json(['status'=>false, 'message' => 'Kit is not exist in our system', 'response' => []], 200);
        }
        $kit->delete();
        return Response()->json(['status'=>true, 'message' => 'Kit Deleted', 'response' => compact('kit')], 200);
    }
    public function getHelperData()
    {
        $currentUser = getApiCurrentUser();
        $chargeCodes = ChargeCode::where('comp_id', $currentUser->comp_id)->select('chg_code_name','chg_code_id')->get();
        return Response()->json(['status'=>true, 'message' => 'Kit', 'response' => compact('chargeCodes')], 200);
    }
}
