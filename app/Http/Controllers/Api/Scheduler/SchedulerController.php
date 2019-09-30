<?php

namespace App\Http\Controllers\Api\Scheduler;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;
use App\User;
use App\Roles;
use App\AssignTask;
use App\Estimate;
use App\Events;
use App\Customer;
use App\Branch;
use App\Services;
use App\WorkOrder;
use App\AssignJobs;
use App\AssignJobsTech;
use App\Http\Controllers\Api\Customer\AssignTaskController as assignTaskC;


class SchedulerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $prefix = DB::getTablePrefix();
        $currentUser = getApiCurrentUser();
        $events = [];
        $inspectionCount = 0;
        $pickupCount = 0;
        $mettingCount = 0;
        $workOderCount = 0;
        $eventCount = 0;
        if (!empty($request->input('event_type'))) {
            $request->merge(['event_type' => explode(',',$request->input('event_type'))]);
        }else{
            $request->merge(['event_type' => []]);
        }

        if (empty($request->input('event_type'))) {
            $newEvents = self::getEvents($request, $currentUser, $prefix);

            if (!empty($newEvents)) {
                $mettingCount = count($newEvents);
                $events = array_merge($events, $newEvents);    
            }            
            
            $jobEvents = self::getAssignedJobs($request, $currentUser, $prefix);
            if (!empty($jobEvents)) {
                $inspectionCount = count($jobEvents);
                $events = array_merge($events, $jobEvents);
            }  

            $pickupEvents = self::getPickupJob($request, $currentUser, $prefix);
            if (!empty($pickupEvents)) {
                $pickupCount = count($pickupEvents);
                $events = array_merge($events, $pickupEvents);
            }          
            
            $tasksEvents = self::getAssignedTasks($request, $currentUser, $prefix);
            if (!empty($tasksEvents)) {
                $eventCount = count($tasksEvents);
                $events = array_merge($events, $tasksEvents);
            }            
            
            $workOrderEvents = self::getWorkOrders($request, $currentUser, $prefix);    
            if (!empty($workOrderEvents)) {
                $workOderCount = count($workOrderEvents);
                $events = array_merge($events, $workOrderEvents);
            }            
        }
        if (is_array($request->input('event_type'))) {
            if (in_array('Meeting', $request->input('event_type'))) {
                $newEvents = self::getEvents($request, $currentUser, $prefix);
                if (!empty($newEvents)) {
                    $mettingCount = count($newEvents);
                    $events = array_merge($events, $newEvents);    
                }
            }

            if (in_array('Inspection', $request->input('event_type'))) {
                $jobEvents = self::getAssignedJobs($request, $currentUser, $prefix);
                if (!empty($jobEvents)) {
                    $inspectionCount = count($jobEvents);
                    $events = array_merge($events, $jobEvents);
                }                
            }
            if (in_array('Pickup', $request->input('event_type'))) {
                $pickupEvents = self::getPickupJob($request, $currentUser, $prefix);
                if (!empty($pickupEvents)) {
                    $pickupCount = count($pickupEvents);
                    $events = array_merge($events, $pickupEvents);
                }                
            }
            if (in_array('Events', $request->input('event_type'))) {
                $tasksEvents = self::getAssignedTasks($request, $currentUser, $prefix);
                if(!empty($tasksEvents)) {
                    $eventCount = count($tasksEvents);
                    $events = array_merge($events, $tasksEvents);
                }
            }
            if (in_array('Work Order', $request->input('event_type'))) {
                $workOrderEvents = self::getWorkOrders($request, $currentUser, $prefix);  
                if (!empty($workOrderEvents)) {
                    $workOderCount = count($workOrderEvents);  
                    $events = array_merge($events, $workOrderEvents);
                }                
            }
        }

        $role = Roles::whereRaw($prefix.'users.role_id = '.$prefix.'roles.role_id')->select('role')->limit(1);
        $users = User::where('comp_id', $currentUser->comp_id)->select('user_id', 'first_name', DB::raw("({$role->toSql()}) as role_name"))->get();
        $branchs = Branch::where('comp_id', $currentUser->comp_id)->get();
        return response()->json(['status'=>true, 'message'=>'All Events', 'response'=>compact('events','users','branchs','mettingCount','inspectionCount','eventCount','workOderCount','pickupCount')], 200);        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $currentUser = getApiCurrentUser();
        $event = new Events();
        $event->status = '1';
        $event->created_by = $currentUser->user_id;
        $event->created_at = new DateTime;
        $event->comp_id = $currentUser->comp_id;
        $event->task_id = 0;
        $event->event_loc = $request->input('event_loc');
        $event->guests = $request->input('guests');
        $event->event_name = $request->input('event_name');

        $event->event_date = $request->input('event_date');

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
        $event->meeting_with = $request->input('meeting_with');
        $event->note = $request->input('note');
        $event->updated_by = $currentUser->user_id;
        $event->updated_at = new DateTime;
        $event->save();

        $link_data = [];
        $link_data['event_name'] = $request->input('event_name');
        $link_data['event_type'] = $request->input('event_type');
        $link_data['rep_id'] = $currentUser->user_id;
        $link_data['note'] = $request->input('note');

        $data = array(
            'subject'           =>  $request->input('event_name'),
            'START_TIME'        =>  $request->input('event_start_time'),
            'END_TIME'          =>  $request->input('event_end_time'),
            'event_time'        =>  $request->input('event_end_time'),
            'event_loc'         =>  $request->input('event_loc'),
            'event_date'        =>  $request->input('event_date'),
            'task_start_date'   =>  $request->input('event_date'),
            'task_end_date'     =>  $request->input('event_date'),
            'event_start_time'  =>  $request->input('event_start_time'),
            'event_end_time'    =>  $request->input('event_end_time'),
            'color'             =>  '#1a1aff',
            'guests'            =>  $request->input('guests')
        );
        $guest_name = '';
        $representatives = User::whereIn('user_id', explode(',', $request->input('meeting_with')))->get();
        if(!empty($representatives))
        {
            foreach($representatives as $representative)
            {
                $guest_name = $representative->first_name;
                $job_data['START_DATE'] = $request->input('event_date');
                $job_data['CUSTOMER_ADDRESS'] = $request->input('event_loc');
                $job_data['CUSTOMER_NAME'] = $representative->first_name;
                $job_data['EVENT_TYPE'] = $request->input('event_type');
                $job_data['NOTES'] = $request->input('note');
                $job_data['USER_NAME'] = $representative->first_name;
                $job_data['USER_EMAIL'] = $representative->email;
                $link_data['rep_id'] = $representative->user_id;
                mailSentToTechnician($currentUser, $request, $job_data, $link_data, $data);
            } 
        }
        $guests = explode(',', $request->input('guests'));
        if(!empty($guests) && is_array($guests))
        {
            foreach ($guests as $guest) {
                $job_data['CUSTOMER_ADDRESS'] = $request->input('event_loc');
                $job_data['CUSTOMER_NAME'] = $guest_name;
                $job_data['START_DATE'] = $request->input('event_date');
                $job_data['USER_NAME'] = 'guest';
                $job_data['USER_EMAIL'] =  $guest;
                $link_data['rep_id'] ='34343434';
                mailSentToTechnician($currentUser, $request, $job_data, $link_data, $data);
            }            
        }

        return response()->json(['status'=>true, 'message'=>'Event Saved', 'response'=>[]], 200);
    }

    public function estimatesScheduler($type = 'all')
    {
        $currentUser = getApiCurrentUser();
        $estimates = Estimate::join('services','services.serv_id','estimate.serv_id')
                    ->join('customer','customer.customer_id','estimate.customer_id')
                    ->where(function ($query) use($currentUser, $type){
                        $query->where('estimate.comp_id', $currentUser->comp_id);
                        if ($type == 'schedular') {
                            $scheduler_estimate_status = getSetting($currentUser->comp_id, 'scheduler_estimate_status');
                            $query->where('estimate.status_id', $scheduler_estimate_status);
                            $query->where('estimate.is_assigned', '1');
                        }elseif ($type == 'un-schedular') {
                            $unscheduler_estimate_status = getSetting($currentUser->comp_id, 'unscheduler_estimate_status');
                            $query->where('estimate.status_id', $unscheduler_estimate_status);
                            $query->where('estimate.is_assigned', '0');
                        }
                    })
                    ->select(
                        'services.serv_name as service_name',
                        'customer.customer_name',
                        'estimate.estimate_id'
                    )
                    ->get();
        return response()->json(['status'=>true, 'message'=>'Estimate', 'response'=>compact('estimates')], 200);   
    }
    public function singleEstimateScheduler($estimate_id = null)
    {        
        $estimate = self::getSingleEstimateScheduler($estimate_id);
        return response()->json(['status'=>true, 'message'=>'Estimate', 'response'=>$estimate], 200);      
    }
    public static function getSingleEstimateScheduler($estimate_id = null)
    {        
        $prefix = DB::getTablePrefix();
        $currentUser = getApiCurrentUser();
        $estimates = Estimate::join('services','services.serv_id','estimate.serv_id')
                    ->join('customer','customer.customer_id','estimate.customer_id')
                    ->leftjoin('work_orders','work_orders.estimate_id','estimate.estimate_id')
                    ->leftjoin('assign_jobs','assign_jobs.estimate_id','estimate.estimate_id')
                    ->where('estimate.estimate_id', $estimate_id)
                    ->select(
                        'estimate.estimate_sno',
                        'estimate.address',
                        'estimate.estimate_id',
                        'customer.customer_name',
                        'services.serv_name as service_name',
                        'work_orders.no_of_technicians',
                        'work_orders.total_days',
                        'work_orders.desc_of_job',
                        'work_orders.work_order_id',
                        'assign_jobs.no_of_days',
                        'assign_jobs.assign_job_id',
                        'assign_jobs.site_address',
                        'assign_jobs.assigned_user',
                        'assign_jobs.start_date',
                        'assign_jobs.pick_up_date',
                        'assign_jobs.title',
                        'assign_jobs.type',
                        'assign_jobs.total_days as job_total_days'
                    )
                    ->get();
        $singleEstimate = $estimates->first();
        $technicians = User::whereRaw(
                        'find_in_set('.$prefix.'users.user_id, '.$prefix.'assign_job_tech.job_techinians)'
                    )
                    ->select(DB::raw('GROUP_CONCAT(" ", '.$prefix.'users.first_name)'));
        foreach ($estimates as &$estimate) {            
            $estimate->technicians = AssignJobsTech::where('assign_job_id', $estimate->assign_job_id)
                        ->select(
                            'assign_job_tech.*',
                            DB::raw("({$technicians->toSql()}) as technician_names")
                        )->get();
        }

        return compact('singleEstimate','estimates');      
    }

    public static function getEvents($request, $currentUser, $prefix)
    {
        $events = Events::where(function($query) use ($currentUser){
            $query->orwhereRaw('find_in_set('.$currentUser->user_id.', meeting_with)');
            $query->orwhere('created_by', $currentUser->user_id);
        })
        ->where(function ($query) use($request){                        
            if (!empty($request->input('event_start_date')) && !empty($request->input('event_end_date'))) {
                $query->whereRaw('event_date >= "'.$request->input('event_start_date').'" AND event_date <= "'.$request->input('event_end_date').'"');
            }elseif (!empty($request->input('event_start_date'))) {
                $query->where('event_date', $request->input('event_start_date'));
            }elseif (!empty($request->input('event_end_date'))) {
                $query->where('event_date', $request->input('event_end_date'));
            }
            if ($request->input('branch')) {
                $users = User::where('branch_id', $request->input('branch'))->select('user_id')->get();
                $representative_ids = [];
                foreach ($users as $user) {
                    $representative_ids[] = $user->user_id;
                }
                if ($representative_ids) {
                    $query->whereRaw('CONCAT(",", `MEETING_WITH`, ",") REGEXP ",('.$representative_ids.'),"');
                }                                                        
            }
            if ($request->input('representative')) {
                $query->whereRaw('find_in_set('.$request->input('representative').', meeting_with)');
            }
            if ($request->input('event_type')) {
                if ($request->input('event_type') == 'metting') {
                    $query->whereIn('event_type', ['BD METTING','BD Meeting','Umbrella Meeting','Network Meeting']);
                }elseif ($request->input('event_type') == 'Events') {
                    
                }elseif ($request->input('event_type') == 'Meeting') {
                    $query->whereIn('event_type', 'LIKE', '%Meeting%');
                }

            }
        })->get()->toArray();
        $dataEvents = [];
        if (!empty($events)) {
            foreach ($events as $event) {
                $dataEvent = [];
                $dataEvent['id'] = $event['event_id'];
                $dataEvent['title'] = $event['event_name'];
                $dataEvent['description'] = $event['event_name'];
                $dataEvent['start'] = $event['event_date'];
                $dataEvent['end'] = $event['event_date'];
                $dataEvent['start_time'] = $event['event_start_time'];
                $dataEvent['end_time'] = $event['event_end_time'];
                $dataEvent['color'] = '#f39c12';
                $dataEvent['type'] = 'event';
                $dataEvent['textEscape'] = false;
                $dataEvent['overlap'] = $dataEvent;
                $dataEvents[] = $dataEvent;
            }
        }
        return $dataEvents;
    }
    public static function getAssignedJobs($request, $currentUser, $prefix)
    {
        $service_name = Services::join('estimate','estimate.serv_id', 'services.serv_id')
                            ->whereRaw($prefix.'estimate.estimate_id = '.$prefix.'assign_jobs.estimate_id')
                            ->select('services.serv_name')
                            ->limit(1);
        $events = AssignJobs::where(function($query) use ($currentUser){
            $query->orwhereRaw('find_in_set('.$currentUser->user_id.', assigned_user)');
            $query->orwhere('created_by', $currentUser->user_id);
        })
        ->where(function ($query) use($request){  
            $query->where('type', 'task');
            if (!empty($request->input('event_start_date')) && !empty($request->input('event_end_date'))) {
                $query->whereRaw('start_date >= "'.$request->input('event_start_date').'" AND start_date <= "'.$request->input('event_end_date').'"');
            }elseif (!empty($request->input('event_start_date'))) {
                $query->where('start_date', $request->input('event_start_date'));
            }elseif (!empty($request->input('event_end_date'))) {
                $query->where('start_date', $request->input('event_end_date'));
            }  

            if ($request->input('branch')) {
                $users = User::where('branch_id', $request->input('branch'))->select('user_id')->get();
                $representative_ids = [];
                foreach ($users as $user) {
                    $representative_ids[] = $user->user_id;
                }
                if ($representative_ids) {
                    $query->whereRaw('CONCAT(",", `assigned_user`, ",") REGEXP ",('.$representative_ids.'),"');
                }                                                        
            }
            if ($request->input('representative')) {
                $query->whereRaw('find_in_set('.$request->input('representative').', assigned_user)');
            }
        })
        ->select(
            'assign_jobs.*', 
             DB::raw("({$service_name->toSql()}) as service_name")
        )
        ->get()->toArray();

        $dataEvents = [];
        if (!empty($events)) {
            foreach ($events as $event) {
                $end_date = date('Y-m-d', strtotime($event['start_date']. ' + '.($event['no_of_days']-1).' days'));
                $dataEvent = [];
                $dataEvent['id'] = $event['assign_job_id'];
                $dataEvent['title'] = $event['title'].' '.$event['start_time'].' '.$event['end_time'];
                $dataEvent['description'] = "Job Name: ".$event["service_name"];
                $dataEvent['start'] = $event['start_date'].'T'.$event['start_time'];
                $dataEvent['end'] = $end_date.'T'.$event['start_time'];
                $dataEvent['start_time'] = $event['start_time'];
                $dataEvent['end_time'] = $event['end_time'];
                $dataEvent['color'] = '#dd4b39';
                $dataEvent['type'] = 'estimate';
                $dataEvent['textEscape'] = false;
                $dataEvent['overlap'] = $dataEvent;
                $dataEvents[] = $dataEvent;
            }
        }
        return $dataEvents;
    }
    public static function getPickupJob($request, $currentUser, $prefix)
    {
        $service_name = Services::join('estimate','estimate.serv_id', 'services.serv_id')
                            ->whereRaw($prefix.'estimate.estimate_id = '.$prefix.'assign_jobs.estimate_id')
                            ->select('services.serv_name')
                            ->limit(1);
        $events = AssignJobs::where(function($query) use ($currentUser){
            $query->orwhereRaw('find_in_set('.$currentUser->user_id.', assigned_user)');
            $query->orwhere('created_by', $currentUser->user_id);
        })
        ->where(function ($query) use($request){ 
            $query->where('type', 'pickup');
            if (!empty($request->input('event_start_date')) && !empty($request->input('event_end_date'))) {
                $query->whereRaw('start_date >= "'.$request->input('event_start_date').'" AND start_date <= "'.$request->input('event_end_date').'"');
            }elseif (!empty($request->input('event_start_date'))) {
                $query->where('start_date', $request->input('event_start_date'));
            }elseif (!empty($request->input('event_end_date'))) {
                $query->where('start_date', $request->input('event_end_date'));
            }  

            if ($request->input('branch')) {
                $users = User::where('branch_id', $request->input('branch'))->select('user_id')->get();
                $representative_ids = [];
                foreach ($users as $user) {
                    $representative_ids[] = $user->user_id;
                }
                if ($representative_ids) {
                    $query->whereRaw('CONCAT(",", `assigned_user`, ",") REGEXP ",('.$representative_ids.'),"');
                }                                                        
            }
            if ($request->input('representative')) {
                $query->whereRaw('find_in_set('.$request->input('representative').', assigned_user)');
            }
        })
        ->select(
            'assign_jobs.*', 
             DB::raw("({$service_name->toSql()}) as service_name")
        )
        ->get()->toArray();

        $dataEvents = [];
        if (!empty($events)) {
            foreach ($events as $event) {
                $end_date = date('Y-m-d', strtotime($event['start_date']. ' + '.($event['no_of_days']-1).' days'));
                $dataEvent = [];
                $dataEvent['id'] = $event['assign_job_id'];
                $dataEvent['title'] = $event['title'].' '.$event['start_time'].' '.$event['end_time'];
                $dataEvent['description'] = "Job Name: ".$event["service_name"];
                $dataEvent['start'] = $event['start_date'].'T'.$event['start_time'];
                $dataEvent['end'] = $end_date.'T'.$event['start_time'];
                $dataEvent['start_time'] = $event['start_time'];
                $dataEvent['end_time'] = $event['end_time'];
                $dataEvent['color'] = '#f54ce8';
                $dataEvent['type'] = 'pickup';
                $dataEvent['textEscape'] = false;
                $dataEvent['overlap'] = $dataEvent;
                $dataEvents[] = $dataEvent;
            }
        }
        return $dataEvents;
    }
    public static function getAssignedTasks($request, $currentUser, $prefix)
    {
        $events = AssignTask::where(function($query) use ($currentUser){
            $query->orwhereRaw('find_in_set('.$currentUser->user_id.', representative)');
            $query->orwhere('created_by', $currentUser->user_id);
        })
        ->where(function ($query) use($request){   
            if (!empty($request->input('event_start_date')) && !empty($request->input('event_end_date'))) {
                $query->whereRaw('start_date >= "'.$request->input('event_start_date').'" AND start_date <= "'.$request->input('event_end_date').'"');
            }elseif (!empty($request->input('event_start_date'))) {
                $query->where('start_date', $request->input('event_start_date'));
            }elseif (!empty($request->input('event_end_date'))) {
                $query->where('start_date', $request->input('event_end_date'));
            }


            if ($request->input('branch')) {
                $users = User::where('branch_id', $request->input('branch'))->select('user_id')->get();
                $representative_ids = [];
                foreach ($users as $user) {
                    $representative_ids[] = $user->user_id;
                }
                if ($representative_ids) {
                    $query->whereRaw('CONCAT(",", `representative`, ",") REGEXP ",('.$representative_ids.'),"');
                }                                                        
            }
            if ($request->input('representative')) {
                $query->whereRaw('find_in_set('.$request->input('representative').', representative)');
            }
        })
        ->get()->toArray();
        $dataEvents = [];
        if (!empty($events)) {
            foreach ($events as $event) {
                $dataEvent = [];
                $dataEvent['id'] = $event['task_id'];
                $dataEvent['title'] = 'Assigned Task:-  '.$event['title'];
                $dataEvent['description'] = "Assigned Job";
                $dataEvent['start'] = $event['start_date'];
                $dataEvent['start_time'] = '';
                $dataEvent['end_time'] = '';
                $dataEvent['end'] = $event['due_date'];
                $dataEvent['color'] = '#00c0ef';
                $dataEvent['type'] = 'task';
                $dataEvent['textEscape'] = false;
                $dataEvent['overlap'] = $dataEvent;
                $dataEvents[] = $dataEvent;
            }
        }
        return $dataEvents;
    }
    public static function getWorkOrders($request, $currentUser, $prefix)
    {
        $events = WorkOrder::where(function($query) use ($currentUser){
            $query->orwhereRaw('find_in_set('.$currentUser->user_id.', representative_id)');
            $query->orwhere('created_by', $currentUser->user_id);
        })
        ->where(function ($query) use($request){     
            if (!empty($request->input('event_start_date')) && !empty($request->input('event_end_date'))) {
                $query->whereRaw('preferred_date >= "'.$request->input('event_start_date').'" AND preferred_date <= "'.$request->input('event_end_date').'"');
            }elseif (!empty($request->input('event_start_date'))) {
                $query->where('preferred_date', $request->input('event_start_date'));
            }elseif (!empty($request->input('event_end_date'))) {
                $query->where('preferred_date', $request->input('event_end_date'));
            } 

            if ($request->input('branch')) {
                $users = User::where('branch_id', $request->input('branch'))->select('user_id')->get();
                $representative_ids = [];
                foreach ($users as $user) {
                    $representative_ids[] = $user->user_id;
                }
                if ($representative_ids) {
                    $query->whereRaw('CONCAT(",", `representative_id`, ",") REGEXP ",('.$representative_ids.'),"');
                }                                                        
            }
            if ($request->input('representative')) {
                $query->whereRaw('find_in_set('.$request->input('representative').', representative_id)');
            }
        })
        ->get()->toArray();
        $dataEvents = [];
        if (!empty($events)) {
            foreach ($events as $event) {
                $dataEvent = [];
                $dataEvent['id'] = $event['work_order_id'];
                $dataEvent['title'] = "Equipment Pick:- ".$event['wrk_sno'];
                $dataEvent['description'] = $event['desc_of_job'];
                $dataEvent['start'] = $event['preferred_date'];
                $dataEvent['start_time'] = '';
                $dataEvent['end_time'] = '';
                $dataEvent['end'] = $event['preferred_date'];
                $dataEvent['color'] = '#1d6f21';
                $dataEvent['type'] = 'wo';
                $dataEvent['textEscape'] = false;
                $dataEvent['overlap'] = $dataEvent;
                $dataEvents[] = $dataEvent;
            }
        }
        return $dataEvents;
    }
}
