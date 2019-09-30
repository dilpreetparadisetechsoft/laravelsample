<?php

namespace App\Http\Controllers\Api\EmailAttachment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\EmailAttachment;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class EmailAttachmentController extends Controller
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
        $emailAttachments = EmailAttachment::where(function ($query) use($param, $currentUser){
                                $query->where('comp_id', $currentUser->comp_id);
                                if (!empty($param)) {
                                    $query->where('email_attach_name', 'LIKE', '%'.$param.'%');
                                }
                            })->paginate(dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Email Attachments', 'response'=>compact('emailAttachments')], 200);
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
            'email_attach_type' => 'required|string|max:191',
			'email_attach_status' => 'required|in:0,1',
			'email_attach_file' => 'required',
        ];
        if ( empty( $id ) ) {
            $rules['email_attach_name'] = 'required|string|max:191|unique:email_attachment';
        } else {
            $rules['email_attach_name'] = 'required|string|max:191|unique:email_attachment,email_attach_name,'.$id.',email_attach_id';
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
        $uuid = createUuid($request->input('email_attach_name').date('YmdHis'));
        $email_attach_sno = $uuid->string;

        $emailAttachment = new EmailAttachment();
        $emailAttachment->email_attach_sno = $email_attach_sno;
		$emailAttachment->email_attach_status = $request->input('email_attach_status');
        $emailAttachment->email_attach_name = $request->input('email_attach_name');
		$emailAttachment->email_attach_type = $request->input('email_attach_type');
        if ($request->file('email_attach_file') != null) {
            $emailAttachment->email_attach_file = fileuploadExtra($request, 'email_attach_file');
        }
        $emailAttachment->comp_id = $currentUser->comp_id;
		$emailAttachment->created_by = $currentUser->user_id;
        $emailAttachment->created_at = new DateTime;
        $emailAttachment->updated_at = new DateTime;
		$emailAttachment->updated_by = $currentUser->user_id;
        $emailAttachment->save();

        return response()->json(['status'=>true, 'message'=>'Email Attachment Created', 'response'=>compact('emailAttachment')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($email_attach_id)
    {
        $emailAttachment = EmailAttachment::find($email_attach_id);
		if (empty($emailAttachment)) {
            return Response()->json(['status'=>false, 'message' => 'Email Attachment is not exists', 'response' => []], 200);
        }
        return Response()->json(['status'=>true, 'message' => 'Email Attachment', 'response' => compact('emailAttachment')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($email_attach_id)
    {
        $emailAttachment = EmailAttachment::find($email_attach_id);
        if (empty($emailAttachment)) {
            return Response()->json(['status'=>false, 'message' => 'Email Attachment is not exists', 'response' => []], 200);
        }
        return response()->json(['status'=>true, 'message'=>'Email Attachment', 'response'=>compact('emailAttachment')], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateAttachment(Request $request, $email_attach_id)
    {
        $validator = Validator::make($request->all(), self::storeRules($email_attach_id));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
		
		$uuid = createUuid($request->input('email_attach_name').date('YmdHis'));
        $email_attach_sno = $uuid->string;
		
        $currentUser = getApiCurrentUser();
        $emailAttachment = EmailAttachment::find($email_attach_id);
		$emailAttachment->email_attach_sno = $email_attach_sno;
		$emailAttachment->email_attach_status = $request->input('email_attach_status');
        $emailAttachment->email_attach_name = $request->input('email_attach_name');
		$emailAttachment->email_attach_type = $request->input('email_attach_type');
        if ($request->file('email_attach_file') != null) {
            $emailAttachment->email_attach_file = fileuploadExtra($request, 'email_attach_file');
        }
        $emailAttachment->comp_id = $currentUser->comp_id;
		$emailAttachment->created_by = $currentUser->user_id;
        $emailAttachment->created_at = new DateTime;
        $emailAttachment->updated_at = new DateTime;
		$emailAttachment->updated_by = $currentUser->user_id;
        $emailAttachment->save();

        return response()->json(['status'=>true, 'message'=>'Email Attachment Created', 'response'=>compact('emailAttachment')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($email_attach_id)
    {
        $emailAttachment = EmailAttachment::find($email_attach_id);
        if (empty($emailAttachment)) {
            return Response()->json(['status'=>false, 'message' => 'Email Attachment id is not exists', 'response' => []], 200);
        }
        $emailAttachment->delete();
        return response()->json(['status'=>true, 'message'=>'Email Attachment Deleted', 'response'=>compact('emailAttachment')], 200);
    }
}
