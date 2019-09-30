<?php

namespace App\Http\Controllers\Api\Building;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Building;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class BuildingController extends Controller
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
        $buildings = Building::where(function ($query) use($param, $currentUser){
                        $query->where('comp_id', $currentUser->comp_id);
                        if (!empty($param)) {
                            $query->where('build_name', 'LIKE', '%'.$param.'%');
                        }
                    })->paginate(dataPerPage());
        return Response()->json(['status'=>true, 'message' => 'Get all Building.', 'response' => compact('buildings')], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function storeRules($request, $id = null)
    {
        $rules = [
            'build_status' => 'required|in:0,1'
        ];
        if ($id) {
            $rules['build_name'] = 'required|string|max:191|unique:building,build_name,'.$id.',build_id';
        }else{
            $rules['build_name'] = 'required|string|unique:building|max:191';
        }
        return $rules;
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }        

        $uuid = createUuid($request->input('build_name').date('YmdHis'));
        $build_sno = $uuid->string;

        $currentUser = getApiCurrentUser();

        $building = new Building(); 
        $building->build_sno = $build_sno;
        $building->build_name = $request->input('build_name');
        $building->comp_id = $currentUser->comp_id;
        $building->build_status = $request->input('build_status');
        $building->created_by = $currentUser->user_id;
        $building->updated_by = $currentUser->user_id;
        $building->created_at = new DateTime;
        $building->updated_at = new DateTime;
        
        $building->save();
        return Response()->json(['status'=>true, 'message' => 'Save Building.', 'response' => compact('building')], 200);   
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $building = Building::find($id); 
        if (empty($building)) {
            return Response()->json(['status'=>false, 'message' => 'Building not exists.', 'response' => []], 200);
        }
        return Response()->json(['status'=>true, 'message' => 'Building.', 'response' => compact('building')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $building = Building::find($id); 
        if (empty($building)) {
            return Response()->json(['status'=>false, 'message' => 'Building not exists.', 'response' => []], 200);
        }
        return Response()->json(['status'=>true, 'message' => 'Building.', 'response' => compact('building')], 200);
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

        $building = Building::find($id); 
        if (empty($building)) {
            return Response()->json(['status'=>false, 'message' => 'Building not exists.', 'response' => []], 200);
        }
        
        $currentUser = getApiCurrentUser();
        $building->build_name = $request->input('build_name');
        $building->build_status = $request->input('build_status');
        $building->updated_by = $currentUser->user_id;
        $building->updated_at = new DateTime;
        
        $building->save();
        return Response()->json(['status'=>true, 'message' => 'Update Building.', 'response' => compact('building')], 200); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $building = Building::find($id); 
        if (empty($building)) {
            return Response()->json(['status'=>false, 'message' => 'Building not exists.', 'response' => []], 200);
        }
        $building->delete();
        return Response()->json(['status'=>true, 'message' => 'Building Deleted.', 'response' => compact('building')], 200);
    }
}
