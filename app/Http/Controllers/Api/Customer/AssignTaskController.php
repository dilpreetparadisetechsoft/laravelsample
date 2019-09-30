<?php

namespace App\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;
use App\User;
use App\AssignTask;
use App\Estimate;
use App\Roles;
use App\Events;
use App\Customer;
use App\EmailTemplate;
use App\Services;
use App\Notifications;
use App\AssignTaskComments;

class AssignTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $customer_id = null)
    {
        $currentUser = getApiCurrentUser();
        $tasks = self::getAssignedTask($request, $currentUser, $customer_id, '', '', dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Tasks', 'response'=>compact('tasks')], 200);
    }

    public function assignedByMe(Request $request)
    {
        $currentUser = getApiCurrentUser();
        $pagination = '-1';
        if ($request->input('itemType') == 'list') {
            $pagination = dataPerPage();
        }
        $event = false;
        $comment = true;
        $tasks = self::getAssignedTask($request, $currentUser, '', $currentUser->user_id, '', $pagination, $event, $comment);
        return response()->json(['status'=>true, 'message'=>'All Assigned By Me Tasks', 'response'=>compact('tasks')], 200);
    }

    public function assignedMe(Request $request)
    {
        $currentUser = getApiCurrentUser();
        $pagination = '-1';
        if ($request->input('itemType') == 'list') {
            $pagination = dataPerPage();
        }
        $event = false;
        $comment = true;
        $tasks = self::getAssignedTask($request, $currentUser, '', '', $currentUser->user_id, $pagination, $event, $comment);
        return response()->json(['status'=>true, 'message'=>'All Assigned Me Tasks', 'response'=>compact('tasks')], 200);
    }
    public function assignTaskGetTags()
    {
        $currentUser = getApiCurrentUser();
        $prefix = DB::getTablePrefix();
        $customerName = Customer::whereRaw($prefix.'customer.customer_id = '.$prefix.'estimate.customer_id')->select('customer_name')->limit(1);
        $estimates = Estimate::where('comp_id', $currentUser->comp_id)->select('estimate_id', DB::raw("({$customerName->toSql()}) as customerName"))->get();
        foreach ($estimates as $estimate) {
            $estimate->key = (string)$estimate->estimate_id;
            $estimate->value = (string)$estimate->estimate_id.' | '.$estimate->customerName;
            unset($estimate->estimate_id);
        }
        $role = Roles::whereRaw($prefix.'users.role_id = '.$prefix.'roles.role_id')->select('role')->limit(1);
        $users = User::where('comp_id', $currentUser->comp_id)->select('user_id', 'first_name', DB::raw("({$role->toSql()}) as role_name"))->get();
        foreach ($users as $user) {
            $user->key = (string)$user->user_id;
            $user->value = $user->first_name.' - '.$user->role_name;
            unset($user->user_id);
            unset($user->first_name);
            unset($user->role_name);
        }
        /*$customers = Customer::where('comp_id', $currentUser->comp_id)->select('customer_id','customer_name')->get();
        foreach ($customers as $customer) {
            $customer->key = (string)$customer->customer_id;
            $customer->value = $customer->customer_name;
            unset($customer->customer_id);
            unset($customer->customer_name);
        }*/
        $customers = [];
        return response()->json(['status'=>true, 'message'=>'All Assigned Me Tasks', 'response'=>compact('estimates','users','customers')], 200);   
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $currentUser = getApiCurrentUser();
        $estimates = Estimate::where('comp_id', $currentUser->comp_id)->select('estimate_id')->get();
        $prefix = DB::getTablePrefix();
        $role = Roles::whereRaw($prefix.'users.role_id = '.$prefix.'roles.role_id')->select('role')->limit(1);
        $users = User::where('comp_id', $currentUser->comp_id)->select('user_id', 'first_name', DB::raw("({$role->toSql()}) as role_name"))->get();
        return response()->json(['status'=>true, 'message'=>'All Estimates', 'response'=>compact('estimates','users')], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illumi$customer_idnate\Http\Response
     */
    public static function storeRules($request)
    {
        return [
            'estimate_id' => [
                'required',
                Rule::exists('estimate')->where(function ($query) use($request) {
                    $query->where('estimate_id', $request->input('estimate_id'));
                }),                
            ],  
            'title' => 'required',
            'representative' => 'required',
            'start_date' => 'required|date_format:Y-m-d',
            'due_date' => 'required|date_format:Y-m-d',
            'level' => 'required|in:Immediate,Important,Urgent,Moderate As Per Due Date',
            'notes' => 'required',
            
            'unit' => 'required',
            'guest_email' => 'email',
        ];
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();
        $uuid = createUuid($request->input('title').date('YmdHis'));
        $task_sno = $uuid->string;
        $assignTask = new AssignTask();
        $assignTask->task_sno = $task_sno;
        $assignTask->comp_id = $currentUser->comp_id;
        if (!empty($request->input('customer_id'))) {
            $assignTask->customer_id = $request->input('customer_id');    
        }elseif (empty($request->input('customer_id'))) {
            $assignTask->customer_id = Estimate::where('estimate_id', $request->input('estimate_id'))->select('customer_id')->get()->pluck('customer_id')->first(); 
        }        
        $assignTask->estimate_id = $request->input('estimate_id');
        $assignTask->title = $request->input('title');
        $assignTask->representative = $request->input('representative');
        $assignTask->start_date = $request->input('start_date');
        $assignTask->due_date = $request->input('due_date');
        $assignTask->level = $request->input('level');
        $assignTask->notes = $request->input('notes');
        $assignTask->inspection = (empty($request->input('inspection'))?0:1);
        $assignTask->customer_check = (empty($request->input('customer_check'))?0:1);
        $assignTask->unit = $request->input('unit');
        $assignTask->guest_email = $request->input('guest_email');
        $assignTask->status = 'Waiting';
        $assignTask->created_by = $currentUser->user_id;
        $assignTask->created_at = new DateTime;
        $assignTask->updated_at = new DateTime;
        $assignTask->updated_by = $currentUser->user_id;
        $assignTask->save();     
        $event = self::createEvent($currentUser, $request, $assignTask->task_id, $assignTask->customer_id); 

        $representatives = User::whereIn('user_id', explode(',',$assignTask->representative))->get();
        $firstRepresentative = $representatives->first();
        $customer = Customer::where('customer_id', $assignTask->customer_id)->get()->first();
        $service = Services::where('serv_id', $customer->serv_id)->select('serv_name')->get()->pluck('serv_name')->first();        
        $link = icsInvite($request, $customer);
        $taskData = [];
        $taskData['start_date']        = $assignTask->start_date;
        $taskData['customer_name']     = $customer->customer_name;
        $taskData['user_name']         = $firstRepresentative->first_name;
        $taskData['customer_address']  = $customer->customer_address;
        $taskData['mobile']            = $customer->phone;
        $taskData['notes']             = $assignTask->notes;
        $taskData['work_order_pdf']    = '&nbsp;';
        $taskData['icsInvite']         = '<a href="'.$link.'" >Click Here </a>';
        $taskData['event_time']        = $request->event_start_time;
        $taskData['assignby']          = $currentUser->first_name;
        $taskData['project_manager']   = '&nbsp;';
        $taskData['serv_name']         = $service;
        if ($request->input('customer_check') == 1) {
            $emailBody = emailTemplate($currentUser, 'inspection_email_template', $taskData);
        }else{
            $emailBody = emailTemplate($currentUser, 'assign_task_email_template', $taskData);
        }        
        $mailsTo = [];      
        foreach($representatives as $representative){
            $mailsTo[] = $representative->first_name;
            sendIcalEvent($request, $representative->first_name, $representative->email, $emailBody, $service, $customer, $currentUser);
        }  
        if ($request->input('inspection') == 1) {              
            $mailsTo = implode(",", $mailsTo);
            $file = inspectionpdf($firstRepresentative->email,$customer->customer_name,$customer->customer_address,$assignTask->start_date,$request->event_start_time,$mailsTo);
            $emailTo =$firstRepresentative->email;
            $emailFromName = $currentUser->first_name;
            $emailFromEmail ='CS@Canadarestorationservices.com';
            $EmailSubject = " Inspection Booking Confirmation  -Canada's Restoration Services ";
            $emailBody =" Dear".$customer->customer_name.",<br/>"." Please find the attached Inspection details.We thank you for the Opportunity,Canada's Restoration Services";
            $attachment = [asset($file)];
            SendEmail($emailTo, $EmailSubject, $emailBody, $attachment, $emailFromName, $emailFromEmail);
        }
        $tasks = self::getAssignedTask($request, $currentUser, $assignTask->customer_id, '', '', dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Tasks', 'response'=>compact('tasks')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $assignTask = AssignTask::find($id);
        if (empty($assignTask)) {
            return response()->json(['status'=>false, 'message'=>'Task is not exists in our system', 'response'=>[]], 200);
        }
        $currentUser = getApiCurrentUser();
        $event = Events::where('task_id', $assignTask->task_id)->get()->first();
        if (empty($event)) {
            $event = new Events();
        }
       $prefix = DB::getTablePrefix();
       $selectRole = Roles::whereRaw($prefix.'users.role_id = '.$prefix.'roles.role_id')->select('role')->limit(1);
       $selectdRepresentatives = User::whereIn('user_id', explode(',',$assignTask->representative))->select('user_id', 'first_name', DB::raw("({$selectRole->toSql()}) as role_name"))->get();

        $role = Roles::whereRaw($prefix.'users.role_id = '.$prefix.'roles.role_id')->select('role')->limit(1);
        $users = User::where('comp_id', $currentUser->comp_id)->select('user_id', 'first_name', DB::raw("({$role->toSql()}) as role_name"))->get();
        $estimates = Estimate::where('comp_id', $currentUser->comp_id)->select('estimate_id')->get();
        return response()->json(['status'=>true, 'message'=>'Assign Task', 'response'=>compact('assignTask','estimates','event','users','selectdRepresentatives')], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $assignTask = AssignTask::find($id);
        if (empty($assignTask)) {
            return response()->json(['status'=>false, 'message'=>'Task is not exists in our system', 'response'=>[]], 200);
        }
        $event = Events::where('task_id', $assignTask->task_id)->get()->first();
        if (empty($event)) {
            $event = new Events();
        }
        $currentUser = getApiCurrentUser();
        $estimates = Estimate::where('comp_id', $currentUser->comp_id)->select('estimate_id')->get();

        $prefix = DB::getTablePrefix();
        $selectRole = Roles::whereRaw($prefix.'users.role_id = '.$prefix.'roles.role_id')->select('role')->limit(1);
        $selectdRepresentatives = User::whereIn('user_id', explode(',',$assignTask->representative))->select('user_id', 'first_name', DB::raw("({$selectRole->toSql()}) as role_name"))->get();

        $role = Roles::whereRaw($prefix.'users.role_id = '.$prefix.'roles.role_id')->select('role')->limit(1);
        $users = User::where('comp_id', $currentUser->comp_id)->select('user_id', 'first_name', DB::raw("({$role->toSql()}) as role_name"))->get();
        return response()->json(['status'=>true, 'message'=>'Assign Task', 'response'=>compact('assignTask','estimates','event','users','selectdRepresentatives')], 200);
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
        $assignTask = AssignTask::find($id);
        if (empty($assignTask)) {
            return response()->json(['status'=>false, 'message'=>'Task is not exists in our system', 'response'=>[]], 200);
        }
        $currentUser = getApiCurrentUser();
        $oldRepresentatives = explode(',', $assignTask->representative);
        $newRepresentatives = explode(',', $request->input('representative'));
        if (!empty($request->input('customer_id'))) {
            $assignTask->customer_id = $request->input('customer_id');    
        }elseif (empty($request->input('customer_id'))) {
            $assignTask->customer_id = Estimate::where('estimate_id', $request->input('estimate_id'))->select('customer_id')->get()->pluck('customer_id')->first(); 
        }  
        $assignTask->estimate_id = $request->input('estimate_id');
        $assignTask->title = $request->input('title');
        $assignTask->representative = $request->input('representative');
        $assignTask->start_date = $request->input('start_date');
        $assignTask->due_date = $request->input('due_date');
        $assignTask->level = $request->input('level');
        $assignTask->notes = $request->input('notes');
        $assignTask->inspection = $request->input('inspection');
        $assignTask->customer_check = $request->input('customer_check');
        $assignTask->unit = $request->input('unit');
        $assignTask->guest_email = $request->input('guest_email');
        $assignTask->status = 1;
        $assignTask->updated_at = new DateTime;
        $assignTask->updated_by = $currentUser->user_id;
        $assignTask->save();
        $cancelMailReprestative = [];
        foreach ($oldRepresentatives as $oldRepresentative) {
            if (!in_array($oldRepresentative, $newRepresentatives)) {
                $cancelMailReprestative[] = $oldRepresentative;
            }
        }       
        $event = self::createEvent($currentUser, $request, $assignTask->task_id, $assignTask->customer_id);
        $representatives = User::whereIn('user_id', explode(',',$assignTask->representative))->get();
        $firstRepresentative = $representatives->first();
        $customer = Customer::where('customer_id', $assignTask->customer_id)->get()->first();
        $service = Services::where('serv_id',$customer->serv_id)->select('serv_name')->get()->pluck('serv_name')->first();
        if (!empty($cancelMailReprestative) && is_array($cancelMailReprestative)) {
            $cancelRepresentatives = User::whereIn('user_id', $cancelMailReprestative)->get();
            foreach ($cancelRepresentatives as $cancelRepresentative) {
                self::sendCancelEmail($cancelRepresentative, $assignTask, $service, $currentUser);   
            }            
        }
        $link = icsInvite($request, $customer);
        $taskData = [];
        $taskData['start_date']        = $assignTask->start_date;
        $taskData['customer_name']     = $customer->customer_name;
        $taskData['user_name']         = $firstRepresentative->first_name;
        $taskData['customer_address']  = $customer->customer_address;
        $taskData['mobile']            = $customer->phone;
        $taskData['notes']             = $assignTask->notes;
        $taskData['work_order_pdf']    = '&nbsp;';
        $taskData['icsInvite']         = '<a href="'.$link.'" >Click Here </a>';
        $taskData['event_time']        = $event->event_start_time;
        $taskData['assignby']          = $currentUser->first_name;
        $taskData['project_manager']   = '&nbsp;';
        $taskData['serv_name']         = $service;
        if ($request->input('customer_check') == 1) {
            $emailBody = self::emailTemplate($currentUser, 'inspection_email_template', $taskData);
        }else{
            $emailBody = self::emailTemplate($currentUser, 'assign_task_email_template', $taskData);
        }        
        $mailsTo = [];      
        foreach($representatives as $representative){
            $mailsTo[] = $representative->first_name;
            sendIcalEvent($request, $representative->first_name, $representative->email, $emailBody, $service, $customer, $currentUser);
        }  
        if ($request->input('inspection') == 1) {              
            $mailsTo = implode(",", $mailsTo);
            $file = inspectionpdf($firstRepresentative->email,$customer->customer_name,$customer->customer_address,$assignTask->start_date,$event->event_start_time,$mailsTo);
            $emailTo =$firstRepresentative->email;
            $emailFromName = $currentUser->first_name;
            $emailFromEmail ='CS@Canadarestorationservices.com';
            $EmailSubject = " Inspection Booking Confirmation  -Canada's Restoration Services ";
            $emailBody =" Dear".$customer->customer_name.",<br/>"." Please find the attached Inspection details.We thank you for the Opportunity,Canada's Restoration Services";
            $attachment = [asset($file)];
            SendEmail($emailTo, $EmailSubject, $emailBody, $attachment, $emailFromName, $emailFromEmail);
        }
        $tasks = self::getAssignedTask($request, $currentUser, $assignTask->customer_id, '', '', dataPerPage());
        return response()->json(['status'=>true, 'message'=>'All Tasks', 'response'=>compact('tasks')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $assignTask = AssignTask::find($id);
        if (empty($assignTask)) {
            return response()->json(['status'=>false, 'message'=>'Task is not exists in our system', 'response'=>[]], 200);
        }
        $assignTask->delete();
        return self::index($assignTask->customer_id);
    }

    public static function sendCancelEmail($cancelRepresentative, $assignTask, $service, $currentUser)
    {
        $taskData['first_name'] = $cancelRepresentative->first_name;
        $taskData['SERV_NAME']=$service;
        $body = EmailTemplate($currentUser, 'cancelled_email_template',$taskData);
        $emailTo = $cancelRepresentative->email;
        $emailFromName = $currentUser->first_name;
        $emailFromEmail ='CS@Canadarestorationservices.com';
        $EmailSubject = " Event Canceled !";
        $emailBody= $body['body'];
        SendEmail($emailTo, $EmailSubject, $emailBody, '', $emailFromName, $emailFromEmail);
    }

    public static function storeEventRules($request)
    {
        return [
            'event_start_time' => 'required|date_format:H:i',
            'event_end_time' => 'required|date_format:H:i',
        ];
    }
    public static function createEvent($currentUser, $request, $task_id,$customer_id)
    {
        /*$validator = Validator::make($request->all(), self::storeEventRules($request));
        if ($validator->fails()) {
            return ;
        }*/
        if(!$event = Events::where('event_id', $request->input('event_id'))->get()->first())
        {
            $customer = Customer::where('customer_id', $customer_id)->select('customer_name', 'email', 'customer_address')->get()->first();
            $event = new Events();
            $event->status = '1';
            $event->created_by = $currentUser->user_id;
            $event->created_at = new DateTime;
            $event->comp_id = $currentUser->comp_id;
            $event->task_id = $task_id;
            $event->event_loc = $customer->customer_address;
            $event->guests = $customer->email;
        }
        $event->event_name = $request->input('title');
        $event->event_date = $request->input('start_date');

        if ($request->input('event_start_time')) {
            $event->event_start_time = $request->input('event_start_time');
        }else{
            $event->event_start_time = '00:00';
        }
        if ($request->input('event_end_time')) {
            $event->event_end_time = $request->input('event_end_time');
        }else{
            $event->event_end_time = '00:00';
        }

        $event->event_type = $request->input('event_type');
        $event->meeting_with = $request->input('representative');
        $event->note = $request->input('notes');
        $event->updated_by = $currentUser->user_id;
        $event->updated_at = new DateTime;
        $event->save();
        return $event;
    }
    public static function getAssignedTask($request, $currentUser, $customer_id = null, $created_by = null, $representative_id = null, $paginate = '-1', $event = false, $comment = false)
    {        
        $tasks = AssignTask::join('customer', 'customer.customer_id', 'assign_task.customer_id')
                    ->where(function ($query) use($customer_id, $created_by, $representative_id, $request){
                        if (!empty($customer_id)) {
                            $query->where('assign_task.customer_id', $customer_id);
                        }
                        $param = $request->input('param');
                        if (!empty($param)) {
                            $query->where('assign_task.title', 'LIKE', '%'.$param.'%');
                        }
                        if (!empty($created_by)) {
                            $query->where('assign_task.created_by', $created_by);
                        }
                        if (!empty($representative_id)) {
                            $query->whereRaw('find_in_set('.$representative_id.', representative)');
                        }
                        if (!empty($request->input('status'))) {
                            if ($request->input('status') == 'Completed') {
                                $query->where('assign_task.status', 'Completed');
                            }elseif ($request->input('status') == 'Today') {
                                $query->where('assign_task.due_date', date('Y-m-d'));
                            }elseif ($request->input('status') == 'OverDue') {
                                $query->where('assign_task.due_date', '<', date('Y-m-d'));
                                $query->where('assign_task.status', '!=', 'Completed');
                            }
                        }
                        if ($request->input('searchQ')) {
                            $query->where('assign_task.title' ,'LIKE','%'.$request->input('searchQ').'%');
                        }
                    })
                    ->select(
                        'assign_task.*',
                        'customer.customer_name',
                        'customer.phone',
                        'customer.email'
                    )
                    ->where('assign_task.comp_id', $currentUser->comp_id)
                    ->orderBy('assign_task.task_id', 'DESC');
        if ($paginate == '-1') {
            $tasks = $tasks->get();
        }else{
            $tasks = $tasks->paginate($paginate);
        }
        
        foreach ($tasks as &$task) {
            $representative = User::whereIn('user_id', explode(',',$task->representative))
                                ->select('first_name')
                                ->pluck('first_name')
                                ->toArray();
            if (!empty($representative)) {                
                $task->representative = implode(', ', $representative);
            }
            $task->assigned_by = User::where('user_id', $task->created_by)->get()->pluck('first_name')->first();
            $task->notes = strip_tags($task->notes);  
            if ($event == true) {
                $task->events = Events::where('task_id', $task->task_id)->get();    
            }    
            if ($comment == true) {
                $task->comments = AssignTaskComments::join('users','users.user_id','assign_task_comments.created_by')
                                    ->where('task_id', $task->task_id)
                                    ->select('users.first_name as created_by_name','assign_task_comments.*')
                                    ->orderBy('task_comment_id', 'DESC')
                                    ->get();
            }
            $task->created_days = getDuration($task->created_at);
        }
        return $tasks;
    }
    public function updateStatusAssignTask(Request $request)
    {
        $tasks = $request->input('tasks');
        if (!empty($tasks) && is_array($tasks)) {
            foreach ($tasks as $task) {
                if($assignTask = AssignTask::where('task_id', $task['task_id'])->get()->first()) {
                    if ($assignTask->status != $task['status']) {
                        $assignTask->status = $task['status'];
                        $assignTask->updated_at = new DateTime;
                        $assignTask->save();
                    }
                }
            }
        }
        return response()->json(['status'=>true, 'message'=>'Task status updated', 'response'=>[]], 200);
    }
    public function saveTaskComment(Request $request)
    {
        $commentHtml = $request->input('comments');
        $currentUser = getApiCurrentUser();
        $dom = new \DomDocument();
        $dom->loadHTML($commentHtml);
        $output = array();

        $user_ids = [];
        $status = [];
        $status[] = [
            'user_id' => (string)$currentUser->user_id,
            'read' => 0
        ];
        foreach ($dom->getElementsByTagName('a') as $item) {  
            if ($item->getAttribute('data-user_id')) {
                $user_ids[] = $item->getAttribute('data-user_id');      
                $status[] = [
                    'user_id' => $item->getAttribute('data-user_id'),
                    'read' => 0
                ];
            }
        }
        
        $comment = new AssignTaskComments();
        $comment->user_id = $currentUser->user_id;
        $comment->task_id = $request->input('task_id');
        $comment->comments = $request->input('comments');
        if ($request->file('file') != null) {
            $comment->file = fileuploadExtra($request, 'file');    
        }
        $comment->created_by = $currentUser->user_id;
        $comment->created_at = new DateTime;
        $comment->updated_at = new DateTime;
        $comment->save();

        $activity = 'Your are mention in a comment added by '.$currentUser->first_name;
        $notification = new Notifications();
        $notification->task_id = $request->input('task_id');
        $notification->user_ids = implode(',', $user_ids);
        $notification->activity = $activity;
        $notification->type = 'task';
        $notification->status = json_encode($status);
        $notification->created_by = $currentUser->user_id;
        $notification->created_at = new DateTime;
        $notification->updated_at = new DateTime;
        $notification->save();

        $comments = AssignTaskComments::join('users','users.user_id','assign_task_comments.created_by')
                                    ->where('task_id', $comment->task_id)
                                    ->select('users.first_name as created_by_name','assign_task_comments.*')
                                    ->orderBy('task_comment_id', 'DESC')
                                    ->get();

        return response()->json(['status'=>true, 'message'=>'Comment addedd successfully', 'response'=>compact('comments')], 200);   
    }
}
