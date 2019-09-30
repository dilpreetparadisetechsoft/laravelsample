<?php

namespace App\Http\Controllers\Api\Occupation;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Occupation;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class OccupationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = getApiCurrentUser();
        $occupations = Occupation::where('comp_id', $currentUser->comp_id)->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Occupations', 'response'=>compact('occupations')], 200);
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
            'occ_status' => 'required|in:0,1',
        ];
        
        if (!$id) {
            $rules['occ_name'] = 'required|string|max:191|unique:occupations';   
        } else {
            $rules['occ_name'] = 'required|string|max:191|unique:occupations,occ_name,'.$id.',occ_id';
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
        $uuid = createUuid($request->input('occ_name').date('YmdHis'));
        $occ_sno = $uuid->string;
        
        $occupation = new Occupation();
        $occupation->comp_id = $currentUser->comp_id;
        $occupation->occ_sno = $occ_sno;
        $occupation->occ_name = $request->input('occ_name');
        $occupation->occ_status = $request->input('occ_status');
        $occupation->created_by = $currentUser->user_id;
        $occupation->updated_by = $currentUser->user_id;
        $occupation->created_at = new DateTime;
        $occupation->updated_at = new DateTime;
        $occupation->save();

        return response()->json(['status'=>true, 'message'=>'Occupation Saved','response'=>compact('occupation')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $occupation = Occupation::find($id);
        if (empty($occupation)) {
            return response()->json(['status'=>false, 'message'=>'Occupation is not exists in our system','response'=>[]], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Occupation','response'=>compact('occupation')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $occupation = Occupation::find($id);
        if (empty($occupation)) {
            return response()->json(['status'=>false, 'message'=>'Occupation is not exists in our system','response'=>[]], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Occupation','response'=>compact('occupation')], 200);
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
        $occupation = Occupation::find($id);
        if (empty($occupation)) {
            return response()->json(['status'=>false, 'message'=>'Occupation is not exists in our system','response'=>[]], 200);
        }
        $currentUser = getApiCurrentUser();
        $occupation->occ_name = $request->input('occ_name');
        $occupation->occ_status = $request->input('occ_status');
        $occupation->updated_by = $currentUser->user_id;
        $occupation->updated_at = new DateTime;
        $occupation->save();

        return response()->json(['status'=>true, 'message'=>'Occupation Updated','response'=>compact('occupation')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $occupation = Occupation::find($id);
        if (empty($occupation)) {
            return response()->json(['status'=>false, 'message'=>'Occupation is not exists in our system','response'=>[]], 200);
        }
        $occupation->delete();
        return response()->json(['status'=>true, 'message'=>'Occupation Deleted','response'=>compact('occupation')], 200);
    }
}
