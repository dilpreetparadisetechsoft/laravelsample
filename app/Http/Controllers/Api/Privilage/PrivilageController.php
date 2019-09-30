<?php

namespace App\Http\Controllers\Api\Privilage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Privilage;
use App\User;
use App\Modules;
use App\Branch;
use App\Roles;
use Validator, DateTime, Config, Helpers;
use Illuminate\Validation\Rule;

class PrivilageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = getApiCurrentUser();
        $privilages = Privilage::join('modules','modules.module_id','privilage.module_id')
                        ->join('users','users.user_id','privilage.user_id')
                        ->select('privilage.*', 'users.first_name as user_name', 'modules.name as module')
                        ->where('privilage.created_by', $currentUser->user_id)
                        ->paginate(dataPerPage());

        return response()->json(['status'=>true, 'message'=>'All Privilage', 'response'=>compact('privilages')], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public static function storeRules($module_id = null, $user_id = null)
    {
        return [
            'module_id' => [
                    'required',
                    Rule::exists('modules')->where(function ($query) use($module_id) {
                        $query->whereIn('module_id', $module_id);
                    }),
                ],
            'user_id' => [
                    'required',
                    Rule::exists('users')->where(function ($query) use($user_id) {
                        $query->whereIn('user_id', $user_id);
                    }),
                ],
        ];
    }
    public function store(Request $request)
    {

        $module_id = $request->input('module_id');
        $user_id = $request->input('user_id');
        $validator = Validator::make($request->all(), self::storeRules($module_id, $user_id));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }

        $currentUser = getApiCurrentUser();        
        
        if (!empty($user_id)) {
            foreach ($user_id as $user) {
                if(!empty($module_id)) {
                    foreach ($module_id as $module) {
                        if (!$privilage = Privilage::where('user_id', $user)->where('module_id',$module)->get()->first()) {
                            $privilage = new Privilage(); 
                            $privilage->type = 'new';
                            $privilage->created_by = $currentUser->user_id;
                            $privilage->created_at = new DateTime;   
                        }                       

                        $privilage->add = ($request->input('add') == true?'1':'0');
                        $privilage->edit = ($request->input('edit') == true?'1':'0');
                        $privilage->view = ($request->input('view') == true?'1':'0');
                        $privilage->delete = ($request->input('delete') == true?'1':'0');
                        $privilage->module_id = $module;
                        $privilage->user_id = $user;
                        $privilage->branch_ids = $request->input('branch_ids');
                        $privilage->updated_at = new DateTime;
                        
                        $privilage->save();               
                    }
                }        
            }    
        }
        return response()->json(['status'=>true, 'message'=>'Privilage Created', 'response'=>compact('privilage')], 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $privilage = Privilage::find($id);
        if (empty($privilage)) {
            return Response()->json(['status'=>false, 'message' => 'Privilage is not exists', 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        if (!Roles::where('role', 'admin')->where('role_id', $currentUser->role_id)->get()->first()) {
            $users = User::where('comp_id', $currentUser->comp_id)->get();
        }else{
            $users = User::all();
        }
        
        $modules = Modules::all();
        $branchs = Branch::where('comp_id', $currentUser->comp_id)->select('branch_id','branch_name')->get();
        $selectedBranchs = Branch::whereIn('branch_id', explode(',', $privilage->branch_ids))->select('branch_id','branch_name')->get();   
        return response()->json(['status'=>true, 'message'=>'Privilage show', 'response'=>compact('privilage','users','modules','branchs','selectedBranchs')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $privilage = Privilage::find($id);
        if (empty($privilage)) {
            return Response()->json(['status'=>false, 'message' => 'Privilage is not exists', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Privilage show', 'response'=>compact('privilage')], 200);
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
        $module_id = $request->input('module_id');
        $user_id = $request->input('user_id');
        $validator = Validator::make($request->all(), self::storeRules($module_id, $user_id));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        if (Privilage::where('user_id', $user_id)->where('module_id', $module_id)->where('privilage_id','!=', $id)->get()->first()) {
            return Response()->json(['status'=>false, 'message' => 'Already assigned this module to this user.', 'response' => []], 200);
        }
        $privilage = Privilage::find($id);
        if (empty($privilage)) {
            return Response()->json(['status'=>false, 'message' => 'Privilage is not exists', 'response' => []], 200);
        }
        
        $currentUser = getApiCurrentUser();
        
        $privilage->add = ($request->input('add') == true?'1':'0');
        $privilage->edit = ($request->input('edit') == true?'1':'0');
        $privilage->view = ($request->input('view') == true?'1':'0');
        $privilage->delete = ($request->input('delete') == true?'1':'0');
        $privilage->branch_ids = $request->input('branch_ids');
        $privilage->module_id = $module_id;
        $privilage->user_id = $user_id;
        $privilage->created_at = new DateTime;
        $privilage->save();

        return response()->json(['status'=>true, 'message'=>'Privilage Updated', 'response'=>compact('privilage')], 200);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $privilage = Privilage::find($id);
        if (empty($privilage)) {
            return Response()->json(['status'=>false, 'message' => 'Privilage is not exists', 'response' => []], 200);
        }
        $privilage->delete();

        return response()->json(['status'=>true, 'message'=>'Privilage Deleted', 'response'=>compact('privilage')], 200);   
    }

    public function getHelperData(){
        $currentUser = getApiCurrentUser();
        if (!Roles::where('role', 'admin')->where('role_id', $currentUser->role_id)->get()->first()) {
            $users = User::where('comp_id', $currentUser->comp_id)->get();
        }else{
            $users = User::all();
        }
        $modules = Modules::all();
        $branchs = Branch::where('comp_id', $currentUser->comp_id)->select('branch_id','branch_name')->get();
        return response()->json(['status'=>true, 'message'=>'Users and Modules', 'response'=>compact('modules','users','branchs')], 200);  
    }
}
