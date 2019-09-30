<?php

namespace App\Http\Controllers\Api\WorkOrder;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\WorkOrderEquipment;
use App\WorkOrder;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class WorkOrderEquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = getApiCurrentUser();
        $workOrder = WorkOrder::where('comp_id', $currentUser->comp_id)->select('work_order_id')->get()->pluck('work_order_id');
        $workOrderequipments = WorkOrderEquipment::whereIn('comp_id', $workOrder)->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Work Order Equipments', 'response'=>compact('workOrderequipments')], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function storeRules($request, $id= null)
    {
        return [
            'work_order_id' => [
                'required',
                Rule::exists('work_order_equipments')->where(function ($query) use($request) {
                    $query->where('work_order_id', $request->input('work_order_id'));
                }),
            ],
            'eq_id' => [
                'required',
                Rule::exists('equipment')->where(function ($query) use($request) {
                    $query->where('eq_id', $request->input('eq_id'));
                }),
            ],
            'no_of_days' => 'required|numeric',
            'no_of_quantities' => 'required|numeric',
        ];
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
       
        $workOrderEquipment = new WorkOrderEquipment();
        $workOrderEquipment->work_order_id = $request->input('work_order_id');
        $workOrderEquipment->eq_id = $request->input('eq_id');
        $workOrderEquipment->no_of_days = $request->input('no_of_days');
        $workOrderEquipment->no_of_quantities = $request->input('no_of_quantities');
        $workOrderEquipment->created_by = $currentUser->user_id;
        $workOrderEquipment->updated_by = $currentUser->user_id;
        $workOrderEquipment->created_at = new DateTime;
        $workOrderEquipment->updated_at = new DateTime;
        $workOrderEquipment->save();

        return response()->json(['status'=>true, 'message'=>'Work Order Equipment Saved','response'=>compact('workOrderEquipment')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $workOrderEquipment = WorkOrderEquipment::find($id);
        if (empty($workOrderEquipment)) {
            return Response()->json(['status'=>false, 'message' => 'Work Order Equipment is not exists in our system.', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Work Order Equipment','response'=>compact('workOrderEquipment')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $workOrderEquipment = WorkOrderEquipment::find($id);
        if (empty($workOrderEquipment)) {
            return Response()->json(['status'=>false, 'message' => 'Work Order Equipment is not exists in our system.', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Work Order Equipment','response'=>compact('workOrderEquipment')], 200);
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
        $validator = Validator::make($request->all(), self::storeRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        
        $workOrderEquipment = WorkOrderEquipment::find($id);
        if (empty($workOrderEquipment)) {
            return Response()->json(['status'=>false, 'message' => 'Work Order Equipment is not exists in our system.', 'response' => []], 200);
        }

        $workOrderEquipment->work_order_id = $request->input('work_order_id');
        $workOrderEquipment->eq_id = $request->input('eq_id');
        $workOrderEquipment->no_of_days = $request->input('no_of_days');
        $workOrderEquipment->no_of_quantities = $request->input('no_of_quantities');
        $workOrderEquipment->updated_by = $currentUser->user_id;
        $workOrderEquipment->updated_at = new DateTime;
        $workOrderEquipment->save();

        return response()->json(['status'=>true, 'message'=>'Work Order Equipment Updated','response'=>compact('workOrderEquipment')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $workOrderEquipment = WorkOrderEquipment::find($id);
        if (empty($workOrderEquipment)) {
            return Response()->json(['status'=>false, 'message' => 'Work Order Equipment is not exists in our system.', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Work Order Equipment Deleted','response'=>compact('workOrderEquipment')], 200);
    }
}
