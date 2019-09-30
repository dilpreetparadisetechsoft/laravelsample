<?php

namespace App\Http\Controllers\Api\Template;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Template;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $param = $request->input('param');
        $currentUser = getApiCurrentUser();
        $templates = Template::where(function ($query) use($param, $currentUser){
                            $query->where('comp_id', $currentUser->comp_id);
                            if (!empty($param)) {
                                $query->where('temp_name', 'LIKE', '%'.$param.'%');
                            }
                        })->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Templates', 'response'=>compact('templates')], 200);
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
			 'temp_subject' => 'string|max:191',
			 'temp_content' => 'required|string',
			 'temp_type' => 'required|string|max:191',	
             'temp_status' => 'required|in:0,1',			 
        ];

        if ( empty( $id ) ) {
            $rules['temp_name'] = 'required|string|max:191|unique:template';
        } else {
            $rules['temp_name'] = 'required|string|max:191|unique:template,temp_name,'.$id.',temp_id';
        }
        return $rules;
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules());
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $template = new Template();
        $uuid = createUuid($request->input('temp_name').date('YmdHis'));
        $temp_sno = $uuid->string;
        $template->temp_sno = $temp_sno;
        $template->temp_name = $request->input('temp_name');
		$template->temp_subject = $request->input('temp_subject');
		$template->temp_content = $request->input('temp_content');
		$template->temp_type = $request->input('temp_type');
		$template->temp_status = $request->input('temp_status');
        $template->comp_id = $currentUser->comp_id;
		$template->created_by = $currentUser->user_id;
        $template->created_at = new DateTime;
        $template->updated_at = new DateTime;
		$template->updated_by = $currentUser->user_id;
        $template->save();

        return response()->json(['status'=>true, 'message'=>'Template Created', 'response'=>compact('template')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($temp_id)
    {
        $template = Template::find($temp_id);
		if (empty($template)) {
            return Response()->json(['status'=>false, 'message' => 'Template is not exists', 'response' => []], 200);
        }
        return Response()->json(['status'=>true, 'message' => 'Template', 'response' => compact('template')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($temp_id)
    {
        $template = Template::find($temp_id);
        if (empty($template)) {
            return Response()->json(['status'=>false, 'message' => 'Template is not exists', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Template', 'response'=>compact('template')], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $temp_id)
    {
        $template = Template::find($temp_id);
        if (empty($template)) {
            return Response()->json(['status'=>false, 'message' => 'Template is not exists', 'response' => []], 200);
        }
        $validator = Validator::make($request->all(), self::storeRules($temp_id));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
		$currentUser = getApiCurrentUser();
		$template = Template::find($temp_id);
        $template->temp_name = $request->input('temp_name');
		$template->temp_subject = $request->input('temp_subject');
		$template->temp_content = $request->input('temp_content');
		$template->temp_type = $request->input('temp_type');
		$template->temp_status = $request->input('temp_status');
        $template->updated_at = new DateTime;
		$template->updated_by = $currentUser->user_id;
        $template->save();

        return response()->json(['status'=>true, 'message'=>'Template Updated', 'response'=>compact('template')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($temp_id)
    {
        $template = Template::find($temp_id);
        if (empty($template)) {
            return Response()->json(['status'=>false, 'message' => 'Template is not exists', 'response' => []], 200);
        }
        $template->delete();
        return response()->json(['status'=>true, 'message'=>'Template Deleted', 'response'=>compact('template')], 200);
    }
}
