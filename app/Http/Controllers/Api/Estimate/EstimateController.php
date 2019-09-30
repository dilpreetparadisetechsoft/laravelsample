<?php

namespace App\Http\Controllers\Api\Estimate;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Estimate;
use App\EstimateLogs;
use App\User;
use App\JobStatus;
use App\Services;
use App\Customer;
use App\Kit;
use App\Tax;
use App\Company;
use App\InsuranceCompany;
use App\ChargeCode;
use App\EstimateItem;
use App\EstimateInvoice;
use App\CustomerContactHistory;
use App\Branch;
use App\LeadSource;
use App\Building;       
use App\Locations; 
use App\WorkOrder; 
use App\AssignTask;
use App\AssignJobs;
use App\PurchaseOrder; 
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class EstimateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $currentUser = getApiCurrentUser();   
        $prefix = DB::getTablePrefix();
        $paidAmount = EstimateInvoice::whereRaw($prefix.'estimate_invoice.estimate_id = '.$prefix.'estimate.estimate_id')->select(DB::raw('SUM(collection) as paidAmount'))->limit(1);
        $totalPaidAmount = EstimateInvoice::whereRaw($prefix.'estimate_invoice.estimate_id = '.$prefix.'estimate.estimate_id')->select(DB::raw('SUM(total_collection) as totalPaidAmount'))->limit(1);
        $poAmount = PurchaseOrder::whereRaw($prefix.'purchase_order.estimate_id = '.$prefix.'estimate.estimate_id')->select(DB::raw('SUM(total_amount) as poAmount'))->limit(1);
        $assignTask = AssignTask::whereRaw($prefix.'assign_task.estimate_id = '.$prefix.'estimate.estimate_id')->select('assign_task.start_date')->limit(1);
        $workOrder = WorkOrder::whereRaw($prefix.'work_orders.estimate_id = '.$prefix.'estimate.estimate_id')->select('work_orders.preferred_date')->limit(1);
        $orderKey = (!empty($request->input('orderKey'))?$request->input('orderKey'):'estimate_id');
        $orderBy = (!empty($request->input('orderBy'))?$request->input('orderBy'):'DESC');
        $size = ($request->input('size')?$request->input('size'):dataPerPage());
        $estimates = Estimate::join('users','users.user_id','estimate.user_id')
                    ->join('customer', 'customer.customer_id', 'estimate.customer_id')
                    ->join('services', 'services.serv_id','estimate.serv_id')
                    ->join('job_status', 'job_status.status_id','estimate.status_id')
                    ->select(
                        'estimate.*', 
                        'users.first_name as project_manager', 
                        'customer.customer_name', 
                        'customer.mobile as customer_mobile', 
                        'services.serv_name as service', 
                        'job_status.name as job_status_name',
                        DB::raw("({$assignTask->toSql()}) as inspection_start_date"),
                        DB::raw("({$workOrder->toSql()}) as job_start_date"),
                        DB::raw("({$paidAmount->toSql()}) as paidAmount"),
                        DB::raw("({$totalPaidAmount->toSql()}) as totalPaidAmount"),
                        DB::raw("({$poAmount->toSql()}) as poAmount")
                    )
                    ->where(function ($query) use($currentUser, $request, $prefix){
                        $query->where('estimate.comp_id', $currentUser->comp_id);
                        $start_date = $request->input('start_date');
                        $end_date = $request->input('end_date');
                        $param = $request->input('param');
                        if (!empty($param)) {
                            $query->where('estimate.estimate_id', 'LIKE', '%'.$param.'%');
                        }
                        if (!empty($start_date) && !empty($end_date)) {
                            $query->whereRaw('DATE_FORMAT('.$prefix.'estimate.created_at, "%Y-%m-%d") >= "'.$start_date.'" AND DATE_FORMAT('.$prefix.'estimate.created_at, "%Y-%m-%d")  <= "'.$end_date.'"');
                        }elseif (!empty($start_date)) {
                            $query->whereRaw('DATE_FORMAT('.$prefix.'estimate.created_at, "%Y-%m-%d") = "'.$end_date.'"');
                        }elseif (!empty($end_date)) {
                            $query->whereRaw('DATE_FORMAT('.$prefix.'estimate.created_at, "%Y-%m-%d") = "'.$end_date.'"');
                        }
                        if (!empty($request->input('status'))) {
                            $status = explode(',', $request->input('status'));
                            $query->whereIn('estimate.status_id', $status);
                        }
                        if (!empty($request->input('service_type'))) {
                            $service_type = explode(',', $request->input('service_type'));
                            $query->whereIn('estimate.serv_id', $service_type);   
                        }
                        if (!empty($request->input('branch'))) {                                
                            $branch = explode(',', $request->input('branch'));
                            $query->whereIn('customer.branch_id', $branch);
                        }
                        if (!empty($request->input('location'))) {
                            $location = explode(',', $request->input('location'));
                            $query->whereIn('customer.loc_id', $location);
                        }
                        if (!empty($request->input('project_manager'))) {
                            $project_manager = explode(',', $request->input('project_manager'));
                            $query->whereIn('estimate.user_id', $project_manager);
                        }
                        if (!empty($request->input('invoice'))) {
                            $query->where('estimate.invoice', $request->input('invoice'));
                        }
                        if (!empty($request->input('customer_id'))) {
                            $query->where('estimate.customer_id', $request->input('customer_id'));   
                        }
                    })
                    ->orderBy($orderKey, $orderBy)
                    ->paginate($size);
        $status = $users = $services = $locations = $branchs = [];
        if ($request->input('type') == 'start') {
            $status = JobStatus::where('comp_id', $currentUser->comp_id)->get();
            $users = User::where('comp_id', $currentUser->comp_id)->get();
            $services = Services::where('comp_id', $currentUser->comp_id)->get();
            $locations = Locations::where('comp_id', $currentUser->comp_id)->get();
            $branchs = Branch::where('comp_id', $currentUser->comp_id)->get();
        }
        $invoiceStatus = getSetting($currentUser->comp_id, 'estimate_status_for_create_invoice_list');
        return response()->json(['status'=>true, 'message'=>'All Estimates', 'response'=>compact('estimates','status','users','services','locations','branchs','invoiceStatus')], 200);
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
            'serv_id' => [
                'required',
                Rule::exists('services')->where(function ($query) use($request) {
                    $query->where('serv_id', $request->input('serv_id'));
                }),
            ],
            'area' => 'required',
            'address' => 'required',
            'status_id' => [
                'required',
                Rule::exists('job_status')->where(function ($query) use($request) {
                    $query->where('status_id', $request->input('status_id'));
                }),
            ],
            'amount' => 'required|numeric',
            'discount' => 'numeric',
            'discount_amount' => 'numeric',
            'profit' => 'numeric',
            'profit_val' => 'numeric',
            'overhead' => 'numeric',
            'overhead_val' => 'numeric',
            'cog_val' => 'required',
            'grand_total' => 'required|numeric',
            'note' => 'required|string|max:191',
            'line_item' => 'required',
            'success' => 'required',
            'expected_collection' => 'required',
            'insurance' => 'required|in:yes,no',
            'po_claim' => 'required',
            'building_type' => 'required',
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
        $customer = Customer::find($request->input('customer_id'));
        if (empty($customer)) {
            $customer = new Customer();
        }
        $location = Locations::find($customer->loc_id);
        if (empty($location)) {
            $location = new Locations();
        }
        $tax = tax::find($location->tax_id);
        if (empty($tax)) {
            $tax = new tax();
        }
        $estimate = new Estimate();
        $estimate->comp_id = $currentUser->comp_id;
        $estimate->customer_id = $request->input('customer_id');
        $estimate->user_id = $request->input('user_id');
        $estimate->serv_id = $request->input('serv_id');
        $estimate->tax_id = $tax->tax_id;
        $estimate->area = $request->input('area');
        $estimate->address = $request->input('address');
        $estimate->status_id = $request->input('status_id');
        $estimate->amount = $request->input('amount');
        $estimate->discount = $request->input('discount');
        $estimate->profit = $request->input('profit');
        $estimate->profit_val = $request->input('profit_val');
        $estimate->overhead = $request->input('overhead');
        $estimate->overhead_val = $request->input('overhead_val');
        $estimate->cog_val = $request->input('cog_val');
        $estimate->our_cost_value = $request->input('our_cost_value');
        $estimate->tax = $tax->tax;
        $estimate->sub_total = $request->input('sub_total');
        $estimate->tax_amount = $request->input('tax_amount');
        $estimate->grand_total = $request->input('grand_total');
        $estimate->note = $request->input('note');
        $estimate->line_item = $request->input('line_item');
        $estimate->profit_overhead = $request->input('profit_overhead');
        $estimate->expiry_date = $request->input('expiry_date');
        $estimate->success = $request->input('success');
        $estimate->expected_collection = $request->input('expected_collection');
        $estimate->insurance = $request->input('insurance');
        $estimate->ins_comp_id = $request->input('ins_comp_id');
        $estimate->is_assigned = ($request->input('is_assigned')?$request->input('is_assigned'):1);
        $estimate->estimate_status = 1;
        $estimate->po_claim = $request->input('po_claim');
        $estimate->building_type = $request->input('building_type');
        $estimate->created_by = $currentUser->user_id;
        $estimate->updated_by = $currentUser->user_id;
        $estimate->created_at = new DateTime;
        $estimate->updated_at = new DateTime;
        $estimate->save();

        $estimate_id = $estimate->estimate_id;
        $estimateItems = $request->input('estimateItems');
        if (!empty($estimateItems) && is_array($estimateItems)) {
            foreach ($estimateItems as $item) {
                $estimateItem = new EstimateItem();
                $estimateItem->estimate_id = $estimate_id;
                $estimateItem->chg_code_id = $item['chg_code_id'];
                $estimateItem->comp_id = $currentUser->comp_id;
                $estimateItem->desc = $item['desc'];
                $estimateItem->unit_price = $item['unit_price'];
                $estimateItem->unit_of_measurement = $item['unit_of_measurement'];
                $estimateItem->days = $item['days'];
                $estimateItem->uom = $item['uom'];
                $estimateItem->remarks = $item['remarks'];
                $estimateItem->our_cost = $item['our_cost'];
                $estimateItem->total_charge_code = $item['total_charge_code'];
                $estimateItem->total_our_cost = $item['total_our_cost'];
                $estimateItem->created_by = $currentUser->user_id;
                $estimateItem->updated_by = $currentUser->user_id;
                $estimateItem->created_at = new DateTime;
                $estimateItem->updated_at = new DateTime;
                $estimateItem->save();
            }
        }
        
        return Response()->json(['status'=>true, 'message' => 'Estimate created', 'response' => compact('estimate')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $estimate = Estimate::find($id);
        if (empty($estimate)) {
            return Response()->json(['status'=>false, 'message' => 'Estmate not exists in our system', 'response' => []], 200);            
        }
        $estimateItems = EstimateItem::where('estimate_id', $estimate->estimate_id)->get();
        $currentUser = getApiCurrentUser();
        $jobStatus = JobStatus::where('comp_id', $currentUser->comp_id)->where('status_id', $estimate->status_id)->get()->pluck('name')->first();
        $users = User::where('comp_id', $currentUser->comp_id)->where('user_id',$estimate->user_id)->get()->first();
        $services = Services::where('comp_id', $currentUser->comp_id)->where('serv_id',$estimate->serv_id)->get()->pluck('serv_name')->first();
        $customers = Customer::where('comp_id', $currentUser->comp_id)->where('customer_id',$estimate->customer_id)->get()->first();
        
        $customer = Customer::find($estimate->customer_id);
        if (empty($customer)) {
            $customer = new Customer();
        }
        $location = Locations::find($customer->loc_id);
        if (empty($location)) {
            $location = new Locations();
        }
        $tax = tax::find($location->tax_id);
        if (empty($tax)) {
            $tax = new tax();
        }

        return Response()->json(['status'=>true, 'message' => 'Estimate', 'response' => compact('estimate','jobStatus','users','services','customers','estimateItems','tax')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $estimate = Estimate::find($id);
        if (empty($estimate)) {
            return Response()->json(['status'=>false, 'message' => 'Estmate not exists in our system', 'response' => []], 200);            
        }

        $estimateItems = EstimateItem::where('estimate_id', $estimate->estimate_id)->get();
        $currentUser = getApiCurrentUser();
        $jobStatuss = JobStatus::where('comp_id', $currentUser->comp_id)->get();
        $users = User::where('comp_id', $currentUser->comp_id)->get();
        $services = Services::where('comp_id', $currentUser->comp_id)->get();
        $customers = Customer::where('comp_id', $currentUser->comp_id)->get();
        $kits = Kit::where('comp_id', $currentUser->comp_id)->get();

        $customer = Customer::find($estimate->customer_id);
        if (empty($customer)) {
            $customer = new Customer();
        }
        $location = Locations::find($customer->loc_id);
        if (empty($location)) {
            $location = new Locations();
        }
        $tax = tax::find($location->tax_id);
        if (empty($tax)) {
            $tax = new tax();
        }

        return Response()->json(['status'=>true, 'message' => 'Estimate', 'response' => compact('estimate','jobStatuss','users','services','customers','estimateItems','kits','tax')], 200);
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
        
        $estimate = Estimate::find($id);
        if (empty($estimate)) {
            return Response()->json(['status'=>false, 'message' => 'Estmate not exists in our system', 'response' => []], 200);            
        }
        self::createEstimateLog($estimate);
        $currentUser = getApiCurrentUser();
        $estimate->comp_id = $currentUser->comp_id;
        $estimate->customer_id = $request->input('customer_id');
        $estimate->user_id = $request->input('user_id');
        $estimate->serv_id = $request->input('serv_id');
        $estimate->area = $request->input('area');
        $estimate->address = $request->input('address');
        $estimate->status_id = $request->input('status_id');
        $estimate->amount = $request->input('amount');
        $estimate->discount = $request->input('discount');
        $estimate->profit = $request->input('profit');
        $estimate->profit_val = $request->input('profit_val');
        $estimate->overhead = $request->input('overhead');
        $estimate->overhead_val = $request->input('overhead_val');
        $estimate->cog_val = $request->input('cog_val');
        $estimate->our_cost_value = $request->input('our_cost_value');
        $estimate->tax = $request->input('tax');
        $estimate->sub_total = $request->input('sub_total');
        $estimate->tax_amount = $request->input('tax_amount');
        $estimate->grand_total = $request->input('grand_total');
        $estimate->note = $request->input('note');
        $estimate->line_item = $request->input('line_item');
        $estimate->profit_overhead = $request->input('profit_overhead');
        $estimate->expiry_date = $request->input('expiry_date');
        $estimate->success = $request->input('success');
        $estimate->expected_collection = $request->input('expected_collection');
        $estimate->insurance = $request->input('insurance');
        $estimate->ins_comp_id = $request->input('ins_comp_id');
        $estimate->is_assigned = ($request->input('is_assigned')?$request->input('is_assigned'):1);
        $estimate->estimate_status = 1;
        $estimate->po_claim = $request->input('po_claim');
        $estimate->building_type = $request->input('building_type');
        $estimate->created_by = $currentUser->user_id;
        $estimate->updated_by = $currentUser->user_id;
        $estimate->created_at = new DateTime;
        $estimate->updated_at = new DateTime;
        $estimate->save();

        $estimate_id = $estimate->estimate_id;
        $estimateItems = $request->input('estimateItems');
        $estimate_item_ids = [];
        if (!empty($estimateItems) && is_array($estimateItems)) {
            foreach ($estimateItems as $item) {
                if (isset($item['estimate_item_id']) && !empty($item['estimate_item_id'])) {
                    $estimateItem = EstimateItem::where('estimate_id',$item['estimate_item_id'])->get()->first();
                    if (!$estimateItem) {
                        $estimateItem = new EstimateItem();
                        $estimateItem->estimate_id = $estimate_id;
                        $estimateItem->created_by = $currentUser->user_id;
                        $estimateItem->updated_at = new DateTime;
                    }
                } else {
                    $estimateItem = new EstimateItem();
                    $estimateItem->estimate_id = $estimate_id;
                    $estimateItem->created_by = $currentUser->user_id;
                    $estimateItem->updated_at = new DateTime;
                }
                
                $estimateItem->chg_code_id = $item['chg_code_id'];
                $estimateItem->comp_id = $currentUser->comp_id;
                $estimateItem->desc = $item['desc'];
                $estimateItem->unit_price = $item['unit_price'];
                $estimateItem->unit_of_measurement = $item['unit_of_measurement'];
                $estimateItem->days = $item['days'];
                $estimateItem->uom = $item['uom'];
                $estimateItem->remarks = $item['remarks'];
                $estimateItem->our_cost = $item['our_cost'];
                $estimateItem->total_charge_code = $item['total_charge_code'];
                $estimateItem->total_our_cost = $item['total_our_cost'];
                $estimateItem->updated_by = $currentUser->user_id;
                $estimateItem->created_at = new DateTime;
                $estimateItem->save();
                $estimate_item_ids[] = $estimateItem->estimate_item_id;
            }
        }
        EstimateItem::whereNotIn('estimate_item_id', $estimate_item_ids)->where('estimate_id',$estimate_id)->delete();
        
        if ($request->input('invoice') == true && $request->input('invoice_amount') > 0) {
            self::createInvoice($currentUser, $estimate_id, $request);
        }
        
        return Response()->json(['status'=>true, 'message' => 'Estimate Updated', 'response' => compact('estimate')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $estimate = Estimate::find($id);
        if (empty($estimate)) {
            return Response()->json(['status'=>false, 'message' => 'Estmate not exists in our system', 'response' => []], 200);            
        }
        $estimate->delete();
        return Response()->json(['status'=>true, 'message' => 'Estimate Deleted', 'response' => compact('estimate')], 200);
    }
    public function getEstimateHelperData()
    {
        $currentUser = getApiCurrentUser();
        $jobStatuss = JobStatus::where('comp_id', $currentUser->comp_id)->select('status_id','name')->get();
        $users = User::where('comp_id', $currentUser->comp_id)->select('user_id','first_name')->get();
        $services = Services::where('comp_id', $currentUser->comp_id)->select('serv_id','serv_name')->get();
        $customers = Customer::where('comp_id', $currentUser->comp_id)->select('customer_id','customer_name')->get();
        $kits = Kit::where('comp_id', $currentUser->comp_id)->select('kit_id','kit_name','chg_code_id')->get();
        $chargeCodes = ChargeCode::where('comp_id', $currentUser->comp_id)->select('chg_code_id','chg_code_name')->get();
        $insuranceCompanies = InsuranceCompany::where('comp_id', $currentUser->comp_id)->select('ins_comp_id','ins_comp_name')->get();
        return Response()->json(['status'=>true, 'message' => 'Estimate', 'response' => compact('jobStatuss','users','services','customers','kits','insuranceCompanies','chargeCodes')], 200);
    }
    public function getChargeCodes(Request $request)
    {
        $currentUser = getApiCurrentUser();
        $triggerChange = $request->input('triggerChange');
        $chg_code_id = $request->input('chg_code_id');
        $customer_id = $request->input('customer_id');
        $chg_code_ids = []; 
        if ($triggerChange == "kit") {
            $chg_code_ids = explode(',',$request->input('chg_code_ids'));
        }elseif ($triggerChange == "charge_code") {
            $chg_code_ids[] = $chg_code_id;
        }
        $chg_code_ids = array_unique($chg_code_ids);
        $chargeItems = ChargeCode::whereIn('chg_code_id', $chg_code_ids)->get();

        $customer = Customer::find($customer_id);
        if (empty($customer)) {
            $customer = new Customer();
        }
        $location = Locations::find($customer->loc_id);
        if (empty($location)) {
            $location = new Locations();
        }
        $tax = tax::find($location->tax_id);
        if (empty($tax)) {
            $tax = new tax();
        }

        return Response()->json(['status'=>true, 'message' => 'Estimate', 'response' => compact('chargeItems','tax')], 200);
    }
	
	public function wip(Request $request)
    {
        $currentUser = getApiCurrentUser();
        $wipStatus = getSetting($currentUser->comp_id, 'wp_estimate');

        $prefix = DB::getTablePrefix();
        $paidAmount = EstimateInvoice::whereRaw($prefix.'estimate_invoice.estimate_id = '.$prefix.'estimate.estimate_id')->select(DB::raw('SUM(collection) as paidAmount'))->limit(1);
        $invoiceAmount = EstimateInvoice::whereRaw($prefix.'estimate_invoice.estimate_id = '.$prefix.'estimate.estimate_id')->select(DB::raw('SUM(invoice_amount) as paidAmount'))->limit(1);
        $poAmount = PurchaseOrder::whereRaw($prefix.'purchase_order.estimate_id = '.$prefix.'estimate.estimate_id')->select(DB::raw('SUM(total_amount) as poAmount'))->limit(1);
        $wordOrderDays = WorkOrder::whereRaw($prefix.'work_orders.estimate_id = '.$prefix.'estimate.estimate_id')->select(DB::raw('SUM(total_days) as wordOrderDays'))->limit(1);
        $assignJobDays = AssignJobs::whereRaw($prefix.'assign_jobs.estimate_id = '.$prefix.'estimate.estimate_id')->select(DB::raw('SUM(total_days) as assignJobDays'))->limit(1);
        $estimateItemsDays = EstimateItem::whereRaw($prefix.'estimate_item.estimate_id = '.$prefix.'estimate.estimate_id')->select(DB::raw('SUM(days) as estimateItemsDays'))->limit(1);
        $assignTask = AssignTask::whereRaw($prefix.'assign_task.estimate_id = '.$prefix.'estimate.estimate_id')->select('assign_task.start_date')->limit(1);
        
        $orderKey = (!empty($request->input('orderKey'))?$request->input('orderKey'):'estimate_id');
        $orderBy = (!empty($request->input('orderBy'))?$request->input('orderBy'):'DESC');
        $size = ($request->input('size')?$request->input('size'):dataPerPage());

        $estimates = Estimate::join('users','users.user_id','estimate.user_id')
                    ->join('customer', 'customer.customer_id', 'estimate.customer_id')
                    ->join('services', 'services.serv_id','estimate.serv_id')
                    ->join('users as updated_by_user','updated_by_user.user_id','estimate.updated_by')
                    ->select(
                        'estimate.*', 
                        'users.first_name as project_manager', 
                        'updated_by_user.first_name as updated_by_name', 
                        'customer.customer_name', 
                        'customer.mobile as customer_mobile', 
                        'services.serv_name as service', 
                        DB::raw('DATEDIFF(CURDATE(), DATE('.$prefix.'estimate.updated_at)) as days'),
                        DB::raw("({$assignTask->toSql()}) as job_assign_start_date"),
                        DB::raw("({$paidAmount->toSql()}) as paidAmount"),
                        DB::raw("({$poAmount->toSql()}) as poAmount"),
                        DB::raw("({$wordOrderDays->toSql()}) as wordOrderDays"),
                        DB::raw("({$assignJobDays->toSql()}) as assignJobDays"),
                        DB::raw("({$estimateItemsDays->toSql()}) as estimateItemsDays"),
                        DB::raw("({$invoiceAmount->toSql()}) as invoiceAmount")
                    )
                    ->where(function ($query) use($currentUser, $request, $prefix, $wipStatus){
                        $query->where('estimate.comp_id', $currentUser->comp_id);
                        $query->where('estimate.status_id', $wipStatus);
                        $start_date = $request->input('start_date');
                        $end_date = $request->input('end_date');
        
                        if (!empty($start_date) && !empty($end_date)) {
                            $query->whereRaw('DATE_FORMAT('.$prefix.'estimate.created_at, "%Y-%m-%d") >= "'.$start_date.'" AND DATE_FORMAT('.$prefix.'estimate.created_at, "%Y-%m-%d")  <= "'.$end_date.'"');
                        }elseif (!empty($start_date)) {
                            $query->whereRaw('DATE_FORMAT('.$prefix.'estimate.created_at, "%Y-%m-%d") = "'.$end_date.'"');
                        }elseif (!empty($end_date)) {
                            $query->whereRaw('DATE_FORMAT('.$prefix.'estimate.created_at, "%Y-%m-%d") = "'.$end_date.'"');
                        }

                        if (!empty($request->input('project_manager'))) {
                            $project_manager = explode(',', $request->input('project_manager'));
                            $query->whereIn('estimate.user_id', $project_manager);
                        }
                        if (!empty($request->input('invoice'))) {
                            $query->where('estimate.invoice', $request->input('invoice'));
                        }
                    })
                    ->orderBy($orderKey, $orderBy)
                    ->paginate($size);
        $users = [];
        if ($request->input('type') == 'start') {
            $users = User::where('comp_id', $currentUser->comp_id)->get();
        }
        return response()->json(['status'=>true, 'message'=>'All Work in Progress Estimate', 'response'=>compact('estimates','users')], 200);
    }
	public function customerEstimate($customer_id = null)
    {
        if (empty($customer_id)) {
            return response()->json(['status'=>false, 'message'=>'Customer is not exists in our system', 'response'=>[]], 200);       
        }        

        $prefix = DB::getTablePrefix();
        $branch = Branch::whereRaw($prefix.'branch.branch_id = '.$prefix.'customer.branch_id')->select('branch_name')->limit(1);
        $job_status = JobStatus::whereRaw($prefix.'job_status.status_id = '.$prefix.'customer.status_id')->select('name')->limit(1);
        $lead_source = LeadSource::whereRaw($prefix.'lead_source.lead_id = '.$prefix.'customer.lead_id')->select('name')->limit(1);
        $service = Services::whereRaw($prefix.'services.serv_id = '.$prefix.'customer.serv_id')->select('serv_name')->limit(1);

        $customer = Customer::select('*', DB::raw("({$branch->toSql()}) as branch_name"), DB::raw("({$job_status->toSql()}) as job_name"), DB::raw("({$lead_source->toSql()}) as lead_name"), DB::raw("({$service->toSql()}) as serv_name"))->where('customer_id', $customer_id)->get()->first();
        if (empty($customer)) {
            return response()->json(['status'=>false, 'message'=>'Customer is not exists in our system', 'response'=>[]], 200);       
        }

        $location = Locations::where('loc_id', $customer->loc_id)->get()->first();

        $tax = Tax::where('tax_id', $location->tax_id)->get()->first();

        $estimate = Estimate::where('customer_id', $customer->customer_id)->select(DB::raw('COUNT(*) as totalEstimate'), DB::raw('SUM(grand_total) as totalAmount'))->get()->first();
        $workOrder = WorkOrder::where('customer_id', $customer->customer_id)->where('customer_id', $customer->customer_id)->count();
        $purchaseOrder = PurchaseOrder::where('customer_id', $customer->customer_id)->where('customer_id', $customer->customer_id)->count();

        return response()->json(['status'=>true, 'message'=>'', 'response'=>compact('customer', 'estimate', 'location','workOrder','purchaseOrder','tax')], 200);
    }
    public function generatePDF($estimate_id = 0, $uuid = null, $type = 'view')
    {
        $estimate = Estimate::find($estimate_id);
        if (empty($estimate)) {
            return Response()->json(['status'=>false, 'message' => 'Estmate not exists in our system', 'response' => []], 200);            
        }

        $estimateItems = EstimateItem::where('estimate_id', $estimate->estimate_id)->get();
        $company = Company::find($estimate->comp_id);
        $user = User::find($estimate->user_id);
        $customer = Customer::find($estimate->customer_id);
        $services = Services::find($estimate->serv_id);
        if (empty($services)) {
            $services = new Services();
        }
        $title ="Estimate";
        
        $tax = Tax::find($estimate->tax_id);
        if (empty($tax)) {
            $tax = new Tax();
        }
        return estimatePDF($estimate, $estimateItems, $company, $user, $customer, $services, $title, $type, $tax);
    }
    public function estimateSendEmail(Request $request)
    {
        $currentUser = getApiCurrentUser(); 
        
        $pdfUrl = self::generatePDF($request->input('estimate_id'), $currentUser->uuid, 'download');

        $emailTo = $request->input('email_to');
        $email_cc = $request->input('email_cc');
        $email_bcc = $request->input('email_bcc');
        $EmailSubject = $request->input('email_subject');
        $emailBody = $request->input('email_content');
        $attachment = explode('^', $request->input('email_attachments'));

        if ($request->file('email_attachment_file') != '') {
            $attachment[] = fileuploadExtra($request, 'email_attachment_file');
        }
        $attachments = [];
        foreach ($attachment as $file) {
            $attachments[] = asset($file);
        }
        $attachments[] = $pdfUrl;
        $emailFromName = $currentUser->first_name;
        $emailFromEmail ='CS@Canadarestorationservices.com';

        $uuid = createUuid('customer_contact_history-'.date('YmdHis'));
        $cust_cont_history_sno = $uuid->string;
        $customerContactHistory = new CustomerContactHistory();
        $customerContactHistory->cust_cont_history_sno = $cust_cont_history_sno;
        $customerContactHistory->comp_id = $currentUser->comp_id;
        $customerContactHistory->customer_id = $request->input('customer_id');
        $customerContactHistory->communication_mode = 'Email';
        $customerContactHistory->note = strip_tags($emailBody);
        $customerContactHistory->contact_date = date('Y-m-d');
        $customerContactHistory->contact_time = date('H:i:s');
        $customerContactHistory->status = 1;        
        $customerContactHistory->created_by = $currentUser->user_id;
        $customerContactHistory->created_at = new DateTime;
        $customerContactHistory->updated_at = new DateTime;
        $customerContactHistory->updated_by = $currentUser->user_id;
        $customerContactHistory->save();

        SendEmail($emailTo, $EmailSubject, $emailBody, $attachments, $emailFromName, $emailFromEmail, $email_cc, $email_bcc);
        return response()->json(['status'=>true, 'message'=>'Mail Sent successfully.', 'response'=>[]], 200);

    }
    public static function createEstimateLog($estimate)
    {
        $estimateLogs = new EstimateLogs();
        $estimateLogs->estimate_id = $estimate->estimate_id;
        $estimateLogs->customer_id = $estimate->customer_id;
        $estimateLogs->comp_id = $estimate->comp_id;
        $estimateLogs->user_id = $estimate->user_id;
        $estimateLogs->serv_id = $estimate->serv_id;
        $estimateLogs->tax_id = $estimate->tax_id;
        $estimateLogs->estimate_sno = $estimate->estimate_sno;
        $estimateLogs->area = $estimate->area;
        $estimateLogs->address = $estimate->address;
        $estimateLogs->status_id = $estimate->status_id;
        $estimateLogs->invoice = $estimate->invoice;
        $estimateLogs->amount = $estimate->amount;
        $estimateLogs->our_cost_value = $estimate->our_cost_value;
        $estimateLogs->discount = $estimate->discount;
        $estimateLogs->discount_amount = $estimate->discount_amount;
        $estimateLogs->profit = $estimate->profit;
        $estimateLogs->profit_val = $estimate->profit_val;
        $estimateLogs->overhead = $estimate->overhead;
        $estimateLogs->overhead_val = $estimate->overhead_val;
        $estimateLogs->profit_overhead = $estimate->profit_overhead;
        $estimateLogs->cog_val = $estimate->cog_val;
        $estimateLogs->tax = $estimate->tax;
        $estimateLogs->sub_total = $estimate->sub_total;
        $estimateLogs->tax_amount = $estimate->tax_amount;
        $estimateLogs->grand_total = $estimate->grand_total;
        $estimateLogs->complete_val = $estimate->complete_val;
        $estimateLogs->note = $estimate->note;
        $estimateLogs->line_item = $estimate->line_item;
        $estimateLogs->expiry_date = $estimate->expiry_date;
        $estimateLogs->success = $estimate->success;
        $estimateLogs->expected_collection = $estimate->expected_collection;
        $estimateLogs->insurance = $estimate->insurance;
        $estimateLogs->ins_comp_id = $estimate->ins_comp_id;
        $estimateLogs->is_assigned = $estimate->is_assigned;
        $estimateLogs->estimate_status = $estimate->estimate_status;
        $estimateLogs->po_claim = $estimate->po_claim;
        $estimateLogs->building_type = $estimate->building_type;
        $estimateItems = EstimateItem::where('estimate_id', $estimate->estimate_id)->get()->toArray();
        $estimateLogs->estimate_items_log = maybe_encode($estimateItems);
        $estimateLogs->created_by = $estimate->user_id;
        $estimateLogs->updated_by = $estimate->user_id;
        $estimateLogs->created_at = $estimate->created_at;
        $estimateLogs->updated_at = $estimate->created_at;
        $estimateLogs->save();
    }
    public function estimateHistoryPreviews($estimate_id)
    {
        $currentUser = getApiCurrentUser();   
        $prefix = DB::getTablePrefix();
        $paidAmount = EstimateInvoice::whereRaw($prefix.'estimate_invoice.estimate_id = '.$prefix.'estimate_log.estimate_id')->select(DB::raw('SUM(collection) as paidAmount'))->limit(1);

        $estimates = EstimateLogs::join('users','users.user_id','estimate_log.user_id')
                    ->join('customer', 'customer.customer_id', 'estimate_log.customer_id')
                    ->join('services', 'services.serv_id','estimate_log.serv_id')
                    ->join('job_status', 'job_status.status_id','estimate_log.status_id')
                    ->select(
                        'estimate_log.*', 
                        'users.first_name as project_manager', 
                        'customer.customer_name', 
                        'customer.mobile as customer_mobile', 
                        'services.serv_name as service', 
                        'job_status.name as job_status_name',
                        DB::raw("({$paidAmount->toSql()}) as paidAmount")
                    )
                    ->where(function ($query) use($currentUser, $estimate_id){
                        $query->where('estimate_log.comp_id', $currentUser->comp_id);
                        $query->where('estimate_log.estimate_id', $estimate_id);
                        
                    })
                    ->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Estimates', 'response'=>compact('estimates')], 200);
    }
    public function estimateHistoryDetails($estimate_log_id)
    {
        $estimate = EstimateLogs::find($estimate_log_id);
        if (empty($estimate)) {
            return Response()->json(['status'=>false, 'message' => 'Estmate not exists in our system', 'response' => []], 200);            
        }

        $estimateItems = maybe_decode($estimate->estimate_items_log);
        $currentUser = getApiCurrentUser();
        $jobStatus = JobStatus::where('comp_id', $currentUser->comp_id)->where('status_id', $estimate->status_id)->get()->pluck('name')->first();
        $users = User::where('comp_id', $currentUser->comp_id)->where('user_id',$estimate->user_id)->get()->first();
        $services = Services::where('comp_id', $currentUser->comp_id)->where('serv_id',$estimate->serv_id)->get()->pluck('serv_name')->first();
        $customers = Customer::where('comp_id', $currentUser->comp_id)->where('customer_id',$estimate->customer_id)->get()->first();
        
        $customer = Customer::find($estimate->customer_id);
        if (empty($customer)) {
            $customer = new Customer();
        }
        $location = Locations::find($customer->loc_id);
        if (empty($location)) {
            $location = new Locations();
        }
        $tax = tax::find($location->tax_id);
        if (empty($tax)) {
            $tax = new tax();
        }
        return Response()->json(['status'=>true, 'message' => 'Estimate', 'response' => compact('estimate','jobStatuss','users','services','customers','estimateItems','kits','tax')], 200);
    }
    public function estimateUpdateStatus(Request $request)
    {

        $estimate_id = $request->input('estimate_id');
        $invoice_amount = $request->input('invoice_amount');
        $estimate_assign_task = $request->input('estimate_assign_task');
        $estimate = Estimate::find($estimate_id);
        if (empty($estimate)) {
            return Response()->json(['status'=>false, 'message' => 'Estmate not exists in our system', 'response' => []], 200);            
        }
        $estimate->status_id = $request->input('status_id');
        $estimate->save();
        $currentUser = getApiCurrentUser();
        $invoiceStatus = (array)getSetting($currentUser->comp_id, 'estimate_status_for_create_invoice_list');
        if (!empty($invoice_amount) && in_array($request->input('status_id'), $invoiceStatus)) {
            $request->merge(['sub_total' => $estimate->sub_total]);
            $request->merge(['customer_id' => $estimate->customer_id]);
            $request->merge(['expiry_date' => $estimate->expiry_date]);
            self::createInvoice($currentUser, $estimate_id, $request);
        }
        if (!empty($estimate_assign_task)) {
            self::createTask($currentUser, $estimate, $request);
        }
        return Response()->json(['status'=>true, 'message' => 'Estmate status updated successfully', 'response' => []], 200);    

    }
    public static function createTask($currentUser, $estimate, $request)
    {
        $assignTask = new AssignTask();
        $uuid = createUuid('Estimate Task'.date('YmdHis'));
        $task_sno = $uuid->string;
        $assignTask->task_sno = $task_sno;
        $assignTask->comp_id = $currentUser->comp_id;
        $assignTask->customer_id = $estimate->customer_id;    
        $assignTask->estimate_id = $estimate->estimate_id;
        $assignTask->title = 'Estimate Task';
        $assignTask->representative = $currentUser->user_id;
        $assignTask->start_date = date('Y-m-d');
        $assignTask->due_date = date('Y-m-d', strtotime("+1 day"));
        $assignTask->level = 'Urgent';
        $assignTask->notes = 'Estimate Task';
        $assignTask->unit = 0;
        $assignTask->guest_email = 'Null';
        $assignTask->inspection = (empty($request->input('inspection'))?0:1);
        $assignTask->customer_check = (empty($request->input('customer_check'))?0:1);
        $assignTask->status = 'Waiting';
        $assignTask->created_by = $currentUser->user_id;
        $assignTask->created_at = new DateTime;
        $assignTask->updated_at = new DateTime;
        $assignTask->updated_by = $currentUser->user_id;
        $assignTask->save();  

        $emailTo = 'finance@canadarestorationservices.com';
        $emailFromName = $currentUser->first_name;
        $emailFromEmail ='CS@Canadarestorationservices.com';
        $EmailSubject = " Assigned Task  -Canada's Restoration Services ";
        $emailBody =" Dear, Finance <br/> A new task assign by ".$currentUser->first_name . " for this Estimate # ". $estimate->estimate_id .".<br/> Thanks and Regards <br/> Bondcrm";
        SendEmail($emailTo, $EmailSubject, $emailBody, [], $emailFromName, $emailFromEmail);
    }
    public static function createInvoice($currentUser, $estimate_id, $request)
    {
        $invoiceAmount = $request->input('sub_total') * $request->input('invoice_amount') / 100;
        $estimateInvoice = new EstimateInvoice();
        $estimateInvoice->comp_id = $currentUser->comp_id;
        $estimateInvoice->estimate_id = $estimate_id;
        $estimateInvoice->customer_id = $request->input('customer_id');
        $estimateInvoice->invoice_amount = $invoiceAmount;
        $estimateInvoice->invoice_no = $estimate_id;
        $estimateInvoice->invoice_date = date('Y-m-d H:i:s');
        $estimateInvoice->invoice_status = 'Unpaid';
        $estimateInvoice->payment_status = 'Unpaid';        
        if ($request->input('expiry_date')) {
            $estimateInvoice->due_date = $request->input('expiry_date');
        }        
        $estimateInvoice->created_by = $currentUser->user_id;
        $estimateInvoice->updated_by = $currentUser->user_id;
        $estimateInvoice->created_at = new DateTime;
        $estimateInvoice->updated_at = new DateTime;
        $estimateInvoice->save();

        $estimateCount = EstimateInvoice::where('estimate_id', $estimate_id)->get()->count();

        $estimateInvoice->invoice_no = $estimate_id.'-'.($estimateCount+1);
        $estimateInvoice->save();
    }
}
