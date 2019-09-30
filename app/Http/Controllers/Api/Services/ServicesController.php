<?php

namespace App\Http\Controllers\Api\Services;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services;
use Validator, DateTime, Config, Helpers, Hash;
use Illuminate\Validation\Rule;
class ServicesController extends Controller
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
        $services = Services::where(function ($query) use($param, $currentUser){
                        $query->where('comp_id', $currentUser->comp_id);
                        if (!empty($param)) {
                            $query->where('serv_name', 'LIKE', '%'.$param.'%');
                        }
                    })->paginate(dataPerPage());
        return Response()->json(['status'=>true, 'message' => 'All Services.', 'response' => compact('services')], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	public static function storeRules($id = null)
    {
        $rules = [
			 'serv_status' => 'required|in:0,1',		
        ];
        if (!empty($id)) {
            $rules['serv_name'] = 'required|string|max:191|unique:services,serv_name,'.$id.',serv_id';
        }else{
            $rules['serv_name'] = 'required|string|max:191|unique:services';
        }
        return $rules;
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules());
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $uuid = createUuid($request->input('serv_name').date('YmdHis'));
        $serv_code = $uuid->string;
        $currentUser = getApiCurrentUser();
        $service = new Services();
		$service->serv_sno = $serv_code;
        $service->serv_name = $request->input('serv_name');
		$service->serv_status = $request->input('serv_status');
        $service->comp_id = $currentUser->comp_id;
		$service->created_by = $currentUser->user_id;
        $service->created_at = new DateTime;
        $service->updated_at = new DateTime;
		$service->updated_by = $currentUser->user_id;
        $service->save();

        return response()->json(['status'=>true, 'message'=>'Service Created', 'response'=>compact('service')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($serv_id)
    {
      $service = Services::find($serv_id);
		if (empty($service)) {
            return Response()->json(['status'=>false, 'message' => 'Services is not exists', 'response' => []], 200);
        }
        return Response()->json(['status'=>true, 'message' => 'Service', 'response' => compact('service')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($serv_id)
    {
       $service = Services::find($serv_id);
        if (empty($service)) {
            return Response()->json(['status'=>false, 'message' => 'Services is not exists', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Service', 'response'=>compact('service')], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $serv_id)
    {
        $validator = Validator::make($request->all(), self::storeRules($serv_id));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $service = Services::find($serv_id);
        $service->serv_name = $request->input('serv_name');
		$service->serv_status = $request->input('serv_status');
        $service->updated_at = new DateTime;
		$service->updated_by = $currentUser->user_id;
        $service->save();

        return response()->json(['status'=>true, 'message'=>'Service Updated', 'response'=>compact('service')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($serv_id)
    {
        $service = Services::find($serv_id);
        if (empty($service)) {
            return Response()->json(['status'=>false, 'message' => 'Service id is not exists', 'response' => []], 200);
        }
        $service->delete();
        return response()->json(['status'=>true, 'message'=>'Service Deleted', 'response'=>compact('service')], 200);
    }
}
