<?php

namespace App\Http\Controllers\Api\InsuranceCompany;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\InsuranceCompany;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class InsuranceCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = getApiCurrentUser();
        $insurances = InsuranceCompany::where('comp_id', $currentUser->comp_id)->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All InsuranceCompany', 'response'=>compact('insurances')], 200);
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
            'ins_comp_status' => 'required|in:0,1',
        ];
        
        if (!$id) {
            $rules['ins_comp_name'] = 'required|string|max:191|unique:insurance_company';   
        } else {
            $rules['ins_comp_name'] = 'required|string|max:191|unique:insurance_company,ins_comp_name,'.$id.',ins_comp_id';
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
        $uuid = createUuid($request->input('ins_comp_name').date('YmdHis'));
        $ins_comp_sno = $uuid->string;

        $insurance = new InsuranceCompany();
        $insurance->ins_comp_sno = $ins_comp_sno;
        $insurance->comp_id = $currentUser->comp_id;
        $insurance->ins_comp_name = $request->input('ins_comp_name');
        $insurance->ins_comp_status = $request->input('ins_comp_status');
        $insurance->created_by = $currentUser->user_id;
        $insurance->updated_by = $currentUser->user_id;
        $insurance->created_at = new DateTime;
        $insurance->updated_at = new DateTime;
        $insurance->save();

        return response()->json(['status'=>true, 'message'=>'InsuranceCompany Saved','response'=>compact('insurance')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $insurance = InsuranceCompany::find($id);
        if (empty($insurance)) {
            return response()->json(['status'=>false, 'message'=>'InsuranceCompany is not exist in our system','response'=>[]], 200);       
        }
        return response()->json(['status'=>true, 'message'=>'InsuranceCompany','response'=>compact('insurance')], 200);
    }   

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $insurance = InsuranceCompany::find($id);
        if (empty($insurance)) {
            return response()->json(['status'=>false, 'message'=>'InsuranceCompany is not exist in our system','response'=>[]], 200);       
        }
        return response()->json(['status'=>true, 'message'=>'InsuranceCompany','response'=>compact('insurance')], 200);
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
        $insurance = InsuranceCompany::find($id);
        if (empty($insurance)) {
            return response()->json(['status'=>false, 'message'=>'InsuranceCompany is not exist in our system','response'=>[]], 200);       
        }
        $currentUser = getApiCurrentUser();
        $insurance->ins_comp_name = $request->input('ins_comp_name');
        $insurance->ins_comp_status = $request->input('ins_comp_status');
        $insurance->updated_by = $currentUser->user_id;
        $insurance->updated_at = new DateTime;
        $insurance->save();
        return response()->json(['status'=>true, 'message'=>'InsuranceCompany Updated','response'=>compact('insurance')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $insurance = InsuranceCompany::find($id);
        if (empty($insurance)) {
            return response()->json(['status'=>false, 'message'=>'InsuranceCompany is not exist in our system','response'=>[]], 200);       
        }
        $insurance->delete();
        return response()->json(['status'=>true, 'message'=>'InsuranceCompany Deleted','response'=>compact('insurance')], 200);
    }
}
