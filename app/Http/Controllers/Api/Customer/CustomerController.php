<?php

namespace App\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, DateTime, Config, Helpers, Hash, DB;
use App\Customer;
use App\Branch;
use App\JobStatus;
use App\LeadSource;
use App\Services;
use App\Building;       
use App\Locations;       

use Illuminate\Validation\Rule;

class CustomerController extends Controller
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
        $branch = Branch::whereRaw($prefix.'branch.branch_id = '.$prefix.'customer.branch_id')->select('branch_name')->limit(1);
        $job_status = JobStatus::whereRaw($prefix.'job_status.status_id = '.$prefix.'customer.status_id')->select('name')->limit(1);
        $lead_source = LeadSource::whereRaw($prefix.'lead_source.lead_id = '.$prefix.'customer.lead_id')->select('name')->limit(1);
        $service = Services::whereRaw($prefix.'services.serv_id = '.$prefix.'customer.serv_id')->select('serv_name')->limit(1);
        $orderKey = (!empty($request->input('orderKey'))?$request->input('orderKey'):'estimate_id');
        $orderBy = (!empty($request->input('orderBy'))?$request->input('orderBy'):'DESC');
        $size = ($request->input('size')?$request->input('size'):dataPerPage());
        $customers = Customer::select(
                            '*', 
                            DB::raw("({$branch->toSql()}) as branch_name"), 
                            DB::raw("({$job_status->toSql()}) as job_name"), 
                            DB::raw("({$lead_source->toSql()}) as lead_name"), 
                            DB::raw("({$service->toSql()}) as serv_name")
                        )
                    ->where(function ($query) use($currentUser, $request, $prefix){
                        $query->where('comp_id', $currentUser->comp_id);
                        $start_date = $request->input('start_date');
                        $end_date = $request->input('end_date');
                        $param = $request->input('param');
                        if (!empty($param)) {
                            $query->where('customer_name', 'LIKE', '%'.$param.'%');
                        }
                        if (!empty($start_date) && !empty($end_date)) {
                            $query->whereRaw('DATE_FORMAT('.$prefix.'estimate.created_at, "%Y-%m-%d") >= "'.$start_date.'" AND DATE_FORMAT('.$prefix.'estimate.created_at, "%Y-%m-%d")  <= "'.$end_date.'"');
                        }elseif (!empty($start_date)) {
                            $query->whereRaw('DATE_FORMAT('.$prefix.'customer.created_at, "%Y-%m-%d") = "'.$start_date.'"');
                        }elseif (!empty($end_date)) {
                            $query->whereRaw('DATE_FORMAT('.$prefix.'customer.created_at, "%Y-%m-%d") = "'.$end_date.'"');
                        }
                        if (!empty($request->input('status'))) {
                            $status = explode(',', $request->input('status'));
                            $query->whereIn('customer.status_id', $status);
                        }
                        if (!empty($request->input('service_type'))) {
                            $service_type = explode(',', $request->input('service_type'));
                            $query->whereIn('customer.serv_id', $service_type);   
                        }
                        if (!empty($request->input('branch'))) {                                
                            $branch = explode(',', $request->input('branch'));
                            $query->whereIn('customer.branch_id', $branch);
                        }
                    })                        
                    ->orderBy($orderKey, $orderBy)
                    ->paginate($size);
        $status = $services = $branchs = [];
        if ($request->input('type') == 'start') {
            $status = JobStatus::where('comp_id', $currentUser->comp_id)->get();
            $services = Services::where('comp_id', $currentUser->comp_id)->get();
            $branchs = Branch::where('comp_id', $currentUser->comp_id)->get();
        }
        return response()->json(['status'=>true, 'message'=>'All Tasktype', 'response'=>compact('customers','status','services','branchs')], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	public static function storeRules($branch_id = null,$loc_id = null,$status_id = null,$serv_id = null,$lead_id=null, $build_id = null,$id=0)
    {
        return [
			'branch_id' => [
                'required',
                Rule::exists('branch')->where(function ($query) use($branch_id) {
                    $query->where('branch_id', $branch_id);
                }),
				
            ],
			'loc_id' => [
                'required',
                Rule::exists('locations')->where(function ($query) use($loc_id) {
                    $query->where('loc_id', $loc_id);
                }),
				
            ],
			'status_id' => [
                'required',
                Rule::exists('job_status')->where(function ($query) use($status_id) {
                    $query->where('status_id', $status_id);
                }),
				
            ],
			'serv_id' => [
                'required',
                Rule::exists('services')->where(function ($query) use($serv_id) {
                    $query->where('serv_id', $serv_id);
                }),
				
            ],
			'lead_id' => [
                'required',
                Rule::exists('lead_source')->where(function ($query) use($lead_id) {
                    $query->where('lead_id', $lead_id);
                }),
				
            ],
            'build_id' => [
                'required',
                Rule::exists('building')->where(function ($query) use($build_id) {
                    $query->where('build_id', $build_id);
                }),
                
            ],	
            'customer_name' => 'required|string|max:191',	
            'customer_address' => 'required|string|max:191',
            'customer_postal_code' => 'required|string|max:191',	
            'email' => 'required|email|unique:customer,email,'.$id.',customer_id',
            'mobile' => 'required|numeric|unique:customer,mobile,'.$id.',customer_id',
            'phone' => 'numeric',
            'language' => 'required',
            'profile' => 'required',	
            'cust_status' => 'required|in:0,1',			 
        ];
    }
    public function store(Request $request)
    {
		$branch_id = $request->input('branch_id');
		$loc_id = $request->input('loc_id');
		$status_id = $request->input('status_id');
		$serv_id = $request->input('serv_id');
		$lead_id = $request->input('lead_id');
        $build_id = $request->input('build_id');
        $validator = Validator::make($request->all(), self::storeRules($branch_id ,$loc_id,$status_id,$serv_id,$lead_id,$build_id));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
		$currentUser = getApiCurrentUser();
        $uuid = createUuid($request->input('customer_name').date('YmdHis'));
        $customer_code = $uuid->string;
        $customer = new Customer();
		$customer->customer_sno = $customer_code;
		$customer->customer_name = $request->input('customer_name');
		$customer->customer_address = $request->input('customer_address');
		$customer->customer_postal_code = $request->input('customer_postal_code');
		$customer->email = $request->input('email');
		$customer->phone = $request->input('phone');
		$customer->mobile = $request->input('mobile');
		$customer->language = $request->input('language');
		$customer->profile = $request->input('profile');
		$customer->cust_status = $request->input('cust_status');
        $customer->branch_id = $branch_id;
        $customer->build_id = $build_id;
		$customer->comp_id = $currentUser->comp_id;
		$customer->loc_id = $loc_id;
		$customer->status_id = $status_id;
		$customer->serv_id = $serv_id;
		$customer->lead_id = $lead_id;
        $customer->created_by = $currentUser->user_id;
        $customer->created_at = new DateTime;
        $customer->updated_at = new DateTime;
		$customer->updated_by = $currentUser->user_id;
        $customer->save();

        return response()->json(['status'=>true, 'message'=>'customer Created', 'response'=>compact('customer')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($customer_id)
    {
        $customer = Customer::find($customer_id);
		if (empty($customer)) {
            return Response()->json(['status'=>false, 'message' => 'customer is not exists in our system', 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $branchs = Branch::where('comp_id', $currentUser->comp_id)->get();
        $jobs = JobStatus::where('comp_id', $currentUser->comp_id)->get();
        $leads = LeadSource::where('comp_id', $currentUser->comp_id)->get();
        $services = Services::where('comp_id', $currentUser->comp_id)->get();
        $locations = locations::where('comp_id', $currentUser->comp_id)->get();
        $buildings = Building::where('comp_id', $currentUser->comp_id)->get();
        return Response()->json(['status'=>true, 'message' => 'customer', 'response' => compact('customer','branchs', 'jobs','leads','services','locations','buildings')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($customer_id)
    {
        $customer = Customer::find($customer_id);
		if (empty($customer)) {
            return Response()->json(['status'=>false, 'message' => 'customer is not exists in our system', 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $branchs = Branch::where('comp_id', $currentUser->comp_id)->get();
        $jobs = JobStatus::where('comp_id', $currentUser->comp_id)->get();
        $leads = LeadSource::where('comp_id', $currentUser->comp_id)->get();
        $services = Services::where('comp_id', $currentUser->comp_id)->get();
        $locations = locations::where('comp_id', $currentUser->comp_id)->get();
        $buildings = Building::where('comp_id', $currentUser->comp_id)->get();
        return Response()->json(['status'=>true, 'message' => 'customer', 'response' => compact('customer','branchs', 'jobs','leads','services','locations','buildings')], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $customer_id)
    {
		$branch_id = $request->input('branch_id');
		$loc_id = $request->input('loc_id');
		$status_id = $request->input('status_id');
		$serv_id = $request->input('serv_id');
		$lead_id = $request->input('lead_id');
        $build_id = $request->input('build_id');
        $validator = Validator::make($request->all(), self::storeRules($branch_id ,$loc_id,$status_id,$serv_id,$lead_id, $build_id, $customer_id));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
		$currentUser = getApiCurrentUser();
        $customer = Customer::find($customer_id);
		if (empty($customer)) {
            return Response()->json(['status'=>false, 'message' => 'customer is not exists in our system', 'response' => []], 200);
        }
		$customer->customer_name = $request->input('customer_name');
		$customer->customer_address = $request->input('customer_address');
		$customer->customer_postal_code = $request->input('customer_postal_code');
		$customer->email = $request->input('email');
		$customer->phone = $request->input('phone');
		$customer->mobile = $request->input('mobile');
		$customer->language = $request->input('language');
		$customer->profile = $request->input('profile');
		$customer->cust_status = $request->input('cust_status');
        $customer->branch_id = $branch_id;
        $customer->loc_id = $loc_id;
        $customer->build_id = $build_id;
        $customer->status_id = $status_id;
        $customer->serv_id = $serv_id;
        $customer->lead_id = $lead_id;
        $customer->updated_at = new DateTime;
		$customer->updated_by = $currentUser->user_id;
        $customer->save();

        return response()->json(['status'=>true, 'message'=>'customer updated', 'response'=>compact('customer')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($customer_id)
    {
        $customer = Customer::find($customer_id);
        if (empty($customer)) {
            return Response()->json(['status'=>false, 'message' => 'customer is not exists our system', 'response' => []], 200);
        }
        $customer->delete();
        return response()->json(['status'=>true, 'message'=>'Deleted', 'response'=>compact('customer')], 200);
    }
    public function getCustomerData()
    {
        $currentUser = getApiCurrentUser();
        $branchs = Branch::where('comp_id', $currentUser->comp_id)->get();
        $jobs = JobStatus::where('comp_id', $currentUser->comp_id)->get();
        $leads = LeadSource::where('comp_id', $currentUser->comp_id)->get();
        $services = Services::where('comp_id', $currentUser->comp_id)->get();
        $locations = locations::where('comp_id', $currentUser->comp_id)->get();
        $buildings = Building::where('comp_id', $currentUser->comp_id)->get();
        return response()->json(['status'=>true, 'message'=>'Deleted', 'response'=>compact('branchs', 'jobs','leads','services','locations','buildings')], 200);
    }
}
