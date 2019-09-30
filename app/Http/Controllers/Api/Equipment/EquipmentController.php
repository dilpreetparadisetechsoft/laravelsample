<?php

namespace App\Http\Controllers\Api\Equipment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Equipment;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class EquipmentController extends Controller
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
        $equipments = Equipment::where(function ($query) use($param, $currentUser){
                            $query->where('comp_id', $currentUser->comp_id);
                            if (!empty($param)) {
                                $query->where('name', 'LIKE', '%'.$param.'%');
                            }
                        })->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Equipments', 'response'=>compact('equipments')], 200);
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
            'eq_status' => 'required|in:0,1',
        ];
        
        if (!$id) {
            $rules['name'] = 'required|string|max:191|unique:equipment';   
        } else {
            $rules['name'] = 'required|string|max:191|unique:equipment,name,'.$id.',eq_id';
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
        $uuid = createUuid($request->input('name').date('YmdHis'));
        $eq_sno = $uuid->string;

        $equipment = new Equipment();
        $equipment->eq_sno = $eq_sno;
        $equipment->comp_id = $currentUser->comp_id;
        $equipment->name = $request->input('name');
        $equipment->eq_status = $request->input('eq_status');
        $equipment->created_by = $currentUser->user_id;
        $equipment->updated_by = $currentUser->user_id;
        $equipment->created_at = new DateTime;
        $equipment->updated_at = new DateTime;
        $equipment->save();

        return response()->json(['status'=>true, 'message'=>'Equipment Saved','response'=>compact('equipment')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $equipment = Equipment::find($id);
        if (empty($equipment)) {
            return response()->json(['status'=>false, 'message'=>'Equipment is not exist in our system','response'=>[]], 200);       
        }
        return response()->json(['status'=>true, 'message'=>'Equipment','response'=>compact('equipment')], 200);
    }   

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $equipment = Equipment::find($id);
        if (empty($equipment)) {
            return response()->json(['status'=>false, 'message'=>'Equipment is not exist in our system','response'=>[]], 200);       
        }
        return response()->json(['status'=>true, 'message'=>'Equipment','response'=>compact('equipment')], 200);
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
        $equipment = Equipment::find($id);
        if (empty($equipment)) {
            return response()->json(['status'=>false, 'message'=>'Equipment is not exist in our system','response'=>[]], 200);       
        }
        $currentUser = getApiCurrentUser();
        $equipment->name = $request->input('name');
        $equipment->eq_status = $request->input('eq_status');
        $equipment->updated_by = $currentUser->user_id;
        $equipment->updated_at = new DateTime;
        $equipment->save();
        return response()->json(['status'=>true, 'message'=>'Equipment Updated','response'=>compact('equipment')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $equipment = Equipment::find($id);
        if (empty($equipment)) {
            return response()->json(['status'=>false, 'message'=>'Equipment is not exist in our system','response'=>[]], 200);       
        }
        $equipment->delete();
        return response()->json(['status'=>true, 'message'=>'Equipment Deleted','response'=>compact('equipment')], 200);
    }
}
