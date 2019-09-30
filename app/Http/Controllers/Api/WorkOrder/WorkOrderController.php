<?php

namespace App\Http\Controllers\Api\WorkOrder;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\WorkOrder;
use App\Estimate;
use App\Equipment;
use App\WorkOrderEquipment;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class WorkOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($estimate_id = null)
    {
        $currentUser = getApiCurrentUser();
        $estimate = Estimate::where('estimate.comp_id', $currentUser->comp_id)
                        ->where('estimate.estimate_id', $estimate_id)
                        ->get()->first();

        if (empty($estimate)) {
            return response()->json(['status'=>false, 'message'=>'You are not authorize to access this', 'response'=>[]], 200);
        }

        $workOrders = WorkOrder::where('comp_id', $currentUser->comp_id)->where('estimate_id', $estimate_id)->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Work Order', 'response'=>compact('workOrders','estimate')], 200);
    }

    public function create($estimate_id = null)
    {
        $currentUser = getApiCurrentUser();
        $estimate = Estimate::join('users','users.user_id','estimate.user_id')
                        ->join('customer', 'customer.customer_id', 'estimate.customer_id')
                        ->select('estimate.estimate_id','estimate.grand_total','estimate.user_id','users.first_name as project_manager','customer.*')
                        ->where('estimate.comp_id', $currentUser->comp_id)
                        ->where('estimate.estimate_id', $estimate_id)
                        ->get()->first();
        if (empty($estimate)) {
            return response()->json(['status'=>false, 'message'=>'You are not authorize to access this', 'response'=>[]], 200);
        }
        $equipments = Equipment::where('comp_id', $currentUser->comp_id)->get();
        return response()->json(['status'=>true, 'message'=>'Estimate', 'response'=>compact('estimate','equipments')], 200);
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
            'estimate_id' => [
                'required',
                Rule::exists('estimate')->where(function ($query) use($request) {
                    $query->where('estimate_id', $request->input('estimate_id'));
                }),
            ],
            'customer_id' => [
                'required',
                Rule::exists('customer')->where(function ($query) use($request) {
                    $query->where('customer_id', $request->input('customer_id'));
                }),
            ],
            'user_id' => [
                'required',
                Rule::exists('users')->where(function ($query) use($request) {
                    $query->where('user_id', $request->input('user_id'));
                }),
            ],
            'desc_of_job' => 'required',
            'preferred_date' => 'required|date_format:Y-m-d',
            'completed_date' => 'required|date_format:Y-m-d',
            'no_of_technicians' => 'required|numeric',
            'no_of_days' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'note' => 'string',
            'wrk_status' => 'required|in:0,1'
        ];
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $uuid = createUuid($request->input('desc_of_job').date('YmdHis'));
        $wrk_sno = $uuid->string;
        $representative_id = $request->input('representative_id');
        $representative_id = (is_array($representative_id)?implode(',', $representative_id):$representative_id);

        $workOrder = new WorkOrder();
        $workOrder->wrk_sno = $wrk_sno;
        $workOrder->comp_id = $currentUser->comp_id;
        $workOrder->estimate_id = $request->input('estimate_id');
        $workOrder->customer_id = $request->input('customer_id');
        $workOrder->user_id = $request->input('user_id');
        $workOrder->representative_id = $representative_id;
        $workOrder->desc_of_job = $request->input('desc_of_job');
        $workOrder->preferred_date = ($request->input('preferred_date')?date('Y-m-d', strtotime($request->input('preferred_date'))):$request->input('preferred_date'));
        $workOrder->completed_date = ($request->input('completed_date')?date('Y-m-d', strtotime($request->input('completed_date'))):$request->input('completed_date'));
        $workOrder->no_of_technicians = $request->input('no_of_technicians');
        $workOrder->no_of_days = $request->input('no_of_days');
        $workOrder->total_days = $request->input('no_of_days');
        $workOrder->total_amount = $request->input('total_amount');
        $workOrder->duration_of_equipment = 0;
        $workOrder->note = $request->input('note');
        $workOrder->wrk_status = $request->input('wrk_status');
        $workOrder->created_by = $currentUser->user_id;
        $workOrder->updated_by = $currentUser->user_id;
        $workOrder->created_at = new DateTime;
        $workOrder->updated_at = new DateTime;
        $workOrder->save();
        self::saveWorkOrderEquipments($request, $workOrder->work_order_id, $currentUser);

        return response()->json(['status'=>true, 'message'=>'WorkOrder Saved','response'=>compact('workOrder')], 200);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $workOrder = WorkOrder::find($id);
        if (empty($workOrder)) {
            return Response()->json(['status'=>false, 'message' => 'Work Order is not exists in our system.', 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $workOrder->project_manager = User::where('user_id', $workOrder->user_id)->get()->pluck('first_name')->first();
        $workOrder->address = Estimate::where('estimate_id', $workOrder->estimate_id)->get()->pluck('address')->first();

        $workOrderEquipments = WorkOrderEquipment::where('work_order_id', $workOrder->work_order_id)->get();
        $work_order_eq_ids = [];
        foreach ($workOrderEquipments as $workOrderEquipment) {
            $eq_ids[] = $workOrderEquipment->eq_id;
        }
        $workOrder->equipments = Equipment::wherein('eq_id', [1,2])->select(DB::raw('GROUP_CONCAT(name, "") AS equipments'))->get()->pluck('equipments')->first();
        $selectedRepresentative = User::where('comp_id', $currentUser->comp_id)->wherein('user_id', explode(',', $workOrder->representative_id))->get();
        return response()->json(['status'=>true, 'message'=>'WorkOrder','response'=>compact('workOrder','workOrderEquipments','selectedRepresentative')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $workOrder = WorkOrder::find($id);
        if (empty($workOrder)) {
            return Response()->json(['status'=>false, 'message' => 'Work Order is not exists in our system.', 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $estimate = Estimate::join('users','users.user_id','estimate.user_id')
                        ->join('customer', 'customer.customer_id', 'estimate.customer_id')
                        ->select('estimate.estimate_id','estimate.grand_total','estimate.user_id','users.first_name as project_manager','customer.*')
                        ->where('estimate.comp_id', $currentUser->comp_id)
                        ->where('estimate.estimate_id', $workOrder->estimate_id)
                        ->get()->first();
        if (empty($estimate)) {
            return response()->json(['status'=>false, 'message'=>'You are not authorize to access this', 'response'=>[]], 200);
        }
        $equipments = Equipment::where('comp_id', $currentUser->comp_id)->get();
        $workOrderEquipments = WorkOrderEquipment::where('work_order_id', $workOrder->work_order_id)->get();
        return response()->json(['status'=>true, 'message'=>'WorkOrder','response'=>compact('workOrder','estimate','equipments','workOrderEquipments')], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public static function updateRules($request, $id= null)
    {
        return [
            'representative_id' => 'required',
            'schedule_date' => 'required|date_format:Y-m-d',
            'notes' => 'string',
        ];
    }
    public function update(Request $request, $id)
    {
        $target = $request->input('target');
        if ($target != 'scheduler') {
            $validator = Validator::make($request->all(), self::storeRules($request));
            if ($validator->fails()) {
                return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
            }
        }else{
            $validator = Validator::make($request->all(), self::updateRules($request));
            if ($validator->fails()) {
                return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
            }
        }

        $workOrder = WorkOrder::find($id);
        if (empty($workOrder)) {
            return Response()->json(['status'=>false, 'message' => 'Work Order is not exists in our system.', 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();

        $representative_id = $request->input('representative_id');
        $representative_id = (is_array($representative_id)?implode(',', $representative_id):$representative_id);
        
        if ($target != 'scheduler') {
            $workOrder->estimate_id = $request->input('estimate_id');
            $workOrder->customer_id = $request->input('customer_id');
            $workOrder->user_id = $request->input('user_id');
            $workOrder->desc_of_job = $request->input('desc_of_job');
            $workOrder->preferred_date = ($request->input('preferred_date')?date('Y-m-d', strtotime($request->input('preferred_date'))):$request->input('preferred_date'));
            $workOrder->completed_date = ($request->input('completed_date')?date('Y-m-d', strtotime($request->input('completed_date'))):$request->input('completed_date'));
            $workOrder->no_of_technicians = $request->input('no_of_technicians');
            $workOrder->total_days = $request->input('no_of_days');
            $workOrder->total_amount = $request->input('total_amount');
            $workOrder->note = $request->input('note');
            $workOrder->wrk_status = $request->input('wrk_status');
        }else{
            $workOrder->schedule_date = $request->input('schedule_date');
            $workOrder->notes = $request->input('notes');
        }
        $workOrder->representative_id = $representative_id;
        $workOrder->updated_by = $currentUser->user_id;
        $workOrder->updated_at = new DateTime;
        $workOrder->save();
        if ($target != 'scheduler') {
            self::saveWorkOrderEquipments($request, $workOrder->work_order_id, $currentUser);
        }
        
        return response()->json(['status'=>true, 'message'=>'WorkOrder Updated','response'=>compact('workOrder')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $workOrder = WorkOrder::find($id);
        if (empty($workOrder)) {
            return Response()->json(['status'=>false, 'message' => 'Work Order is not exists in our system.', 'response' => []], 200);
        }
        $workOrder->delete();
        return response()->json(['status'=>true, 'message'=>'WorkOrder Deleted','response'=>compact('workOrder')], 200);
    }
    public static function saveWorkOrderEquipments($request, $work_order_id, $currentUser)
    {
        $work_order_eq_ids = [];
        $workOrderEquipments = (array)$request->input('workOrderEquipments');

        if (!empty($workOrderEquipments) && is_array($workOrderEquipments)) {
            foreach ($workOrderEquipments as $equipment) {
                $workOrderEquipment = WorkOrderEquipment::where('work_order_id', $work_order_id)->where('work_order_eq_id', $equipment['work_order_eq_id'])->get()->first();
                if (!$workOrderEquipment) {
                    $workOrderEquipment = new WorkOrderEquipment();
                    $workOrderEquipment->work_order_id = $work_order_id;
                    $workOrderEquipment->created_by = $currentUser->user_id;
                    $workOrderEquipment->created_at = new DateTime;
                }
                $workOrderEquipment->eq_id = $equipment['eq_id'];
                $workOrderEquipment->no_of_days = $equipment['no_of_days'];
                $workOrderEquipment->no_of_quantities = $equipment['no_of_quantities'];
                $workOrderEquipment->updated_by = $currentUser->user_id;
                $workOrderEquipment->updated_at = new DateTime;
                $workOrderEquipment->save();

                $work_order_eq_ids[] = $workOrderEquipment->work_order_eq_id;
            }
        }
        WorkOrderEquipment::where('work_order_id', $work_order_id)->whereNotIn('work_order_eq_id', $work_order_eq_ids)->delete();        
    }
}
