<?php

namespace App\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;
use App\User;
use App\CustomerOutSource;
use App\Networks;

class CustomerOutSourceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($customer_id = null)
    {
        $currentUser = getApiCurrentUser();
        $prefix = DB::getTablePrefix();
        $network = Networks::whereRaw($prefix.'networks.network_id = '.$prefix.'customer_out_source.network_id')->select('comp_name')->limit(1);
        $outSources = CustomerOutSource::select('*', DB::raw("({$network->toSql()}) as network_name"))->where('customer_id', $customer_id)->where('comp_id', $currentUser->comp_id)->paginate(dataPerPage());

        return response()->json(['status'=>true, 'message'=>'All Customer Out Sources', 'response'=>compact('outSources')], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $currentUser = getApiCurrentUser();
        $networks = Networks::where('comp_id', $currentUser->comp_id)->select('network_id', 'comp_name')->get();
        return response()->json(['status'=>true, 'message'=>'All Network', 'response'=>compact('networks')], 200);
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
            'network_id' => [
                'required',
                Rule::exists('networks')->where(function ($query) use($request) {
                    $query->where('network_id', $request->input('network_id'));
                }),                
            ],  
            'note' => 'required',
            'send_email' => 'required|in:0,1',
        ];
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $uuid = createUuid('customer_out_source-'.date('YmdHis'));
        $cust_out_src_sno = $uuid->string;
        $customerOutSource = new CustomerOutSource();
        $customerOutSource->cust_out_src_sno = $cust_out_src_sno;
        $customerOutSource->comp_id = $currentUser->comp_id;
        $customerOutSource->customer_id = $request->input('customer_id');
        $customerOutSource->network_id = $request->input('network_id');
        $customerOutSource->note = $request->input('note');
        $customerOutSource->send_email = $request->input('send_email');
        $customerOutSource->status = 1;        
        $customerOutSource->created_by = $currentUser->user_id;
        $customerOutSource->created_at = new DateTime;
        $customerOutSource->updated_at = new DateTime;
        $customerOutSource->updated_by = $currentUser->user_id;
        $customerOutSource->save();

        return self::index($customerOutSource->customer_id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $customerOutSource = CustomerOutSource::find($id);
        if (empty($customerOutSource)) {
            return response()->json(['status'=>false, 'message'=>'Customer Out Source is not exists in our system', 'response'=>[]], 200);    
        }
        $currentUser = getApiCurrentUser();
        $network = Networks::where('comp_id', $currentUser->comp_id)->select('network_id', 'comp_name')->get();
        return response()->json(['status'=>true, 'message'=>'Customer Out Source', 'response'=>compact('customerOutSource','network')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $customerOutSource = CustomerOutSource::find($id);
        if (empty($customerOutSource)) {
            return response()->json(['status'=>false, 'message'=>'Customer Out Source is not exists in our system', 'response'=>[]], 200);    
        }
        $currentUser = getApiCurrentUser();
        $network = Networks::where('comp_id', $currentUser->comp_id)->select('network_id', 'comp_name')->get();
        return response()->json(['status'=>true, 'message'=>'Customer Out Source', 'response'=>compact('customerOutSource','network')], 200);
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

        $customerOutSource = CustomerOutSource::find($id);
        if (empty($customerOutSource)) {
            return response()->json(['status'=>false, 'message'=>'Customer Out Source is not exists in our system', 'response'=>[]], 200);    
        }
        
        $currentUser = getApiCurrentUser();

        $customerOutSource->network_id = $request->input('network_id');
        $customerOutSource->note = $request->input('note');
        $customerOutSource->send_email = $request->input('send_email');
        $customerOutSource->updated_at = new DateTime;
        $customerOutSource->updated_by = $currentUser->user_id;
        $customerOutSource->save();

        return self::index($customerOutSource->customer_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customerOutSource = CustomerOutSource::find($id);
        if (empty($customerOutSource)) {
            return response()->json(['status'=>false, 'message'=>'Customer Out Source is not exists in our system', 'response'=>[]], 200);    
        }

        $customerOutSource->delete();
        return response()->json(['status'=>true, 'message'=>'Customer Out Source Deleted', 'response'=>compact('customerOutSource')], 200);    
    }
}
