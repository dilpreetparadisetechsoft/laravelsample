<?php

namespace App\Http\Controllers\Api\Branch;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Branch;
use App\Tax;
use App\Locations;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;
use App\Countries;
use App\Cities;
use App\States;

class BranchController extends Controller
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
        $prefix = DB::getTablePrefix();
        $location = Locations::whereRaw($prefix.'locations.loc_id = '.$prefix.'branch.loc_id')
                        ->join('tax', 'tax.tax_id','locations.tax_id')
                        ->select(DB::RAW('CONCAT(country, " | ", state, " | ", name) as location'))
                        ->limit(1);
        $branchs = Branch::select('branch.*', DB::raw("({$location->toSql()}) as location"))
                        ->where(function ($query) use($param, $currentUser){
                            $query->where('comp_id', $currentUser->comp_id);
                            if (!empty($param)) {
                                $query->where('branch_name', 'LIKE', '%'.$param.'%');
                            }
                        })
                        ->paginate(dataPerPage());
        return Response()->json(['status'=>true, 'message' => 'Get all branch.', 'response' => compact('branchs')], 200);
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
            'loc_id' => [
                'required',
                Rule::exists('locations')->where(function ($query) use($request) {
                    $query->where('loc_id', $request->input('loc_id'));
                }),
            ], 
            'branch_status' => 'required|in:0,1',
            //'reg_id' => 'required'
        ];
        if ($id) {
            $rules['branch_name'] = 'required|string|max:191|unique:branch,branch_name,'.$id.',branch_id';
        }else{
            $rules['branch_name'] = 'required|string|unique:branch|max:191';
        }
        return $rules;
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }        

        $uuid = createUuid($request->input('branch_name').date('YmdHis'));
        $branch_sno = $uuid->string;

        $currentUser = getApiCurrentUser();

        $branch = new Branch(); 
        $branch->branch_sno = $branch_sno;
        $branch->branch_name = $request->input('branch_name');
        $branch->comp_id = $currentUser->comp_id;
        $branch->loc_id = $request->input('loc_id');
        $branch->branch_status = $request->input('branch_status');
        $branch->reg_id = $request->input('reg_id');
        $branch->created_by = $currentUser->user_id;
        $branch->updated_by = $currentUser->user_id;
        $branch->created_at = new DateTime;
        $branch->updated_at = new DateTime;
        
        $branch->save();
        return Response()->json(['status'=>true, 'message' => 'Save Branch.', 'response' => compact('branch')], 200);   
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $branch = Branch::find($id); 
        if (empty($branch)) {
            return Response()->json(['status'=>false, 'message' => 'Branch not exists.', 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $prefix = DB::getTablePrefix();
        $tax = Tax::whereRaw($prefix.'tax.tax_id = '.$prefix.'locations.tax_id')->select('name')->limit(1);
        $locations = Locations::select('locations.*', DB::raw("({$tax->toSql()}) as name"))->where('comp_id', $currentUser->comp_id)->get();
        return Response()->json(['status'=>true, 'message' => 'Branch.', 'response' => compact('branch','locations')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $branch = Branch::find($id); 
        if (empty($branch)) {
            return Response()->json(['status'=>false, 'message' => 'Branch not exists.', 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $prefix = DB::getTablePrefix();
        $tax = Tax::whereRaw($prefix.'tax.tax_id = '.$prefix.'locations.tax_id')->select('name')->limit(1);
        $locations = Locations::select('locations.*', DB::raw("({$tax->toSql()}) as name"))->where('comp_id', $currentUser->comp_id)->get();
        return Response()->json(['status'=>true, 'message' => 'Branch.', 'response' => compact('branch','locations')], 200);
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
                
        $uuid = createUuid($request->input('branch_name').date('YmdHis'));
        $branch_sno = $uuid->string;

        $currentUser = getApiCurrentUser();

        $branch = Branch::find($id); 
        if (empty($branch)) {
            return Response()->json(['status'=>false, 'message' => 'Branch not exists.', 'response' => []], 200);
        }
        $branch->branch_name = $request->input('branch_name');
        $branch->loc_id = $request->input('loc_id');
        $branch->branch_status = $request->input('branch_status');
        $branch->reg_id = $request->input('reg_id');
        $branch->updated_by = $currentUser->user_id;
        $branch->updated_at = new DateTime;
        
        $branch->save();
        return Response()->json(['status'=>true, 'message' => 'Update Branch.', 'response' => compact('branch')], 200); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $branch = Branch::find($id); 
        if (empty($branch)) {
            return Response()->json(['status'=>false, 'message' => 'Branch not exists.', 'response' => []], 200);
        }
        $branch->delete();
        return Response()->json(['status'=>true, 'message' => 'Branch Deleted.', 'response' => compact('branch')], 200);
    }
    public function getAllLocation()
    {
        $currentUser = getApiCurrentUser();
        $prefix = DB::getTablePrefix();
        $tax = Tax::whereRaw($prefix.'tax.tax_id = '.$prefix.'locations.tax_id')->select('name')->limit(1);
        $locations = Locations::select('locations.*', DB::raw("({$tax->toSql()}) as name"))->where('comp_id', $currentUser->comp_id)->get();
        return Response()->json(['status'=>true, 'message' => 'Locations', 'response' => compact('locations')], 200);
    }
}
