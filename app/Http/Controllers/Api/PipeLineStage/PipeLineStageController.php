<?php

namespace App\Http\Controllers\Api\PipeLineStage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\PipeLineStage;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class PipeLineStageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = getApiCurrentUser();
        $pipeLineStages = PipeLineStage::where('comp_id', $currentUser->comp_id)->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Pipe Line Stages', 'response'=>compact('pipeLineStages')], 200);
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
            'pip_status' => 'required|in:0,1',
        ];
        
        if (!$id) {
            $rules['pip_name'] = 'required|string|max:191|unique:pipe_line_stages';   
        } else {
            $rules['pip_name'] = 'required|string|max:191|unique:pipe_line_stages,pip_name,'.$id.',pip_id';
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
        $uuid = createUuid($request->input('pip_name').date('YmdHis'));
        $pip_sno = $uuid->string;
        
        $pipeLineStage = new PipeLineStage();
        $pipeLineStage->comp_id = $currentUser->comp_id;
        $pipeLineStage->pip_sno = $pip_sno;
        $pipeLineStage->pip_name = $request->input('pip_name');
        $pipeLineStage->pip_status = $request->input('pip_status');
        $pipeLineStage->created_by = $currentUser->user_id;
        $pipeLineStage->updated_by = $currentUser->user_id;
        $pipeLineStage->created_at = new DateTime;
        $pipeLineStage->updated_at = new DateTime;
        $pipeLineStage->save();

        return response()->json(['status'=>true, 'message'=>'Pipe Line Stage Saved','response'=>compact('pipeLineStage')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pipeLineStage = PipeLineStage::find($id);
        if (empty($pipeLineStage)) {
            return response()->json(['status'=>false, 'message'=>'PipeLineStage is not exists in our system','response'=>[]], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Pipe Line Stage','response'=>compact('pipeLineStage')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pipeLineStage = PipeLineStage::find($id);
        if (empty($pipeLineStage)) {
            return response()->json(['status'=>false, 'message'=>'PipeLineStage is not exists in our system','response'=>[]], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Pipe Line Stage','response'=>compact('pipeLineStage')], 200);
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
        $pipeLineStage = PipeLineStage::find($id);
        if (empty($pipeLineStage)) {
            return response()->json(['status'=>false, 'message'=>'Pipe Line Stage is not exists in our system','response'=>[]], 200);
        }
        $currentUser = getApiCurrentUser();
        $pipeLineStage->pip_name = $request->input('pip_name');
        $pipeLineStage->pip_status = $request->input('pip_status');
        $pipeLineStage->updated_by = $currentUser->user_id;
        $pipeLineStage->updated_at = new DateTime;
        $pipeLineStage->save();

        return response()->json(['status'=>true, 'message'=>'Pipe Line Stage Updated','response'=>compact('pipeLineStage')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $pipeLineStage = PipeLineStage::find($id);
        if (empty($pipeLineStage)) {
            return response()->json(['status'=>false, 'message'=>'PipeLineStage is not exists in our system','response'=>[]], 200);
        }
        $pipeLineStage->delete();
        return response()->json(['status'=>true, 'message'=>'Pipe Line Stage Deleted','response'=>compact('pipeLineStage')], 200);
    }
}
