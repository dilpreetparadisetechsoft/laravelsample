<?php

namespace App\Http\Controllers\Api\Tax;
use App\Http\Controllers\Controller;
use App\Tax;
use Illuminate\Http\Request;
use Validator, DateTime, Config, Helpers;
use Illuminate\Validation\Rule;

class Taxcontroller extends Controller
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
        $taxs = Tax::where(function ($query) use($param, $currentUser){
                        $query->where('comp_id', $currentUser->comp_id);
                        if (!empty($param)) {
                            $query->where('name', 'LIKE', '%'.$param.'%');
                        }
                    })->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Tax', 'response'=>compact('taxs')], 200);
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
			 'tax' => 'required|numeric',
             'name' => 'required|string|max:191',
        ];
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules());
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();    
        $tax = new Tax();
		$tax->tax = $request->input('tax');
        $tax->name = $request->input('name');
        $tax->comp_id = $currentUser->comp_id;
        $tax->created_at = new DateTime;
        $tax->updated_at = new DateTime;
        $tax->save();

        return response()->json(['status'=>true, 'message'=>'Tax Created', 'response'=>compact('tax')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($tax_id)
    {
        $tax = Tax::find($tax_id);
		if (empty($tax)) {
            return Response()->json(['status'=>false, 'message' => 'Tax is not exists', 'response' => []], 200);
        }
        return Response()->json(['status'=>true, 'message' => 'Tax', 'response' => compact('tax')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($tax_id)
    {
        $tax = Tax::find($tax_id);
        if (empty($tax)) {
            return Response()->json(['status'=>false, 'message' => 'Tax is not exists', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Tax', 'response'=>compact('tax')], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $tax_id)
    {
        $validator = Validator::make($request->all(), self::storeRules());
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $tax = Tax::find($tax_id);
		$tax->tax = $request->input('tax');
        $tax->name = $request->input('name');
        $tax->updated_at = new DateTime;
        $tax->save();
        return response()->json(['status'=>true, 'message'=>'Tax Created', 'response'=>compact('tax')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($tax_id)
    {
        $tax = Tax::find($tax_id);
        if (empty($tax)) {
            return Response()->json(['status'=>false, 'message' => 'Tax is not exists', 'response' => []], 200);
        }
        $tax->delete();
        return response()->json(['status'=>true, 'message'=>'Deleted', 'response'=>compact('tax')], 200);
    }
}
