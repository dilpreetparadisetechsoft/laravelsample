<?php

namespace App\Http\Controllers\Api\Company;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Company;
use App\User;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = getApiCurrentUser();
        $prefix = DB::getTablePrefix();
        $user = User::whereRaw($prefix.'users.comp_id = '.$prefix.'company.comp_id')->select('first_name')->limit(1);
        $companies = Company::select('company.*', DB::raw("({$user->toSql()}) as name"))->where('created_by', $currentUser->user_id)->paginate(dataPerPage());

        return response()->json(['status'=>true, 'message'=>'All Company', 'response'=>compact('companies')], 200);
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
            'comp_logo' => 'required',
            'tag_line' => 'required',
            'comp_address' => 'required',
            'comp_gst_no' => 'required',
            'comp_pst_no' => 'required',
            'comp_qst_no' => 'required',
            'comp_status' => 'required|in:1,0',
            'finance_mail' => 'required|email',
        ];
        
        if (!$id) {
            $rules['comp_name'] = 'required|string|max:255|unique:company';            
            $rules['first_name'] = 'required|string|max:255';
            $rules['last_name'] = 'required|string|max:255';
            $rules['email'] = 'required|string|email|max:255|unique:users';
            $rules['phone'] = 'required|numeric|unique:users';
            $rules['active'] = 'required|in:0,1';
            $rules['role_id'] = [
                    'required',
                    Rule::exists('roles')->where(function ($query) use($request) {
                        $query->where('role_id', $request->input('role_id'));
                    }),
                ];
        } else {
            $rules['comp_name'] = 'required|string|max:255|unique:company,comp_name,'.$id.',comp_id';
        }
        return $rules;
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $uuid = createUuid($request->input('comp_name').date('YmdHis'));
        $comp_code = $uuid->string;
        $company = new Company();
        $company->comp_code = $comp_code;
        $company->comp_name = $request->input('comp_name');

        if ($request->file('comp_logo') != null) {
            $company->comp_logo = fileuploadExtra($request, 'comp_logo');
        }        
        $company->tag_line = $request->input('tag_line');
        $company->comp_address = $request->input('comp_address');
        $company->comp_gst_no = $request->input('comp_gst_no');
        $company->comp_pst_no = $request->input('comp_pst_no');
        $company->comp_qst_no = $request->input('comp_qst_no');
        $company->comp_status = $request->input('comp_status');
        $company->finance_mail = $request->input('finance_mail');
        $company->created_by = $currentUser->user_id;
        $company->updated_by = $currentUser->user_id;
        $company->created_at = new DateTime;
        $company->updated_at = new DateTime;
        $company->save();

        $activation_key = sha1(mt_rand(10000,99999).time().$request->input('email'));
        $uuid = createUuid($request->input('first_name').date('YmdHis'));
        $uuid = $uuid->string;
        $password = randomPassword();
        User::create([
            'uuid' => $uuid,
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'password' => Hash::make($password),
            'verify_code' => $activation_key,
            'active' => $request->input('active'),
            'role_id' => $request->input('role_id'),
            'report_to' => $request->input('report_to'),
            'comp_id' => $company->comp_id,
            'created_at' => new DateTime,
            'updated_at'=> new DateTime
        ]);
        $htmlmessage = 'new account created';
        SendEmail($request->input('email'),'New account',$htmlmessage,'');
        return response()->json(['status'=>true, 'message'=>'Company Created', 'response'=>compact('company')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $company = Company::find($id);
        if (empty($company)) {
            return Response()->json(['status'=>false, 'message' => 'Company is not exists', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Company show', 'response'=>compact('company')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $company = Company::find($id);
        if (empty($company)) {
            return Response()->json(['status'=>false, 'message' => 'Company is not exists', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Company edit', 'response'=>compact('company')], 200);
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
        $validator = Validator::make($request->all(), self::storeRules($request, $id));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $company = Company::find($id);
        if (empty($company)) {
            return Response()->json(['status'=>false, 'message' => 'Company is not exists', 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $company->comp_name = $request->input('comp_name');
        if ($request->file('comp_logo') != null) {
            $company->comp_logo = fileuploadExtra($request, 'comp_logo');
        } 
        $company->tag_line = $request->input('tag_line');
        $company->comp_address = $request->input('comp_address');
        $company->comp_gst_no = $request->input('comp_gst_no');
        $company->comp_pst_no = $request->input('comp_pst_no');
        $company->comp_qst_no = $request->input('comp_qst_no');
        $company->comp_status = $request->input('comp_status');
        $company->finance_mail = $request->input('finance_mail');
        $company->updated_by = $currentUser->user_id;
        $company->updated_at = new DateTime;
        $company->save();

        return response()->json(['status'=>true, 'message'=>'Company Updated', 'response'=>compact('company')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $company = Company::find($id);
        if (empty($company)) {
            return Response()->json(['status'=>false, 'message' => 'Company is not exists', 'response' => []], 200);
        }
        $company->delete();
        return response()->json(['status'=>true, 'message'=>'Deleted', 'response'=>compact('company')], 200);
    }
}
