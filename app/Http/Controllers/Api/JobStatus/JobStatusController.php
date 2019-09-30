<?php

namespace App\Http\Controllers\Api\JobStatus;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\JobStatus;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class JobStatusController extends Controller
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
        $jobStatuses = JobStatus::where(function ($query) use($param, $currentUser){
                            $query->where('comp_id', $currentUser->comp_id);
                            if (!empty($param)) {
                                $query->where('name', 'LIKE', '%'.$param.'%');
                            }
                        })->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Job Status', 'response'=>compact('jobStatuses')], 200);
    }
    
    public function getAllJobStatus()
    {
        $currentUser = getApiCurrentUser();
        $jobStatuses = JobStatus::where('comp_id', $currentUser->comp_id)->get();
        return response()->json(['status'=>true, 'message'=>'All Job Status', 'response'=>compact('jobStatuses')], 200);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function storeRules($id= null)
    {
        $rules = [];
        if (!$id) {
            $rules['name'] = 'required|string|max:255|unique:job_status';   
        } else {
            $rules['name'] = 'required|string|max:255|unique:job_status,name,'.$id.',status_id';
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
        $jobStatus = new JobStatus();
        $jobStatus->name = $request->input('name');
        $jobStatus->comp_id = $currentUser->comp_id;
        $jobStatus->created_at = new DateTime;
        $jobStatus->updated_at = new DateTime;    
        $jobStatus->save();

        return response()->json(['status'=>true, 'message'=>'Job Status Saved', 'response'=>compact('jobStatus')], 200);   
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $jobStatus = JobStatus::find($id);
        if (empty($jobStatus)) {
            return response()->json(['status'=>false, 'message'=>'Job status not exists', 'response'=>[]], 200);       
        }
        return response()->json(['status'=>true, 'message'=>'Job Status Saved', 'response'=>compact('jobStatus')], 200);   
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $jobStatus = JobStatus::find($id);
        if (empty($jobStatus)) {
            return response()->json(['status'=>false, 'message'=>'Job status not exists', 'response'=>[]], 200);       
        }
        return response()->json(['status'=>true, 'message'=>'Job Status Saved', 'response'=>compact('jobStatus')], 200);   
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
        $jobStatus = JobStatus::find($id);
        if (empty($jobStatus)) {
            return response()->json(['status'=>false, 'message'=>'Job status not exists', 'response'=>[]], 200);       
        }
        $jobStatus->name = $request->input('name');
        $jobStatus->updated_at = new DateTime;
        $jobStatus->save();

        return response()->json(['status'=>true, 'message'=>'Job Status Saved', 'response'=>compact('jobStatus')], 200);   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $jobStatus = JobStatus::find($id);
        if (empty($jobStatus)) {
            return response()->json(['status'=>false, 'message'=>'Job status not exists', 'response'=>[]], 200);       
        }
        $jobStatus->delete();
        return response()->json(['status'=>true, 'message'=>'Job Status Saved', 'response'=>compact('jobStatus')], 200);   
    }
}
