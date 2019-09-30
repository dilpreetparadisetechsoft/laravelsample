<?php

namespace App\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;
use App\User;
use App\Customer;
use App\CustomerDocs;
use App\DocumentType;

class CustomerDocsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($customer_id = null)
    {
        $prefix = DB::getTablePrefix();
        $currentUser = getApiCurrentUser();
        $documentType = DocumentType::whereRaw($prefix.'document_type.doc_type_id = '.$prefix.'customer_docs.doc_type_id')->select('doc_type_name')->limit(1);
        $user = User::whereRaw($prefix.'users.user_id = '.$prefix.'customer_docs.created_by')->select('first_name')->limit(1);
        $customerDocs = CustomerDocs::select('*', DB::raw("({$documentType->toSql()}) as documentType"), DB::raw("({$user->toSql()}) as created_by"))->where('customer_id', $customer_id)->where('comp_id', $currentUser->comp_id)->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Customer Docs', 'response'=>compact('customerDocs')], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {        
        $currentUser = getApiCurrentUser();
        $documentTypes = DocumentType::where('comp_id', $currentUser->comp_id)->get();
        return response()->json(['status'=>true, 'message'=>'All Document Types', 'response'=>compact('documentTypes')], 200);
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
            'doc_type_id' => [
                'required',
                Rule::exists('document_type')->where(function ($query) use($request) {
                    $query->where('doc_type_id', $request->input('doc_type_id'));
                }),                
            ],
            'customer_id' => [
                'required',
                Rule::exists('customer')->where(function ($query) use($request) {
                    $query->where('customer_id', $request->input('customer_id'));
                }),                
            ],  
            'description' => 'required|string|max:191', 
            'status' => 'required|in:0,1',          
        ];
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $uuid = createUuid('customer_docs-'.date('YmdHis'));
        $cust_doc_sno = $uuid->string;
        $customerDoc = new CustomerDocs();
        $customerDoc->cust_doc_sno = $cust_doc_sno;
        $customerDoc->comp_id = $currentUser->comp_id;
        $customerDoc->doc_type_id = $request->input('doc_type_id');
        $customerDoc->customer_id = $request->input('customer_id');
        $customerDoc->description = $request->input('description');
        if ($request->file('file_name') != null) {
            $customerDoc->file_name = fileuploadExtra($request, 'file_name');
        } 
        $customerDoc->status = $request->input('status');        
        $customerDoc->created_by = $currentUser->user_id;
        $customerDoc->created_at = new DateTime;
        $customerDoc->updated_at = new DateTime;
        $customerDoc->updated_by = $currentUser->user_id;
        $customerDoc->save();

        return self::index($customerDoc->customer_id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $customerDoc = CustomerDocs::find($id);
        if (empty($customerDoc)) {
            return response()->json(['status'=>false, 'message'=>'Customer Doc is not exists in our system', 'response'=>[]], 200);       
        }
        $currentUser = getApiCurrentUser();
        $documentTypes = DocumentType::where('comp_id', $currentUser->comp_id)->get();
        return response()->json(['status'=>true, 'message'=>'Customer Doc', 'response'=>compact('customerDoc','documentTypes')], 200);       
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $customerDoc = CustomerDocs::find($id);
        if (empty($customerDoc)) {
            return response()->json(['status'=>false, 'message'=>'Customer Doc is not exists in our system', 'response'=>[]], 200);       
        }
        $currentUser = getApiCurrentUser();
        $documentTypes = DocumentType::where('comp_id', $currentUser->comp_id)->get();
        return response()->json(['status'=>true, 'message'=>'Customer Doc', 'response'=>compact('customerDoc','documentTypes')], 200);
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
        $customerDoc = CustomerDocs::find($id);
        if (empty($customerDoc)) {
            return response()->json(['status'=>false, 'message'=>'Customer Doc is not exists in our system', 'response'=>[]], 200);       
        }
        $currentUser = getApiCurrentUser();
        $customerDoc->doc_type_id = $request->input('doc_type_id');
        $customerDoc->customer_id = $request->input('customer_id');
        $customerDoc->description = $request->input('description');
        if ($request->file('file_name') != null) {
            $customerDoc->file_name = fileuploadExtra($request, 'file_name');
        }        
        $customerDoc->status = $request->input('status');      
        $customerDoc->updated_at = new DateTime;
        $customerDoc->updated_by = $currentUser->user_id;
        $customerDoc->save();
        return self::index($customerDoc->customer_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customerDoc = CustomerDocs::find($id);
        if (empty($customerDoc)) {
            return response()->json(['status'=>false, 'message'=>'Customer Doc is not exists in our system', 'response'=>[]], 200);       
        }
        $customerDoc->delete();
        return self::index($customerDoc->customer_id);
    }
}
