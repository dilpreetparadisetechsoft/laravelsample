<?php

namespace App\Http\Controllers\Api\Location;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Tax;
use App\Locations;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;
use App\Countries;
use App\Cities;
use App\States;

class LocationController extends Controller
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
        $tax = Tax::whereRaw($prefix.'tax.tax_id = '.$prefix.'locations.tax_id')->select('name')->limit(1);
        $locations = Locations::select('locations.*', DB::raw("({$tax->toSql()}) as name"))
                        ->where(function ($query) use($param, $currentUser){
                            $query->where('comp_id', $currentUser->comp_id);
                            if (!empty($param)) {
                                $query->where('state', 'LIKE', '%'.$param.'%');
                            }
                        })->paginate(dataPerPage());
        return Response()->json(['status'=>true, 'message' => 'Locations', 'response' => compact('locations')], 200);
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
        ];
        $rules['tax_id'] = [
            Rule::exists('tax')->where(function ($query) use($request) {
                $query->where('tax_id', $request->input('tax_id'));
            }),
        ];
        $rules['country'] = 'required';
        $rules['state'] = 'required';
        //$rules['city'] = 'required';
        return $rules;
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        if (!empty(Locations::where('comp_id', $currentUser->comp_id)->where('tax_id', $request->input('tax_id'))->where('country', $request->input('country'))->where('state', $request->input('state'))->where('city', $request->input('city'))->get()->first())) {
            return Response()->json(['status'=>false, 'message' => 'Entery is already exists', 'response' => []], 200);
        }
        $location = new Locations();
        $location->comp_id = $currentUser->comp_id;
        $location->tax_id = $request->input('tax_id');
        $location->country = $request->input('country');
        $location->state = $request->input('state');
        $location->city = $request->input('city');
        $location->created_at = new DateTime;
        $location->updated_at = new DateTime;
        $location->save();
        return Response()->json(['status'=>true, 'message' => 'Location Saved', 'response' => compact('location')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $currentUser = getApiCurrentUser();
        $countries = getCountry();
        $taxs = Tax::where('comp_id', $currentUser->comp_id)->get();
        $location = Locations::find($id);
        if (empty($location)) {
            return Response()->json(['status'=>false, 'message' => 'Company is not exists', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Company show', 'response'=>compact('location','countries','taxs')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $currentUser = getApiCurrentUser();
        $countries = getCountry();
        $taxs = Tax::where('comp_id', $currentUser->comp_id)->get();
        $location = Locations::find($id);
        if (empty($company)) {
            return Response()->json(['status'=>false, 'message' => 'Company is not exists', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Company show', 'response'=>compact('location','countries','taxs')], 200);
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

        $location = Locations::find($id);
        if (empty($location)) {
            return Response()->json(['status'=>false, 'message' => 'Location is not exists', 'response' => []], 200);
        }

        $currentUser = getApiCurrentUser();
        if (!empty(Locations::where('comp_id', $currentUser->comp_id)->where('tax_id', $request->input('tax_id'))->where('country', $request->input('country'))->where('state', $request->input('state'))->where('city', $request->input('city'))->where('loc_id','!=', $id)->get()->first())) {
            return Response()->json(['status'=>false, 'message' => 'Entery is already exists', 'response' => []], 200);
        }
        $location->comp_id = $currentUser->comp_id;
        $location->tax_id = $request->input('tax_id');
        $location->country = $request->input('country');
        $location->state = $request->input('state');
        $location->city = $request->input('city');
        $location->created_at = new DateTime;
        $location->updated_at = new DateTime;
        $location->save();
        return Response()->json(['status'=>true, 'message' => 'Location Saved', 'response' => compact('location')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $location = Locations::find($id);
        if (empty($location)) {
            return Response()->json(['status'=>false, 'message' => 'Company is not exists', 'response' => []], 200);
        }
        $location->delete();
        return response()->json(['status'=>true, 'message'=>'Company show', 'response'=>compact('location')], 200);
    }

    public function getHelperData(){
        $currentUser = getApiCurrentUser();
        $countries = getCountry();
        $taxs = Tax::where('comp_id', $currentUser->comp_id)->get();
        return response()->json(['status'=>true,'message'=>'','response'=>compact('countries','taxs')],200); 
    }
}
