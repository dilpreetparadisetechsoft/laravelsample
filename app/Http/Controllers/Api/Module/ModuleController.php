<?php

namespace App\Http\Controllers\Api\Module;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules;
use Validator, DateTime, Config, Helpers;

class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = getApiCurrentUser();
        $modules = Modules::where('created_by', $currentUser->user_id)->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Modules', 'response'=>compact('modules')], 200);
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
            'name' => 'required|unique:modules'
        ];
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules());
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $module = new Modules();
        $module->name = $request->input('name');
        $module->type = 'new';
        $module->created_by = $currentUser->user_id;
        $module->created_at = new DateTime;
        $module->updated_at = new DateTime;
        $module->save();

        return response()->json(['status'=>true, 'message'=>'Module', 'response'=>compact('module')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $module = Modules::find($id);
        if (empty($module)) {
            return Response()->json(['status'=>false, 'message' => 'Module is not exists', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Module show', 'response'=>compact('module')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $module = Modules::find($id);
        if (empty($module)) {
            return Response()->json(['status'=>false, 'message' => 'Module is not exists', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Module show', 'response'=>compact('module')], 200);
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
        $validator = Validator::make($request->all(), self::storeRules());
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $module = Modules::find($id);
        if (empty($module)) {
            return Response()->json(['status'=>false, 'message' => 'Module is not exists', 'response' => []], 200);
        }
        $module->name = $request->input('name');
        $module->updated_at = new DateTime;
        $module->save();
        return response()->json(['status'=>true, 'message'=>'Module Updated', 'response'=>compact('module')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $module = Modules::find($id);
        if (empty($module)) {
            return Response()->json(['status'=>false, 'message' => 'Module is not exists', 'response' => []], 200);
        }
        if ($module->type == 'default') {
            return Response()->json(['status'=>false, 'message' => 'You dont have right to delete this module', 'response' => []], 200);
        }
        $module->delete();
        return response()->json(['status'=>true, 'message'=>'Module Deleted', 'response'=>compact('module')], 200);
    }
}
