<?php

namespace App\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;
use App\User;
use App\Company;
use App\AssignTask;
use App\Estimate;
use App\Roles;
use App\JobStatus;
use App\Branch;
use App\Events;
use App\Customer;
use App\EmailTemplate;
use App\EmailAttachment;
use App\Services;
use App\EstimateInvoice;
use App\EstimateInvoiceLogs;
use App\CustomerContactHistory;

class FinanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexInvoices($customer_id = null)
    {
        $currentUser = getApiCurrentUser();       
        $prefix = DB::getTablePrefix();
        $whereRawQuery = $prefix.'estimate_invoice_logs.estimate_invoice_id = '.$prefix.'estimate_invoice.estimate_invoice_id';

        $totalBadDebits = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action != "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.amount), 0) AS totalBadAmount'))
                            ->limit(1);

        $totalPaidAmount = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action != "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.amount), 0) AS totalAmount'))
                            ->limit(1);

        $totalTax = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action = "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.tax), 0) AS totalTax'))
                            ->limit(1);
        
        $totalCollections = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action != "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.total_collection), 0) AS totalCollection'))
                            ->limit(1);

        $totalInvoiceAmount = DB::table('estimate_invoice AS EI')->whereRaw($prefix.'EI.estimate_id = '.$prefix.'estimate_invoice.estimate_id')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'EI.invoice_amount), 0) AS totalInvoiceAmount'));
        $orderKey = (!empty($request->input('orderKey'))?$request->input('orderKey'):'estimate_id');
        $orderBy = (!empty($request->input('orderBy'))?$request->input('orderBy'):'DESC');
        $size = ($request->input('size')?$request->input('size'):dataPerPage());   
        $param = $request->input('param');                 
        $invoices = EstimateInvoice::join('estimate', 'estimate.estimate_id', 'estimate_invoice.estimate_id')
                    ->leftjoin('tax', 'tax.tax_id', 'estimate.tax_id')
                    ->join('job_status', 'job_status.status_id', 'estimate.status_id')
                    ->where(function ($query) use($customer_id,$param){
                        if ($customer_id) {
                            $query->where('estimate_invoice.customer_id', $customer_id);    
                        } 

                        if (!empty($param)) {
                            $query->where('estimate_invoice.invoice_amount', 'LIKE', '%'.$param.'%');
                        }                      
                    })
                    ->where('estimate.comp_id', $currentUser->comp_id)
                    ->select(
                        'estimate_invoice.*', 
                        'estimate.grand_total',
                        'estimate.sub_total',
                        'estimate.estimate_status',
                        'tax.name AS tax_name',
                        'tax.tax AS tax_percentage',
                        'job_status.name as job_status_name',
                        DB::raw("({$totalBadDebits->toSql()}) as totalBadDebits"),
                        DB::raw("({$totalPaidAmount->toSql()}) as totalPaidAmount"),
                        DB::raw("({$totalTax->toSql()}) as totalTax"),
                        DB::raw("({$totalCollections->toSql()}) as totalCollections"),
                        DB::raw("({$totalInvoiceAmount->toSql()}) as totalInvoiceAmount")
                    )
                    ->paginate(dataPerPage());

        return response()->json(['status'=>true, 'message'=>'All Invoices', 'response'=>compact('invoices')], 200);
    }

    public function index(Request $request)
    {
        $currentUser = getApiCurrentUser();       
        $prefix = DB::getTablePrefix();
        $whereRawQuery = $prefix.'estimate_invoice_logs.estimate_invoice_id = '.$prefix.'estimate_invoice.estimate_invoice_id';

        $totalBadDebits = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action != "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.amount), 0) AS totalBadAmount'))
                            ->limit(1);

        $totalPaidAmount = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action != "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.amount), 0) AS totalAmount'))
                            ->limit(1);

        $totalTax = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action = "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.tax), 0) AS totalTax'))
                            ->limit(1);
        
        $totalCollections = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action != "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.total_collection), 0) AS totalCollection'))
                            ->limit(1);

        $totalInvoiceAmount = DB::table('estimate_invoice AS EI')->whereRaw($prefix.'EI.estimate_id = '.$prefix.'estimate_invoice.estimate_id')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'EI.invoice_amount), 0) AS totalInvoiceAmount'));
        
        $orderKey = (!empty($request->input('orderKey'))?$request->input('orderKey'):'estimate_id');
        $orderBy = (!empty($request->input('orderBy'))?$request->input('orderBy'):'DESC');
        $size = ($request->input('size')?$request->input('size'):dataPerPage()); 

        $invoices = EstimateInvoice::join('estimate', 'estimate.estimate_id', 'estimate_invoice.estimate_id')
                    ->leftjoin('tax', 'tax.tax_id', 'estimate.tax_id')
                    ->join('job_status', 'job_status.status_id', 'estimate.status_id')
                    ->join('customer', 'estimate_invoice.customer_id', 'customer.customer_id')
                    ->where('estimate.comp_id', $currentUser->comp_id)
                    ->where(function ($query) use($request){
                        $start_date = $request->input('start_date');
                        $end_date = $request->input('end_date');
                        $param = $request->input('param');    
                        if (!empty($param)) {
                            $query->where('estimate_invoice.invoice_no', 'LIKE', '%'.$param.'%');
                        }
                        if (!empty($start_date) && !empty($end_date)) {
                            $query->whereRaw('DATE_FORMAT('.$prefix.'estimate_invoice.created_at, "%Y-%m-%d") >= "'.$start_date.'" AND DATE_FORMAT('.$prefix.'estimate_invoice.created_at, "%Y-%m-%d")  <= "'.$end_date.'"');
                        }elseif (!empty($start_date)) {
                            $query->whereRaw('DATE_FORMAT('.$prefix.'estimate_invoice.created_at, "%Y-%m-%d") = "'.$end_date.'"');
                        }elseif (!empty($end_date)) {
                            $query->whereRaw('DATE_FORMAT('.$prefix.'estimate_invoice.created_at, "%Y-%m-%d") = "'.$end_date.'"');
                        }
                        if (!empty($request->input('status'))) {
                            $status = explode(',', $request->input('status'));
                            $query->whereIn('customer.status_id', $status);
                        }              
                        if (!empty($request->input('branch'))) {                                
                            $branch = explode(',', $request->input('branch'));
                            $query->whereIn('customer.branch_id', $branch);
                        }
                        if (!empty($request->input('project_manager'))) {
                            $project_manager = explode(',', $request->input('project_manager'));
                            $query->whereIn('customer.user_id', $project_manager);
                        }
                        
                    })
                    ->select(
                        'estimate_invoice.*', 
                        'estimate.grand_total',
                        'estimate.sub_total',
                        'estimate.estimate_status',
                        'tax.name AS tax_name',
                        'tax.tax AS tax_percentage',
                        'job_status.name as job_status_name',
                        'customer.customer_name',
                        DB::raw('('.$prefix.'estimate_invoice.invoice_amount-'.$prefix.'estimate_invoice.collection-('.$prefix.'estimate_invoice.invoice_amount*'.$prefix.'estimate_invoice.discount)/100) as balance'),
                        DB::raw("({$totalBadDebits->toSql()}) as totalBadDebits"),
                        DB::raw("({$totalPaidAmount->toSql()}) as totalPaidAmount"),
                        DB::raw("({$totalTax->toSql()}) as totalTax"),
                        DB::raw("({$totalCollections->toSql()}) as totalCollections"),
                        DB::raw("({$totalInvoiceAmount->toSql()}) as totalInvoiceAmount")
                    );
                    if ($request->input('balance') == '1') {
                        $invoices = $invoices->havingRaw('balance > 0');
                    }elseif ($request->input('balance') == '0') {
                        $invoices = $invoices->havingRaw('balance <= 0');
                    }

                    $invoices = $invoices->orderBy($orderKey, $orderBy)->paginate($size);
                    

        $status = $users = $services = $branchs = [];
        if ($request->input('type') == 'start') {
            $status = JobStatus::where('comp_id', $currentUser->comp_id)->get();
            $users = User::where('comp_id', $currentUser->comp_id)->get();
            $services = Services::where('comp_id', $currentUser->comp_id)->get();
            $branchs = Branch::where('comp_id', $currentUser->comp_id)->get();
        }
        return response()->json(['status'=>true, 'message'=>'All Invoices', 'response'=>compact('invoices','status','users','services','branchs')], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $currentUser = getApiCurrentUser();        
        $prefix = DB::getTablePrefix();
        $totalInvoiceAmount = EstimateInvoice::whereRaw('`'.$prefix.'estimate_invoice`.`estimate_id` = `'.$prefix.'estimate`.`estimate_id`'
                            )
                            ->select(DB::raw('IFNULL(SUM(invoice_amount), 0) as amount'))
                            ->limit(1);

        $estimates = Estimate::where('estimate.comp_id', $currentUser->comp_id)
                        ->where("estimate.sub_total" ,">", DB::raw("(".$totalInvoiceAmount->toSql().")"))
                        ->select('estimate.estimate_id')->get();

        return response()->json(['status'=>true, 'message'=>'All Estimates', 'response'=>compact('estimates')], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function storeRules($request)
    {
        return [
            'customer_id' => [
                'required',
                Rule::exists('customer')->where(function ($query) use($request) {
                    $query->where('customer_id', $request->input('customer_id'));
                }),                
            ],
            'estimate_id' => [
                'required',
                Rule::exists('estimate')->where(function ($query) use($request) {
                    $query->where('estimate_id', $request->input('estimate_id'));
                }),                
            ],  
            'invoice_amount' => 'required|numeric',
            'due_date' => 'required|date_format:Y-m-d'
        ];
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $invoiceTotalAmount = EstimateInvoice::where('estimate_id', $request->input('estimate_id'))
                    ->select(DB::raw('SUM(invoice_amount) AS totalAmount'))
                    ->get()->pluck('totalAmount')->first();

        $estimateTotalAmount = Estimate::where('estimate_id', $request->input('estimate_id'))->get()->pluck('sub_total')->first();
        if ($invoiceTotalAmount >= $estimateTotalAmount) {
            return Response()->json(['status'=>false, 'message' => 'Invoice amount should less than net Estimate amount ', 'response' => []], 200);
        }

        $invoiceTotalAmount = $invoiceTotalAmount + $request->input('invoice_amount');

        if ($invoiceTotalAmount > $estimateTotalAmount) {
            return Response()->json(['status'=>false, 'message' => 'Invoice amount should less than net Estimate amount ', 'response' => []], 200);
        }          
        
        $currentUser = getApiCurrentUser();
        $estimateInvoice = new EstimateInvoice();
        $estimateInvoice->comp_id = $currentUser->comp_id;
        $estimateInvoice->estimate_id = $request->input('estimate_id');
        $estimateInvoice->customer_id = $request->input('customer_id');
        $estimateInvoice->invoice_amount = $request->input('invoice_amount');
        $estimateInvoice->invoice_no = $request->input('estimate_id');
        $estimateInvoice->invoice_date = date('Y-m-d H:i:s');
        $estimateInvoice->invoice_status = 'Unpaid';
        $estimateInvoice->payment_status = 'Unpaid';        
        if ($request->input('due_date')) {
            $estimateInvoice->due_date = $request->input('due_date');
        }        
        $estimateInvoice->created_by = $currentUser->user_id;
        $estimateInvoice->updated_by = $currentUser->user_id;
        $estimateInvoice->created_at = new DateTime;
        $estimateInvoice->updated_at = new DateTime;
        $estimateInvoice->save();

        $estimateCount = EstimateInvoice::where('estimate_id', $request->input('estimate_id'))->get()->count();

        $estimateInvoice->invoice_no = $request->input('estimate_id').'-'.($estimateCount+1);
        $estimateInvoice->save();
        
        return self::index($estimateInvoice->customer_id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoice = self::getInvoice($id);
        if (empty($invoice)) {
            return response()->json(['status'=>false, 'message'=>'Invoice is not exist in our system.', 'response'=>[]], 200);       
        }
        $paymentStatus = [
            'Paid'=> 'Full Payment',
            'Bad Debt' => 'Bad Debt',
            'Discount' => 'Discount',
            'Partially'=>'Partially', 
        ];
        return response()->json(['status'=> true, 'message' => 'Invoice and invoice logs details', 'response' => compact('invoice','paymentStatus')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $currentUser = getApiCurrentUser(); 
        $invoice = self::getInvoice($id);
        if (empty($invoice)) {
            return response()->json(['status'=>false, 'message'=>'Invoice is not exist in our system.', 'response'=>[]], 200);       
        }
        $paymentStatus = [
            'Paid' => 'Full Payment',
            'Bad Debt' => 'Bad Debt',
            'Discount' => 'Discount',
            'Partially' =>'Partially', 
        ];
        $invoiceLogs = EstimateInvoiceLogs::where('estimate_invoice_id', $invoice->estimate_invoice_id)->get();
        return response()->json(['status'=> true, 'message' => 'Invoice and invoice logs details', 'response' => compact('invoice', 'invoiceLogs','paymentStatus')], 200);
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
        
        $invoice = EstimateInvoice::where('estimate_invoice_id', $id)->get()->first();
        /*$invoice = self::getInvoice($id);
        print_r($invoice);
        print_r($request->all());
        die;*/
        if (empty($invoice)) {
            return response()->json(['status'=>false, 'message'=>'Invoice is not exist in our system.', 'response'=>[]], 200);       
        }
        $paymentStatus = [
            'Paid',
            'Bad Debt',
            'Discount',
            'Partially'
        ];
        if (!in_array($request->input('payment_status'), $paymentStatus)) {
            return response()->json(['status'=>false, 'message'=>'Invoice payment type is not valid.', 'response'=>[]], 200); 
        }
        if (in_array($request->input('payment_status'), ['Paid','Partially'])) {
            $invoiceAmount = $invoice->invoice_amount;
            $totalInviceAmount = $invoiceAmount + ($invoiceAmount * $invoice->tax_percentage / 100);

            $paidCollection = $invoice->collection;
            $collection = $request->input('collection');
            $bad_debts_amount = $request->input('bad_debts_amount');
            $discount = $request->input('discount');

            $paidAmount = $collection + $bad_debts_amount + $discount;

            $paidAmount  = $paidAmount + ($paidAmount * $invoice->tax_percentage / 100);
            $paidTax  = $paidCollection * $invoice->tax_percentage / 100;
            $balance = $invoiceAmount - ($paidCollection+($paidTax+$paidAmount));
            if ($balance < 0) {
                return response()->json(['status'=>false, 'message'=>'Invoice balance is not less then 0', 'response'=>[]], 200);        
            }
        }elseif ($request->input('payment_status') == 'Bad Debt') {
            $invoiceAmount = $invoice->invoice_amount;
            $totalInviceAmount = $invoiceAmount + ($invoiceAmount * $invoice->tax_percentage / 100);

            $paidCollection = $invoice->bad_debts_amount;

            $collection = $request->input('collection');
            $bad_debts_amount = $request->input('bad_debts_amount');
            $discount = $request->input('discount');

            $paidAmount = $collection + $bad_debts_amount + $discount;

            $paidAmount  = $paidAmount + ($paidAmount * $invoice->tax_percentage / 100);
            $paidTax  = $paidCollection * $invoice->tax_percentage / 100;
            $balance = $invoiceAmount - ($paidCollection+($paidTax+$paidAmount));
            if ($balance < 0) {
                return response()->json(['status'=>false, 'message'=>'Invoice balance is not less then 0', 'response'=>[]], 200);        
            }
        }
        elseif ($request->input('payment_status') == 'Discount') {
            $invoiceAmount = $invoice->invoice_amount;
            $totalInviceAmount = $invoiceAmount + ($invoiceAmount * $invoice->tax_percentage / 100);

            $paidCollection = $invoice->discount;

            $collection = $request->input('collection');
            $bad_debts_amount = $request->input('bad_debts_amount');
            $discount = $request->input('discount');

            $paidAmount = $collection + $bad_debts_amount + $discount;

            $paidAmount  = $paidAmount + ($paidAmount * $invoice->tax_percentage / 100);
            $paidTax  = $paidCollection * $invoice->tax_percentage / 100;
            $balance = $invoiceAmount - ($paidCollection+($paidTax+$paidAmount));
            if ($balance < 0) {
                return response()->json(['status'=>false, 'message'=>'Invoice balance is not less then 0', 'response'=>[]], 200);        
            }
        }
      
        $currentUser = getApiCurrentUser();
        $invoice->paid_on = date('Y-m-d H:i:s');
        $invoice->payment_note = $request->input('payment_note');
        $invoice->payment_method = $request->input('payment_method');
        $invoice->payment_status = $request->input('payment_status');

        if ($request->input('collection')) {
            $invoice->collection = $invoice->collection+$request->input('collection');
        }        
        
        $invoice->total_collection = $request->input('total_collection');

        $invoice->tax = $request->input('tax');
        if ($request->input('bad_debts_amount')) {
            $invoice->bad_debts_amount = $request->input('bad_debts_amount');
        }
        if ($request->input('discount')) {
            $invoice->discount = $invoice->discount+$request->input('discount');
        }
        $invoice->invoice_status = 'Paid';
        $invoice->updated_at = date('Y-m-d H:i:s');
        $invoice->updated_by = $currentUser->user_id;
        $invoice->save();

        $transactionAmount = 0;
        if($request->input('collection') != 0.00 ){
            $transactionAmount = $request->input('collection');
        }
        if($request->input('bad_debts_amount') && $request->input('bad_debts_amount') != 0.00){
            $transactionAmount = $request->input('bad_debts_amount');
        }

        $invoiceLogs = new EstimateInvoiceLogs();
        $invoiceLogs->estimate_invoice_id = $invoice->estimate_invoice_id;
        $invoiceLogs->action = $request->input('payment_status');
        $invoiceLogs->amount = $transactionAmount;
        if ($request->input('discount')) {
            $invoiceLogs->discount = $request->input('discount');
        }
        $invoiceLogs->tax = $request->input('tax');
        $invoiceLogs->total_collection = $request->input('total_collection');
        $invoiceLogs->created_by = $currentUser->user_id;
        $invoiceLogs->updated_by = $currentUser->user_id;
        $invoiceLogs->created_at = date('Y-m-d H:i:s');
        $invoiceLogs->updated_at = date('Y-m-d H:i:s');
        $invoiceLogs->save();

        $invoiceMailData = [];
        $invoiceMailData['estimate_no'] = $invoice->estimate_id; 
        $invoiceMailData['amount'] = $request->input('collection');
        $invoiceMailData['collection'] = $invoice->invoice_amount;
        $invoiceMailData['user_name'] = $currentUser->first_name;
        $invoiceMailData['invoice_no'] = $invoice->invoice_no;
        $invoiceMailData['customer_name'] = Customer::where('customer_id', $invoice->customer_id)->get()->pluck('customer_name')->first();
        $representative = User::join('estimate', 'estimate.user_id', 'users.user_id')->where('estimate.estimate_id', $invoice->estimate_id)->select('users.first_name', 'users.email')->get()->first();
        $invoiceMailData['rep_name'] = $representative->first_name;

        $emailBody = emailTemplate($currentUser, 'payment_collection', $invoiceMailData);

        $emailFromEmail = 'noreply@bondcrm.com';
        $emailFromName = $currentUser->first_name;
        $emailTo = $representative->email;
        $EmailSubject = $emailBody['subject'];
        $emailBody = $emailBody['body'];

        SendEmail($emailTo, $EmailSubject, $emailBody, [], $emailFromName, $emailFromEmail, [], []);

        return self::index($invoice->customer_id);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $invoice = EstimateInvoice::where('estimate_invoice_id', $id)->get()->first();
        if (empty($invoice)) {
            return response()->json(['status'=>false, 'message'=>'Invoice is not exist in our system.', 'response'=>[]], 200);       
        }
        $invoice->delete();
        return self::index($invoice->customer_id);
    }

    public function getEstimateInvoiceDetails($estimate_id = null)
    {
        $currentUser = getApiCurrentUser();        
        $estimateInvoice = Estimate::join('customer', 'customer.customer_id', 'estimate.customer_id')
                        ->join('users', 'users.user_id', 'estimate.user_id')
                        ->where('estimate.comp_id', $currentUser->comp_id)
                        ->where('estimate_id', $estimate_id)
                        ->select(
                            'estimate.*', 
                            'customer.customer_name',
                            'customer.phone',
                            'users.first_name as project_manager'
                        )
                        ->get()->first();
        $invoices = EstimateInvoice::where('estimate_id', $estimate_id)
                    ->select(DB::raw('SUM(collection) AS amount'), DB::raw('SUM(invoice_amount) AS totalAmount'), 'invoice_status')
                    ->groupBy('invoice_status')
                    ->get();
        $paid = $unpaid = $netInvoiceAmount = 0;
        foreach ($invoices as $invoice) {
            $paid += $invoice->amount;
            $netInvoiceAmount += $invoice->totalAmount;
        }

        $netBalance = $netInvoiceAmount-$paid;

        $estimateInvoice->paid = $paid;
        $estimateInvoice->unpaid = $unpaid;
        $estimateInvoice->netBalance = $netBalance;
        $estimateInvoice->netInvoiceAmount = $netBalance;

        return response()->json(['status'=>true, 'message'=>'Estimate', 'response'=>compact('estimateInvoice')], 200);
    }

    public function estimateInvoicePDF($estimate_invoice_id = 0, $uuid = null)
    {
        return self::estimateInvoiceViewPDF($uuid, $estimate_invoice_id);        
    }
    public function estimateInvoiceViewPDF($uuid, $estimate_invoice_id)
    {
        $prefix = DB::getTablePrefix();
        $companyUser = User::where('uuid', $uuid)->get()->first();

        if (empty($companyUser)) {
            return response()->json(['status'=>false, 'message'=>'Invoice is not exist in our system.', 'response'=>[]], 200);
        }
        $whereRawQuery = $prefix.'estimate_invoice_logs.estimate_invoice_id = '.$prefix.'estimate_invoice.estimate_invoice_id';

        $totalBadDebits = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action != "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.amount), 0) AS totalBadAmount'))
                            ->limit(1);

        $totalPaidAmount = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action != "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.amount), 0) AS totalAmount'))
                            ->limit(1);

        $totalTax = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action = "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.tax), 0) AS totalTax'))
                            ->limit(1);
        
        $totalCollections = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action != "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.total_collection), 0) AS totalCollection'))
                            ->limit(1);

        $totalInvoiceAmount = EstimateInvoice::whereRaw($prefix.'estimate_invoice.estimate_invoice_id = '.$prefix.'estimate_invoice.estimate_invoice_id')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice.invoice_amount), 0) AS totalInvoiceAmount'))
                            ->limit(1);
        $invoice = self::getInvoice($estimate_invoice_id);
        if (empty($invoice)) {
            return response()->json(['status'=>false, 'message'=>'Invoice is not exist in our system.', 'response'=>[]], 200);       
        }
        $invoices = EstimateInvoice::join('estimate', 'estimate.estimate_id', 'estimate_invoice.estimate_id')
                    ->leftjoin('tax', 'tax.tax_id', 'estimate.tax_id')
                    ->join('job_status', 'job_status.status_id', 'estimate.status_id')
                    ->join('services', 'services.serv_id','estimate.serv_id')
                    ->where('estimate_invoice.estimate_id', $invoice->estimate_id)                    
                    ->select(
                        'estimate_invoice.*', 
                        'estimate.grand_total',
                        'estimate.sub_total',
                        'estimate.estimate_id',
                        'estimate.tax',
                        'estimate.po_claim',
                        'estimate.note',
                        'services.serv_name as service',
                        'estimate.estimate_status',
                        'tax.name AS tax_name',
                        'tax.tax AS tax_percentage',
                        'job_status.name as job_status_name',
                        DB::raw("({$totalBadDebits->toSql()}) as totalBadDebits"),
                        DB::raw("({$totalPaidAmount->toSql()}) as totalPaidAmount"),
                        DB::raw("({$totalTax->toSql()}) as totalTax"),
                        DB::raw("({$totalCollections->toSql()}) as totalCollections"),
                        DB::raw("({$totalInvoiceAmount->toSql()}) as totalInvoiceAmount")
                    )->get();

        $company = Company::where('comp_id', $invoice->comp_id)->get()->first();
        if (empty($company)) {
            return response()->json(['status'=>false, 'message'=>'Invoice is not exist in our system.', 'response'=>[]], 200);       
        }

        $customer = Customer::where('customer_id', $invoice->customer_id)->get()->first();
        if (empty($customer)) {
            return response()->json(['status'=>false, 'message'=>'Invoice is not exist in our system.', 'response'=>[]], 200);       
        }

        return InvoiceViewPDf($invoice, $invoices, $company, $customer, $companyUser);
    }
    public function estimateInvoiceCreatEmail($estimate_id = 0)
    {
        $estimate = Estimate::find($estimate_id);
        if (empty($estimate)) {
            return response()->json(['status'=>false, 'message'=>'Estimate is not exist in our system.', 'response'=>[]], 200);     
        }
        $currentUser = getApiCurrentUser(); 
        $emailTemplates = EmailTemplate::where('comp_id', $currentUser->comp_id)->get();
        $emailAttachments = EmailAttachment::where('comp_id', $currentUser->comp_id)->get();        
        $customer = Customer::where('customer_id', $estimate->customer_id)->get()->first();
        return response()->json(['status'=>true, 'message'=>'Email Create Data', 'response'=>compact('emailTemplates','emailAttachments','customer')], 200);
    }

    public function estimateInvoiceSendEmail(Request $request)
    {
        $currentUser = getApiCurrentUser(); 
        $estimate_invoice_id = $request->input('estimate_invoice_id');
        $pdfUrl = response()->json(self::estimateInvoiceCreatePDF($currentUser->uuid, $estimate_invoice_id));
        
        if ($pdfUrl->getData()->original->status == false) {
            return $pdfUrl;
        }
        $pdfUrl = $pdfUrl->getData()->original->response->pdfUrl;

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
    public function estimateInvoiceCreatePDF($uuid, $estimate_invoice_id)
    {
        $prefix = DB::getTablePrefix();
        $companyUser = User::where('uuid', $uuid)->get()->first();

        if (empty($companyUser)) {
            return response()->json(['status'=>false, 'message'=>'Invoice is not exist in our system.', 'response'=>[]], 200);
        }
        $whereRawQuery = $prefix.'estimate_invoice_logs.estimate_invoice_id = '.$prefix.'estimate_invoice.estimate_invoice_id';

        $totalBadDebits = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action != "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.amount), 0) AS totalBadAmount'))
                            ->limit(1);

        $totalPaidAmount = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action != "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.amount), 0) AS totalAmount'))
                            ->limit(1);

        $totalTax = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action = "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.tax), 0) AS totalTax'))
                            ->limit(1);
        
        $totalCollections = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action != "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.total_collection), 0) AS totalCollection'))
                            ->limit(1);

        $totalInvoiceAmount = EstimateInvoice::whereRaw($prefix.'estimate_invoice.estimate_invoice_id = '.$prefix.'estimate_invoice.estimate_invoice_id')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice.invoice_amount), 0) AS totalInvoiceAmount'))
                            ->limit(1);
        $invoice = self::getInvoice($estimate_invoice_id);
        if (empty($invoice)) {
            return response()->json(['status'=>false, 'message'=>'Invoice is not exist in our system.', 'response'=>[]], 200);       
        }
        $invoices = EstimateInvoice::join('estimate', 'estimate.estimate_id', 'estimate_invoice.estimate_id')
                    ->leftjoin('tax', 'tax.tax_id', 'estimate.tax_id')
                    ->join('job_status', 'job_status.status_id', 'estimate.status_id')
                    ->join('services', 'services.serv_id','estimate.serv_id')
                    ->where('estimate_invoice.estimate_id', $invoice->estimate_id)                    
                    ->select(
                        'estimate_invoice.*', 
                        'estimate.grand_total',
                        'estimate.sub_total',
                        'estimate.estimate_id',
                        'estimate.tax',
                        'estimate.po_claim',
                        'estimate.note',
                        'services.serv_name as service',
                        'estimate.estimate_status',
                        'tax.name AS tax_name',
                        'tax.tax AS tax_percentage',
                        'job_status.name as job_status_name',
                        DB::raw("({$totalBadDebits->toSql()}) as totalBadDebits"),
                        DB::raw("({$totalPaidAmount->toSql()}) as totalPaidAmount"),
                        DB::raw("({$totalTax->toSql()}) as totalTax"),
                        DB::raw("({$totalCollections->toSql()}) as totalCollections"),
                        DB::raw("({$totalInvoiceAmount->toSql()}) as totalInvoiceAmount")
                    )->get();

        $company = Company::where('comp_id', $invoice->comp_id)->get()->first();
        if (empty($company)) {
            return response()->json(['status'=>false, 'message'=>'Invoice is not exist in our system.', 'response'=>[]], 200);       
        }

        $customer = Customer::where('customer_id', $invoice->customer_id)->get()->first();
        if (empty($customer)) {
            return response()->json(['status'=>false, 'message'=>'Invoice is not exist in our system.', 'response'=>[]], 200);       
        }

        $pdfUrl = InvoicePDf($invoice, $invoices, $company, $customer, $companyUser);
        return response()->json(['status'=>true, 'message'=>'PDF Created successfully', 'response'=>compact('pdfUrl')], 200);
    }
    public static function getInvoice($estimate_invoice_id)
    {
        $prefix = DB::getTablePrefix();
        $whereRawQuery = $prefix.'estimate_invoice_logs.estimate_invoice_id = '.$prefix.'estimate_invoice.estimate_invoice_id';

        $totalBadDebits = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action != "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.amount), 0) AS totalBadAmount'))
                            ->limit(1);

        $totalPaidAmount = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action != "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.amount), 0) AS totalAmount'))
                            ->limit(1);

        $totalTax = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action = "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.tax), 0) AS totalTax'))
                            ->limit(1);
        
        $totalCollections = EstimateInvoiceLogs::whereRaw($whereRawQuery)
                            ->whereRaw($prefix.'estimate_invoice_logs.action != "Bad Debt"')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice_logs.total_collection), 0) AS totalCollection'))
                            ->limit(1);

        $totalInvoiceAmount = EstimateInvoice::whereRaw($prefix.'estimate_invoice.estimate_invoice_id = '.$prefix.'estimate_invoice.estimate_invoice_id')
                            ->select(DB::raw('IFNULL(SUM('.$prefix.'estimate_invoice.invoice_amount), 0) AS totalInvoiceAmount'))
                            ->limit(1);
        return EstimateInvoice::join('estimate', 'estimate.estimate_id', 'estimate_invoice.estimate_id')
                    ->leftjoin('tax', 'tax.tax_id', 'estimate.tax_id')
                    ->join('job_status', 'job_status.status_id', 'estimate.status_id')
                    ->join('services', 'services.serv_id','estimate.serv_id')
                    ->where('estimate_invoice.estimate_invoice_id', $estimate_invoice_id)
                    ->select(
                        'estimate_invoice.*', 
                        'estimate.grand_total',
                        'estimate.sub_total',
                        'estimate.estimate_id',
                        'estimate.user_id as estUser',
                        'estimate.tax',
                        'estimate.po_claim',
                        'estimate.note',
                        'services.serv_name as service',
                        'estimate.estimate_status',
                        'tax.name AS tax_name',
                        'tax.tax AS tax_percentage',
                        'job_status.name as job_status_name',
                        DB::raw("({$totalBadDebits->toSql()}) as totalBadDebits"),
                        DB::raw("({$totalPaidAmount->toSql()}) as totalPaidAmount"),
                        DB::raw("({$totalTax->toSql()}) as totalTax"),
                        DB::raw("({$totalCollections->toSql()}) as totalCollections"),
                        DB::raw("({$totalInvoiceAmount->toSql()}) as totalInvoiceAmount")
                    )
                    ->get()->first();
    }
}
