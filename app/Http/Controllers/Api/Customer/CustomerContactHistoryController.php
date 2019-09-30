<?php

namespace App\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;
use App\User;
use App\CustomerContactHistory;

class CustomerContactHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($customer_id = null)
    {
        $currentUser = getApiCurrentUser();
        $contactHistories = CustomerContactHistory::where('customer_id', $customer_id)->where('comp_id', $currentUser->comp_id)->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Contact History', 'response'=>compact('contactHistories')], 200);
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
            'communication_mode' => 'required|in:Message,Phone Call,Email,Whatsapp,Others',
            'note' => 'required',
            'contact_date' => 'required|date_format:Y-m-d',
            'contact_time' => 'required|date_format:H:i',
        ];
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $uuid = createUuid('customer_contact_history-'.date('YmdHis'));
        $cust_cont_history_sno = $uuid->string;
        $customerContactHistory = new CustomerContactHistory();
        $customerContactHistory->cust_cont_history_sno = $cust_cont_history_sno;
        $customerContactHistory->comp_id = $currentUser->comp_id;
        $customerContactHistory->customer_id = $request->input('customer_id');
        $customerContactHistory->communication_mode = $request->input('communication_mode');
        $customerContactHistory->note = $request->input('note');
        $customerContactHistory->contact_date = $request->input('contact_date');
        $customerContactHistory->contact_time = $request->input('contact_time');
        $customerContactHistory->status = 1;        
        $customerContactHistory->created_by = $currentUser->user_id;
        $customerContactHistory->created_at = new DateTime;
        $customerContactHistory->updated_at = new DateTime;
        $customerContactHistory->updated_by = $currentUser->user_id;
        $customerContactHistory->save();

        return self::index($customerContactHistory->customer_id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $customerContactHistory = CustomerContactHistory::find($id);
        if (empty($customerContactHistory)) {
            return response()->json(['status'=>false, 'message'=>'Customer Contact History is not exists in our system', 'response'=>[]], 200);       
        }
        return response()->json(['status'=>true, 'message'=>'Customer Contact History', 'response'=>compact('customerContactHistory')], 200); 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $customerContactHistory = CustomerContactHistory::find($id);
        if (empty($customerContactHistory)) {
            return response()->json(['status'=>false, 'message'=>'Customer Contact History is not exists in our system', 'response'=>[]], 200);       
        }
        return response()->json(['status'=>true, 'message'=>'Customer Contact History', 'response'=>compact('customerContactHistory')], 200); 
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
        $customerContactHistory = CustomerContactHistory::find($id);
        if (empty($customerContactHistory)) {
            return response()->json(['status'=>false, 'message'=>'Customer Contact History is not exists in our system', 'response'=>[]], 200);       
        }

        $customerContactHistory->customer_id = $request->input('customer_id');
        $customerContactHistory->communication_mode = $request->input('communication_mode');
        $customerContactHistory->note = $request->input('note');
        $customerContactHistory->contact_date = $request->input('contact_date');
        $customerContactHistory->contact_time = $request->input('contact_time');
        $customerContactHistory->updated_at = new DateTime;
        $customerContactHistory->updated_by = $currentUser->user_id;
        $customerContactHistory->save();

        return self::index($customerContactHistory->customer_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customerContactHistory = CustomerContactHistory::find($id);
        if (empty($customerContactHistory)) {
            return response()->json(['status'=>false, 'message'=>'Customer Contact History is not exists in our system', 'response'=>[]], 200);       
        }
        $customerContactHistory->delete();
        return response()->json(['status'=>true, 'message'=>'Customer contact history deleted', 'response'=>compact('customerContactHistory')], 200);
    }
}
