<?php

namespace App\Http\Controllers\Api\DocumentType;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;
use App\DocumentType;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class DcoumentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $param = $request->input('param');
        $currentUser = getApiCurrentUser();
        $documents = DocumentType::where(function ($query) use($param, $currentUser){
                        $query->where('comp_id', $currentUser->comp_id);
                        if (!empty($param)) {
                            $query->where('doc_type_name', 'LIKE', '%'.$param.'%');
                        }
                    })->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Documents', 'response'=>compact('documents')], 200);
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
            'doc_type_status' => 'required|in:0,1',
        ];
        
        if (!$id) {
            $rules['doc_type_name'] = 'required|string|max:255|unique:document_type';   
        } else {
            $rules['doc_type_name'] = 'required|string|max:255|unique:document_type,doc_type_name,'.$id.',doc_type_id';
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
        $uuid = createUuid($request->input('comp_name').date('YmdHis'));
        $document_code = $uuid->string;

        $document = new DocumentType();
        $document->doc_type_sno = $document_code;
        $document->doc_type_name = $request->input('doc_type_name');
        $document->doc_type_status = $request->input('doc_type_status');
        $document->comp_id = $currentUser->comp_id;
        $document->created_by = $currentUser->user_id;
        $document->updated_by = $currentUser->user_id;
        $document->created_at = new DateTime;
        $document->updated_at = new DateTime;
        $document->save();

        return Response()->json(['status'=>true, 'message' => 'Document Saved', 'response' => compact('document')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $document = DocumentType::find($id);
        if (empty($document)) {
            return Response()->json(['status'=>false, 'message' => 'Document is not exists.', 'response' => []], 200);
        }
        return Response()->json(['status'=>true, 'message' => 'Document', 'response' => compact('document')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $document = DocumentType::find($id);
        if (empty($document)) {
            return Response()->json(['status'=>false, 'message' => 'Document is not exists.', 'response' => []], 200);
        }
        return Response()->json(['status'=>true, 'message' => 'Document', 'response' => compact('document')], 200);
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
        $document = DocumentType::find($id);
        if (empty($document)) {
            return Response()->json(['status'=>false, 'message' => 'Document is not exists.', 'response' => []], 200);
        }
        $validator = Validator::make($request->all(), self::storeRules($id));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();

        $document->doc_type_name = $request->input('doc_type_name');
        $document->doc_type_status = $request->input('doc_type_status');
        $document->updated_by = $currentUser->user_id;
        $document->updated_at = new DateTime;
        $document->save();
        return Response()->json(['status'=>true, 'message' => 'Document Updated', 'response' => compact('document')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $document = DocumentType::find($id);
        if (empty($document)) {
            return Response()->json(['status'=>false, 'message' => 'Document is not exists.', 'response' => []], 200);
        }
        $document->delete();
        return Response()->json(['status'=>true, 'message' => 'Document Deleted', 'response' => compact('document')], 200);
    }
}
