<?php

namespace App\Http\Controllers\Api\PurchaseOrder;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\PurchaseOrder;
use App\Estimate;
use App\Networks;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($estimate_id = null)
    {
        $currentUser = getApiCurrentUser();
        $purchaseOrders = PurchaseOrder::where('comp_id', $currentUser->comp_id)->where('estimate_id', $estimate_id)->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Purchase Order', 'response'=>compact('purchaseOrders')], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
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
        $networks = Networks::where('comp_id', $currentUser->comp_id)->select('network_id','comp_name')->get();
        return response()->json(['status'=>true, 'message'=>'Estimate Networks', 'response'=>compact('estimate','networks')], 200);
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
            'network_id' => [
                'required',
                Rule::exists('networks')->where(function ($query) use($request) {
                    $query->where('network_id', $request->input('network_id'));
                }),
            ],
            'invoice_to' => 'required',
            'terms_of_pay_insurance_files' => 'required',
            'desc_of_job' => 'required',
            'date' => 'required|date_format:Y-m-d',
            'expected_start_date' => 'required|date_format:Y-m-d',
            'expected_end_date' => 'required|date_format:Y-m-d',
            'total_amount' => 'required|numeric',
            'time_material' => 'required|numeric',
            'confirmation_of_ack' => 'required|numeric',
            'purchase_order_status' => 'required|in:0,1',
            'address' => 'required|string',
            'pdf_name' => '',
            'po_approved' => 'required|in:0,1',
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
        $purchase_order_no = $uuid->string;

        $purchaseOrder = new PurchaseOrder();
        $purchaseOrder->purchase_order_no = $purchase_order_no;
        $purchaseOrder->comp_id = $currentUser->comp_id;
        $purchaseOrder->estimate_id = $request->input('estimate_id');
        $purchaseOrder->customer_id = $request->input('customer_id');
        $purchaseOrder->network_id = $request->input('network_id');
        $purchaseOrder->invoice_to = $request->input('invoice_to');
        $purchaseOrder->terms_of_pay_insurance_files = $request->input('terms_of_pay_insurance_files');
        $purchaseOrder->desc_of_job = $request->input('desc_of_job');
        $purchaseOrder->date = ($request->input('date')?date('Y-m-d', strtotime($request->input('date'))):$request->input('date'));
        $purchaseOrder->expected_start_date = ($request->input('expected_start_date')?date('Y-m-d', strtotime($request->input('expected_start_date'))):$request->input('expected_start_date'));
        $purchaseOrder->expected_end_date = ($request->input('expected_end_date')?date('Y-m-d', strtotime($request->input('expected_end_date'))):$request->input('expected_end_date'));
        $purchaseOrder->total_amount = $request->input('total_amount');
        $purchaseOrder->time_material = $request->input('time_material');
        $purchaseOrder->confirmation_of_ack = $request->input('confirmation_of_ack');
        $purchaseOrder->purchase_order_status = $request->input('purchase_order_status');
        $purchaseOrder->address = $request->input('address');

        if ($request->file('pdf_name') != '') {
            $purchaseOrder->pdf_name = fileuploadExtra($request, 'pdf_name');
        }
        
        $purchaseOrder->po_approved = $request->input('po_approved');
        $purchaseOrder->created_by = $currentUser->user_id;
        $purchaseOrder->updated_by = $currentUser->user_id;
        $purchaseOrder->created_at = new DateTime;
        $purchaseOrder->updated_at = new DateTime;
        $purchaseOrder->save();

        return response()->json(['status'=>true, 'message'=>'Purchase Order Saved','response'=>compact('purchaseOrder')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::find($id);
        if (empty($purchaseOrder)) {
            return Response()->json(['status'=>false, 'message' => 'Purchase Order is not exists in our system.', 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $estimate = Estimate::join('users','users.user_id','estimate.user_id')
                        ->join('customer', 'customer.customer_id', 'estimate.customer_id')
                        ->select('estimate.estimate_id','estimate.grand_total','estimate.user_id','users.first_name as project_manager','customer.*')
                        ->where('estimate.comp_id', $currentUser->comp_id)
                        ->where('estimate.estimate_id', $purchaseOrder->estimate_id)
                        ->get()->first();
        if (empty($estimate)) {
            return response()->json(['status'=>false, 'message'=>'You are not authorize to access this', 'response'=>[]], 200);
        }

        $networks = Networks::where('comp_id', $currentUser->comp_id)->get();
        return response()->json(['status'=>true, 'message'=>'Purchase Order','response'=>compact('purchaseOrder','estimate','networks')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $purchaseOrder = PurchaseOrder::find($id);
        if (empty($purchaseOrder)) {
            return Response()->json(['status'=>false, 'message' => 'Purchase Order is not exists in our system.', 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $estimate = Estimate::join('users','users.user_id','estimate.user_id')
                        ->join('customer', 'customer.customer_id', 'estimate.customer_id')
                        ->select('estimate.estimate_id','estimate.grand_total','estimate.user_id','users.first_name as project_manager','customer.*')
                        ->where('estimate.comp_id', $currentUser->comp_id)
                        ->where('estimate.estimate_id', $purchaseOrder->estimate_id)
                        ->get()->first();
        if (empty($estimate)) {
            return response()->json(['status'=>false, 'message'=>'You are not authorize to access this', 'response'=>[]], 200);
        }

        $networks = Networks::where('comp_id', $currentUser->comp_id)->get();
        return response()->json(['status'=>true, 'message'=>'Purchase Order','response'=>compact('purchaseOrder','estimate','networks')], 200);
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
        $purchaseOrder = PurchaseOrder::find($id);
        if (empty($purchaseOrder)) {
            return Response()->json(['status'=>false, 'message' => 'Purchase Order is not exists in our system.', 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();

        $purchaseOrder->comp_id = $currentUser->comp_id;
        $purchaseOrder->estimate_id = $request->input('estimate_id');
        $purchaseOrder->customer_id = $request->input('customer_id');
        $purchaseOrder->network_id = $request->input('network_id');
        $purchaseOrder->invoice_to = $request->input('invoice_to');
        $purchaseOrder->terms_of_pay_insurance_files = $request->input('terms_of_pay_insurance_files');
        $purchaseOrder->desc_of_job = $request->input('desc_of_job');
        $purchaseOrder->date = ($request->input('date')?date('Y-m-d', strtotime($request->input('date'))):$request->input('date'));
        $purchaseOrder->expected_start_date = ($request->input('expected_start_date')?date('Y-m-d', strtotime($request->input('expected_start_date'))):$request->input('expected_start_date'));
        $purchaseOrder->expected_end_date = ($request->input('expected_end_date')?date('Y-m-d', strtotime($request->input('expected_end_date'))):$request->input('expected_end_date'));
        $purchaseOrder->total_amount = $request->input('total_amount');
        $purchaseOrder->time_material = $request->input('time_material');
        $purchaseOrder->confirmation_of_ack = $request->input('confirmation_of_ack');
        $purchaseOrder->purchase_order_status = $request->input('purchase_order_status');
        $purchaseOrder->address = $request->input('address');

        if ($request->file('pdf_name') != '') {
            $purchaseOrder->pdf_name = fileuploadExtra($request, 'pdf_name');
        }
        
        $purchaseOrder->po_approved = $request->input('po_approved');
        $purchaseOrder->updated_by = $currentUser->user_id;
        $purchaseOrder->updated_at = new DateTime;
        $purchaseOrder->save();

        return response()->json(['status'=>true, 'message'=>'Purchase Order Updated','response'=>compact('purchaseOrder')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $purchaseOrder = PurchaseOrder::find($id);
        if (empty($purchaseOrder)) {
            return Response()->json(['status'=>false, 'message' => 'Purchase Order is not exists in our system.', 'response' => []], 200);
        }
        $purchaseOrder->delete();
        return response()->json(['status'=>true, 'message'=>'Purchase Order Deleted','response'=>compact('purchaseOrder')], 200);
    }
}
