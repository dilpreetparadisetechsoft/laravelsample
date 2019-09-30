<?php

namespace App\Http\Controllers\Api\TaskType;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DateTime;
use App\TaskType;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
class TasktypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $param = $request->input('param');
        $currentUser = getApiCurrentUser();
        $tasktypes = TaskType::where(function ($query) use($param, $currentUser){
                            $query->where('comp_id', $currentUser->comp_id);
                            if (!empty($param)) {
                                $query->where('task_name', 'LIKE', '%'.$param.'%');
                            }
                        })->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Tasktype', 'response'=>compact('tasktypes')], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	 
	public static function storeRules()
    {
        return [
			 'task_name' => 'required',		
             'task_status' => 'required|in:0,1',				 
        ];
    }
    public function store(Request $request)
    {
        $comp_id = $request->input('comp_id');
        $validator = Validator::make($request->all(), self::storeRules());
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $tasktype = new TaskType();
        $currentUser = getApiCurrentUser();
		$tasktype->task_name = $request->input('task_name');
		$tasktype->task_status = $request->input('task_status');
        $tasktype->comp_id = $currentUser->comp_id;
        $tasktype->created_at = new DateTime;
        $tasktype->updated_at = new DateTime;
        $tasktype->save();

        return response()->json(['status'=>true, 'message'=>'task Type Created', 'response'=>compact('tasktype')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($task_id)
    {
        $tasktype = TaskType::find($task_id);
		if (empty($tasktype)) {
            return Response()->json(['status'=>false, 'message' => 'Tasktype is not exists', 'response' => []], 200);
        }
        return Response()->json(['status'=>true, 'message' => 'Tasktype', 'response' => compact('tasktype')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($task_id)
    {
       $tasktype = TaskType::find($task_id);
        if (empty($tasktype)) {
            return Response()->json(['status'=>false, 'message' => 'tasktype is not exists', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'tasktype', 'response'=>compact('tasktype')], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $task_id)
    {
        $validator = Validator::make($request->all(), self::storeRules());
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
		$tasktype = TaskType::find($task_id);
		$tasktype->task_name = $request->input('task_name');
		$tasktype->task_status = $request->input('task_status');
        $tasktype->updated_at = new DateTime;
        $tasktype->save();

        return response()->json(['status'=>true, 'message'=>'task Type Created', 'response'=>compact('tasktype')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($task_id)
    {
        $tasktype = TaskType::find($task_id);
        if (empty($tasktype)) {
            return Response()->json(['status'=>false, 'message' => 'tasktype id is not exists', 'response' => []], 200);
        }
        $tasktype->delete();
        return response()->json(['status'=>true, 'message'=>'Deleted', 'response'=>compact('tasktype')], 200);
    }
    
}
