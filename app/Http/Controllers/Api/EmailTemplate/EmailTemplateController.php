<?php

namespace App\Http\Controllers\Api\EmailTemplate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\EmailTemplate;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class EmailTemplateController extends Controller
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
        $emailTemplates = EmailTemplate::where(function ($query) use($param, $currentUser){
                            $query->where('comp_id', $currentUser->comp_id);
                            if (!empty($param)) {
                                $query->where('email_temp_name', 'LIKE', '%'.$param.'%');
                            }
                        })->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Email Templates', 'response'=>compact('emailTemplates')], 200);
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
			 'email_temp_subject' => 'string|max:191',
			 'email_temp_message' => 'required|string',
			 'email_place_holder' => 'required|string|max:191',	
             'email_temp_status' => 'required|in:0,1',			 
        ];
        if ( empty( $id ) ) {
            $rules['email_temp_name'] = 'required|string|max:191|unique:email_template';
        } else {
            $rules['email_temp_name'] = 'required|string|max:191|unique:email_template,email_temp_name,'.$id.',email_temp_id';
        }
        return $rules;
    }
    public function store(Request $request)
    {
     $comp_id = $request->input('comp_id');
        $validator = Validator::make($request->all(), self::storeRules($comp_id));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $emailTemplate = new EmailTemplate();
        $uuid = createUuid($request->input('email_temp_name').date('YmdHis'));
        $email_temp_sno = $uuid->string;
        $emailTemplate->email_temp_sno = $email_temp_sno;
        $emailTemplate->email_temp_name = $request->input('email_temp_name');
		$emailTemplate->email_temp_subject = $request->input('email_temp_subject');
		$emailTemplate->email_temp_message = $request->input('email_temp_message');
		$emailTemplate->email_place_holder = $request->input('email_place_holder');
		$emailTemplate->email_temp_status = $request->input('email_temp_status');
        $emailTemplate->comp_id = $currentUser->comp_id;
		$emailTemplate->created_by = $currentUser->user_id;
        $emailTemplate->created_at = new DateTime;
        $emailTemplate->updated_at = new DateTime;
		$emailTemplate->updated_by = $currentUser->user_id;
        $emailTemplate->save();

        return response()->json(['status'=>true, 'message'=>'Email Template Created', 'response'=>compact('emailTemplate')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($email_temp_id)
    {
     $emailTemplate = EmailTemplate::find($email_temp_id);
		if (empty($emailTemplate)) {
            return Response()->json(['status'=>false, 'message' => 'Email Template is not exists', 'response' => []], 200);
        }
        return Response()->json(['status'=>true, 'message' => 'Email Template', 'response' => compact('emailTemplate')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($email_temp_id)
    {
        $emailTemplate = EmailTemplate::find($email_temp_id);
        if (empty($emailTemplate)) {
            return Response()->json(['status'=>false, 'message' => 'Email Template is not exists', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Email Template', 'response'=>compact('emailTemplate')], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $email_temp_id)
    {
        $validator = Validator::make($request->all(), self::storeRules($email_temp_id));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $emailTemplate = EmailTemplate::find($email_temp_id);
        if (empty($emailTemplate)) {
            return Response()->json(['status'=>false, 'message' => 'Email Template is not exists', 'response' => []], 200);
        }
		$currentUser = getApiCurrentUser();
        $emailTemplate->email_temp_name = $request->input('email_temp_name');
		$emailTemplate->email_temp_subject = $request->input('email_temp_subject');
		$emailTemplate->email_temp_message = $request->input('email_temp_message');
		$emailTemplate->email_place_holder = $request->input('email_place_holder');
		$emailTemplate->email_temp_status = $request->input('email_temp_status');
        $emailTemplate->updated_at = new DateTime;
		$emailTemplate->updated_by = $currentUser->user_id;
        $emailTemplate->save();

        return response()->json(['status'=>true, 'message'=>'Email Template Updated', 'response'=>compact('emailTemplate')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($email_temp_id)
    {
        $emailTemplate = EmailTemplate::find($email_temp_id);
        if (empty($emailTemplate)) {
            return Response()->json(['status'=>false, 'message' => 'Email Template is not exists', 'response' => []], 200);
        }
        $emailTemplate->delete();
        return response()->json(['status'=>true, 'message'=>'Email Template Deleted', 'response'=>compact('emailTemplate')], 200);
    }
}
