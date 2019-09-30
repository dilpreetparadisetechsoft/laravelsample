<?php

namespace App\Http\Controllers\Api\Networks;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Estimate;
use App\Networks;
use App\Branch;
use App\PipeLineStage;
use App\Occupation;
use App\Group;
use App\Interest;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class NetworksController extends Controller
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
        $networks = Networks::where(function ($query) use($param, $currentUser){
                        $query->where('comp_id', $currentUser->comp_id);
                        if (!empty($param)) {
                            $query->where('comp_name', 'LIKE', '%'.$param.'%');
                        }
                    })->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Networks', 'response'=>compact('networks')], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $currentUser = getApiCurrentUser();
        $branchs = Branch::where('comp_id', $currentUser->comp_id)->get();
        $pipeLineStages = PipeLineStage::where('comp_id', $currentUser->comp_id)->get();
        $occupations = Occupation::where('comp_id', $currentUser->comp_id)->get();
        $users = User::where('comp_id', $currentUser->comp_id)->get();
        $groups = Group::where('comp_id', $currentUser->comp_id)->get();
        $interests = Interest::where('comp_id', $currentUser->comp_id)->get();
        return response()->json(['status'=>true, 'message'=>'', 'response'=>compact('branchs','pipeLineStages','occupations','users','groups','interests')], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function storeRules($request, $id= null)
    {
        return [
            'comp_name' => 'required|string|max:191',
            'occ_id' => [
                'required',
                Rule::exists('occupations')->where(function ($query) use($request) {
                    $query->where('occ_id', $request->input('occ_id'));
                }),
            ],
            'branch_id' => [
                'required',
                Rule::exists('branch')->where(function ($query) use($request) {
                    $query->where('branch_id', $request->input('branch_id'));
                }),
            ],
            'pip_id' => [
                'required',
                Rule::exists('pipe_line_stages')->where(function ($query) use($request) {
                    $query->where('pip_id', $request->input('pip_id'));
                }),
            ],
            'user_id' => [
                'required',
                Rule::exists('users')->where(function ($query) use($request) {
                    $query->where('user_id', $request->input('user_id'));
                }),
            ],
            'group_id' => [
                'required',
                Rule::exists('groups')->where(function ($query) use($request) {
                    $query->where('group_id', $request->input('group_id'));
                }),
            ],
            'int_id' => [
                'required',
                Rule::exists('interests')->where(function ($query) use($request) {
                    $query->where('int_id', $request->input('int_id'));
                }),
            ],
            'address' => 'required',
            'postal_code' => 'required',
            'website' => 'required',
            'email' => 'required|email',
            'phone' => 'required|integer|digits_between:10,15',
            'network_status' => 'required|in:0,1',
        ];
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $uuid = createUuid($request->input('desc_of_job').date('YmdHis'));
        $network_sno = $uuid->string;

        $network = new Networks();
        $network->network_sno = $network_sno;
        $network->comp_id = $currentUser->comp_id;
        $network->occ_id = $request->input('occ_id');
        $network->branch_id = $request->input('branch_id');
        $network->pip_id = $request->input('pip_id');
        $network->user_id = $request->input('user_id');
        $network->group_id = $request->input('group_id');
        $network->int_id = $request->input('int_id');
        $network->comp_name = $request->input('comp_name');
        $network->address = $request->input('address');
        $network->postal_code = $request->input('postal_code');
        $network->website = $request->input('website');
        $network->email = $request->input('email');
        $network->phone = $request->input('phone');
        $network->contact_persons = maybe_encode($request->input('contact_persons'));
        $network->network_status = $request->input('network_status');
        $network->created_by = $currentUser->user_id;
        $network->updated_by = $currentUser->user_id;
        $network->created_at = new DateTime;
        $network->updated_at = new DateTime;
        $network->save();

        return response()->json(['status'=>true, 'message'=>'Network Saved','response'=>compact('network')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $network = Networks::find($id);
        if (empty($network)) {
            return Response()->json(['status'=>false, 'message' => 'Network is not exists in our system.', 'response' => []], 200);
        }
        $network->contact_persons = maybe_decode($network->contact_persons);
        $currentUser = getApiCurrentUser();
        $branchs = Branch::where('comp_id', $currentUser->comp_id)->get();
        $pipeLineStages = PipeLineStage::where('comp_id', $currentUser->comp_id)->get();
        $occupations = Occupation::where('comp_id', $currentUser->comp_id)->get();
        $users = User::where('comp_id', $currentUser->comp_id)->get();
        $groups = Group::where('comp_id', $currentUser->comp_id)->get();
        $interests = Interest::where('comp_id', $currentUser->comp_id)->get();
        return response()->json(['status'=>true, 'message'=>'Network','response'=>compact('network','branchs','pipeLineStages','occupations','users','groups','interests')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $network = Networks::find($id);
        if (empty($network)) {
            return Response()->json(['status'=>false, 'message' => 'Network is not exists in our system.', 'response' => []], 200);
        }
        $network->contact_persons = maybe_decode($network->contact_persons);
        $currentUser = getApiCurrentUser();
        $branchs = Branch::where('comp_id', $currentUser->comp_id)->get();
        $pipeLineStages = PipeLineStage::where('comp_id', $currentUser->comp_id)->get();
        $occupations = Occupation::where('comp_id', $currentUser->comp_id)->get();
        $users = User::where('comp_id', $currentUser->comp_id)->get();
        $groups = Group::where('comp_id', $currentUser->comp_id)->get();
        $interests = Interest::where('comp_id', $currentUser->comp_id)->get();
        return response()->json(['status'=>true, 'message'=>'Network','response'=>compact('network','branchs','pipeLineStages','occupations','users','groups','interests')], 200);
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
        $currentUser = getApiCurrentUser();

        $network = Networks::find($id);
        if (empty($network)) {
            return Response()->json(['status'=>false, 'message' => 'Network is not exists in our system.', 'response' => []], 200);
        }

        $network->occ_id = $request->input('occ_id');
        $network->branch_id = $request->input('branch_id');
        $network->pip_id = $request->input('pip_id');
        $network->user_id = $request->input('user_id');
        $network->group_id = $request->input('group_id');
        $network->int_id = $request->input('int_id');
        $network->comp_name = $request->input('comp_name');
        $network->address = $request->input('address');
        $network->postal_code = $request->input('postal_code');
        $network->language = $request->input('language');
        $network->website = $request->input('website');
        $network->email = $request->input('email');
        $network->phone = $request->input('phone');
        $network->contact_persons = maybe_encode($request->input('contact_persons'));
        $network->network_status = $request->input('network_status');        
        $network->updated_by = $currentUser->user_id;
        $network->updated_at = new DateTime;
        $network->save();

        return response()->json(['status'=>true, 'message'=>'Network Updated','response'=>compact('network')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $network = Networks::find($id);
        if (empty($network)) {
            return Response()->json(['status'=>false, 'message' => 'Network is not exists in our system.', 'response' => []], 200);
        }
        $network->delete();
        return response()->json(['status'=>true, 'message'=>'Network Updated','response'=>compact('network')], 200);
    }
}
