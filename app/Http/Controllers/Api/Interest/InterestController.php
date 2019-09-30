<?php

namespace App\Http\Controllers\Api\Interest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Interest;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class InterestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = getApiCurrentUser();
        $interests = Interest::where('comp_id', $currentUser->comp_id)->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Interest', 'response'=>compact('interests')], 200);
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
            'int_status' => 'required|in:0,1',
        ];
        
        if (!$id) {
            $rules['int_name'] = 'required|string|max:191|unique:interests';   
        } else {
            $rules['int_name'] = 'required|string|max:191|unique:interests,int_name,'.$id.',group_id';
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
        $uuid = createUuid($request->input('int_name').date('YmdHis'));
        $int_sno = $uuid->string;
        
        $interest = new Interest();
        $interest->comp_id = $currentUser->comp_id;
        $interest->int_sno = $int_sno;
        $interest->int_name = $request->input('int_name');
        $interest->int_status = $request->input('int_status');
        $interest->created_by = $currentUser->user_id;
        $interest->updated_by = $currentUser->user_id;
        $interest->created_at = new DateTime;
        $interest->updated_at = new DateTime;
        $interest->save();

        return response()->json(['status'=>true, 'message'=>'Interest Saved','response'=>compact('interest')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $interest = Interest::find($id);
        if (empty($interest)) {
            return response()->json(['status'=>false, 'message'=>'Interest is not exists in our system','response'=>[]], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Interest','response'=>compact('interest')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $interest = Interest::find($id);
        if (empty($interest)) {
            return response()->json(['status'=>false, 'message'=>'Interest is not exists in our system','response'=>[]], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Interest','response'=>compact('interest')], 200);
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
        $interest = Interest::find($id);
        if (empty($interest)) {
            return response()->json(['status'=>false, 'message'=>'Interest is not exists in our system','response'=>[]], 200);
        }
        $currentUser = getApiCurrentUser();
        $interest->int_name = $request->input('int_name');
        $interest->int_status = $request->input('int_status');
        $interest->updated_by = $currentUser->user_id;
        $interest->updated_at = new DateTime;
        $interest->save();

        return response()->json(['status'=>true, 'message'=>'Interest Updated','response'=>compact('interest')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $interest = Interest::find($id);
        if (empty($interest)) {
            return response()->json(['status'=>false, 'message'=>'Interest is not exists in our system','response'=>[]], 200);
        }
        $interest->delete();
        return response()->json(['status'=>true, 'message'=>'Interest','response'=>compact('interest')], 200);
    }
}
