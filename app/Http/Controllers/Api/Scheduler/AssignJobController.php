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
use App\Http\Controllers\Api\Scheduler\SchedulerController;


class AssignJobController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
            
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $assignJobValidation = self::assignJobValidation($request);
        if ($assignJobValidation['status'] == false) {
            return response()->json(['status'=>false, 'message'=>$assignJobValidation['message'], 'response'=>[]], 200);
        }

        $currentUser = getApiCurrentUser();

        $assignJobData = $assignJobValidation['assignJobData'];
        $assign_job_id = $request->input('assign_job_id');

        $assignjob = AssignJobs::findOrCreate($assign_job_id);
        $assignjob->comp_id = $currentUser->comp_id;
        $assignjob->estimate_id = $request->input('estimate_id');
        $assignjob->assigned_user = implode(',', (array)array_unique($assignJobData['technicians']));
        $assignjob->title = $request->input('title');
        $assignjob->no_of_days = $request->input('no_of_days');
        $assignjob->total_days = $assignJobData['total_days'];
        $assignjob->importance_level = $request->input('importance_level');
        $assignjob->site_address = $request->input('site_address');
        $assignjob->start_date = $assignJobData['job_date'];
        $assignjob->start_time = $assignJobData['start_time'];
        $assignjob->end_time = $assignJobData['end_time'];
        $assignjob->type = ($request->input('type')?$request->input('type'):'task');
        if ($request->input('pick_up_date')) {
            $assignjob->pick_up_date = $request->input('pick_up_date');    
        }

        $assignjob->notes = $request->input('notes');
        if (!$assign_job_id) {
            $assignjob->assign_job_sno = 'task-';
            $assignjob->created_by = $currentUser->user_id;
            $assignjob->created_at = new Datetime;
            $assignjob->status = 1;
            $assignjob->job_status = 'Assigned';
        }        

        $assignjob->updated_by = $currentUser->user_id;
        $assignjob->updated_at = new Datetime;
        $assignjob->save();

        if (!$assign_job_id) {
            $assignjob->assign_job_sno = 'task-'.$assignjob->assign_job_id;
            $assignjob->save();
        }

        $estimate = Estimate::find($assignjob->estimate_id);
        $estimate->status_id = getSetting($currentUser->comp_id, 'scheduler_estimate_status');
        $estimate->is_assigned = '1';
        $estimate->save();

        $job_technicians = (array)$request->input('job_technicians');
        $job_tech_ids = [];
        foreach ($job_technicians as $job_technician) {
            $job_tech_id = $request->input('job_tech_id');
            if (!$assignJobTech = AssignJobsTech::where('job_tech_id', $job_tech_id)->where('assign_job_id', $assignjob->assign_job_id)->get()->first()) {
                $assignJobTech = new AssignJobsTech();
                $assignJobTech->created_at = new Datetime; 
                $assignJobTech->assign_job_id = $assignjob->assign_job_id; 
            }
            $technicians = [];
            if (is_array($job_technician['job_techinians']) && !empty($job_technician['job_techinians'])) {
                foreach ($job_technician['job_techinians'] as $job_techinians) {
                    $technicians[] = $job_techinians['user_id'];
                }
            }
            $assignJobTech->job_techinians = implode(',', $technicians);
            $assignJobTech->job_date = $job_technician['job_date'];
            $assignJobTech->start_time = $job_technician['start_time'];
            $assignJobTech->end_time = $job_technician['end_time'];
            $assignJobTech->updated_at = new Datetime; 
            $assignJobTech->save();
            $job_tech_ids[] = $assignJobTech->job_tech_id;
        }
        if (!empty($job_tech_ids)) {
            AssignJobsTech::whereNotIn('job_tech_id', $job_tech_ids)
                ->where('assign_job_id', $assignjob->assign_job_id)
                ->delete();
        }
        $estimateData = SchedulerController::getSingleEstimateScheduler($assignjob->estimate_id);

        $link_data = [];
        $link_data['event_name'] = $request->input('title');
        $link_data['event_type'] = 'estimate';
        $link_data['rep_id'] = $currentUser->user_id;
        $link_data['note'] = $request->input('notes');

        $request->merge(['event_loc' => $estimateData['singleEstimate']->address]);
        $request->merge(['meeting_with' => implode(',',array_unique($assignJobData['technicians']))]);
        $request->merge(['note' => $request->input('notes')]);

        $data = array(
            'subject'           =>  $request->input('title'),
            'START_TIME'        =>  $assignJobData['start_time'],
            'END_TIME'          =>  $assignJobData['end_time'],
            'event_time'        =>  $assignJobData['end_time'],
            'event_loc'         =>  $estimateData['singleEstimate']->address,
            'event_date'        =>  $assignJobData['job_date'],
            'task_start_date'   =>  $assignJobData['job_date'],
            'task_end_date'     =>  $assignJobData['job_date'],
            'event_start_time'  =>  $assignJobData['start_time'],
            'event_end_time'    =>  $assignJobData['end_time'],
            'color'             =>  '#1a1aff',
            'guests'            =>  $request->input('guests')
        );

        $representatives = User::whereIn('user_id', array_unique($assignJobData['technicians']))->get();
        if(!empty($representatives))
        {
            foreach($representatives as $representative)
            {
                $job_data['START_DATE'] = $assignJobData['job_date'];
                $job_data['CUSTOMER_ADDRESS'] = $estimateData['singleEstimate']->address;
                $job_data['CUSTOMER_NAME'] = $representative->first_name;
                $job_data['EVENT_TYPE'] = 'estimate';
                $job_data['NOTES'] = $request->input('notes');
                $job_data['USER_NAME'] = $representative->first_name;
                $job_data['USER_EMAIL'] = $representative->email;
                $link_data['rep_id'] = $representative->user_id;
                mailSentToTechnicianAssignJob($currentUser, $request, $job_data, $link_data, $data);
            } 
        }

        return response()->json(['status'=>true, 'message'=>'Job saved succesfully.', 'response'=>$estimateData], 200);
    }
    public static function assignJobValidation($request)
    {
        $estimate_id = $request->input('estimate_id');
        $no_of_days = $request->input('no_of_days');
        $assign_job_id = $request->input('assign_job_id');
        $job_technicians = (array)$request->input('job_technicians');
        $workOrder = WorkOrder::where('estimate_id', $estimate_id)
                        ->select('total_days','no_of_technicians')
                        ->orderBy('work_order_id', 'DESC')
                        ->get()->first();
        $workOrderTotalDays = ($workOrder->total_days?$workOrder->total_days:0) * ($workOrder->no_of_technicians?$workOrder->no_of_technicians:0);
        $assignJobsTotalDays = AssignJobs::where(function ($query) use($estimate_id, $assign_job_id){
                            $query->where('estimate_id', $estimate_id);
                            if (!empty($assign_job_id)) {
                                $query->where('assign_job_id', '!=', $assign_job_id);
                            }
                        })
                        ->select(DB::RAW('SUM(total_days) AS total_days'))
                        ->get()->pluck('total_days')->first();
        $techTotalDays = 0;
        $assignJobData = [];
        $depth = 0;
        $technicians = [];
        foreach ($job_technicians as $job_technician) {
            if ($depth == 0) {
                $assignJobData['start_time'] = $job_technician['start_time'];
                $assignJobData['end_time'] = $job_technician['end_time'];
                $assignJobData['job_date'] = $job_technician['job_date'];
            }
            if (is_array($job_technician['job_techinians']) && !empty($job_technician['job_techinians'])) {
                foreach ($job_technician['job_techinians'] as $job_techinians) {
                    $technicians[] = $job_techinians['user_id'];
                }
            }
            $start_time = $job_technician['start_time'];
            $end_time = $job_technician['end_time'];
            $technicianTotal = count($job_technician['job_techinians']);
            $techTotalDays += calculateDaysAccTime(($technicianTotal*$no_of_days), $start_time, $end_time);
            $depth++;
        }        
        $totalActualDays = (($assignJobsTotalDays?$assignJobsTotalDays:0) + ($techTotalDays?$techTotalDays:0));
        if ($totalActualDays > $workOrderTotalDays) {
            $leftDays = $workOrderTotalDays - ($totalActualDays - $techTotalDays);
            return ['status' => false, 'message' => 'You should select max '.$leftDays.' day(s) for this task'];
        }
        $assignJobData['total_days'] = $techTotalDays;
        $assignJobData['technicians'] = $technicians;
        return ['status' => true, 'message' => '', 'assignJobData' => $assignJobData];
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $assignjob = AssignJobs::find($id);
        if (empty($assignjob)) {
            return response()->json(['status'=>false, 'message'=>'Job not found in our system.', 'response'=>[]], 200);
        }
        $job_technicians = AssignJobsTech::where('assign_job_id', $id)->get();
        foreach ($job_technicians as &$job_technician) {
            $job_technician->job_techinians = User::whereIn('user_id', explode(',', $job_technician->job_techinians))->select('user_id', 'first_name')->get();
            $start_time = explode(':', $job_technician->start_time);
            $job_technician->start_time = [
                                        'HH' => (isset($start_time[0])?$start_time[0]:'00'), 
                                        'mm'=> (isset($start_time[1])?$start_time[1]:'00')
                                    ];
            $end_time = explode(':', $job_technician->end_time);
            $job_technician->end_time = [
                                        'HH' => (isset($end_time[0])?$end_time[0]:'00'), 
                                        'mm'=> (isset($end_time[1])?$end_time[1]:'00')
                                    ];
        }
        $assignjob->job_technicians = $job_technicians;
        return response()->json(['status'=>true, 'message'=>'Assign job and technician.', 'response'=>compact('assignjob')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $assignjob = AssignJobs::find($id);
        if (empty($assignjob)) {
            return response()->json(['status'=>false, 'message'=>'Job not found in our system.', 'response'=>[]], 200);
        }
        $assignjob->delete();
        $estimateData = SchedulerController::getSingleEstimateScheduler($assignjob->estimate_id);
        return response()->json(['status'=>true, 'message'=>'Job deleted succesfully.', 'response'=>$estimateData], 200);
    }
}
