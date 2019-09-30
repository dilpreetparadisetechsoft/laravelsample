<?php

namespace App\Http\Controllers\Api\Notification;

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

class NotificationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $type = $request->input('type');
        $currentUser = getApiCurrentUser();
        $prefix = DB::getTablePrefix();
        $taskTitle = AssignTask::whereRaw($prefix.'notifications.task_id = '.$prefix.'assign_task.task_id')->select('title')->limit(1);
        if ($type == 'unread') {
            $where = '{"user_id": "'.$currentUser->user_id.'", "read": 0}';    
        } else {
            $where = '{"user_id": "'.$currentUser->user_id.'"}';
        }
        
        $notifications = Notifications::where(function($query) use ($currentUser){
                                $query->orwhereRaw('FIND_IN_SET('.$currentUser->user_id.', user_ids)');
                                $query->orwhere('created_by', $currentUser->user_id);
                            })
                            ->whereRaw("(JSON_CONTAINS(status, '".$where."'))")
                            ->select('notifications.*', DB::raw("({$taskTitle->toSql()}) as taskTitle"))
                            ->orderBy('notification_id', 'DESC')
                            ->paginate(5);
        return response()->json(['status'=>true, 'message'=>'All Tasks', 'response'=>compact('notifications')], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illumi$customer_idnate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::storeRules($request));
        if ($validator->fails()) {
            return Response()->json(['status'=>false, 'message' => $validator->getMessageBag()->first(), 'response' => []], 200);
        }
        $currentUser = getApiCurrentUser();

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
        $notification = Notifications::find($id);
        if (empty($notification)) {
            return response()->json(['status'=>true, 'message'=>'Something went wrong, Please try after sometime', 'response'=>[]], 200);
        }
        $notificationStatus = json_decode($notification->status);
        $currentUser = getApiCurrentUser();
        if (!empty($notificationStatus) && is_array($notificationStatus)) {
            foreach ($notificationStatus as &$notificationStatu) {
                if($notificationStatu->user_id == $currentUser->user_id)
                {
                    $notificationStatu->read = 1;
                }
            }
        }
        $notification->status = json_encode($notificationStatus);
        $notification->save();
        
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
        $taskEvent = AssignTask::join('customer', 'customer.customer_id', 'assign_task.customer_id')
                ->select(
                    'assign_task.*',
                    'customer.customer_name',
                    'customer.phone',
                    'customer.email'
                )
                ->where('assign_task.comp_id', $currentUser->comp_id)
                ->where('assign_task.task_id', $notification->task_id)
                ->get()->first();
        if ($taskEvent) {
            $representative = User::whereIn('user_id', explode(',',$taskEvent->representative))
                                ->select('first_name')
                                ->pluck('first_name')
                                ->toArray();
            if (!empty($representative)) {                
                $taskEvent->representative = implode(', ', $representative);
            }
            $taskEvent->assigned_by = User::where('user_id', $taskEvent->created_by)->get()->pluck('first_name')->first();
            $taskEvent->notes = strip_tags($taskEvent->notes);  
            $taskEvent->comments = AssignTaskComments::join('users','users.user_id','assign_task_comments.created_by')
                                    ->where('task_id', $taskEvent->task_id)
                                    ->select('users.first_name as created_by_name','assign_task_comments.*')
                                    ->orderBy('task_comment_id', 'DESC')
                                    ->get();
            $taskEvent->created_days = getDuration($taskEvent->created_at);
        }
        
        /*$customers = Customer::where('comp_id', $currentUser->comp_id)->select('customer_id','customer_name')->get();
        foreach ($customers as $customer) {
            $customer->key = (string)$customer->customer_id;
            $customer->value = $customer->customer_name;
            unset($customer->customer_id);
            unset($customer->customer_name);
        }*/
        $customers = [];
        return response()->json(['status'=>true, 'message'=>'All Assigned Me Tasks', 'response'=>compact('estimates','users','customers','taskEvent')], 200);
    }

}
