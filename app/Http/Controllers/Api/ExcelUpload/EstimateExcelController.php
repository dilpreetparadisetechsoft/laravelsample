<?php

namespace App\Http\Controllers\Api\ExcelUpload;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Company;
use App\EstimateWorkExcelLogs;
use App\EstimateWorkExcel;
use App\User;
use App\Branch;
use Validator, DateTime, Config, Helpers, Hash, DB, Input, Excel;
use Illuminate\Validation\Rule;

class EstimateExcelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = getApiCurrentUser();   
        $prefix = DB::getTablePrefix();
        $branch = Branch::whereRaw($prefix.'estimate_work_excel_logs.branch_id = '.$prefix.'branch.branch_id')->select('branch_name')->limit(1);
        $user = User::whereRaw($prefix.'estimate_work_excel_logs.user_id = '.$prefix.'users.user_id')->select('first_name')->limit(1);
        $excelLogs = EstimateWorkExcelLogs::where('comp_id', $currentUser->comp_id)
                        ->select(
                            'estimate_work_excel_logs.*',
                            DB::raw("({$branch->toSql()}) as branch_name"),
                            DB::raw("({$user->toSql()}) as user_name")
                        )
                        ->orderBy('estimate_work_excel_logs.work_excel_log_id', 'DESC')
                        ->paginate(dataPerPage());
        $branchs = Branch::where('comp_id', $currentUser->comp_id)->get();
        return response()->json(['status'=>true, 'message'=>'All Estimate Excel Logs', 'response'=>compact('excelLogs','branchs')], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public static function storeRules($request)
    {
        return [
            '' => ''
        ];
    }
    public function store(Request $request)
    {
        if(Input::hasFile('file_name')){
            $path = Input::file('file_name')->getRealPath();
            $name = Input::file('file_name')->getClientOriginalName();            

            $currentUser = getApiCurrentUser();
            $estimateWorkExcelLogs = new EstimateWorkExcelLogs();
            $estimateWorkExcelLogs->user_id = $currentUser->user_id;
            $estimateWorkExcelLogs->branch_id = $request->input('branch_id');
            $estimateWorkExcelLogs->comp_id = $currentUser->comp_id;
            $estimateWorkExcelLogs->start_date = $request->input('start_date');
            $estimateWorkExcelLogs->end_date = $request->input('end_date');
            $estimateWorkExcelLogs->excel_name = $name;
            $estimateWorkExcelLogs->created_at = new DateTime;
            $estimateWorkExcelLogs->updated_at = new DateTime;
            $estimateWorkExcelLogs->save();

            $rows = Excel::load($path, function($reader) {
            })->get();
            
            $estimateWorkExcelItems = [];
            foreach ($rows as $key => $values) {
                foreach ($values as $row) {
                    $estimateWorkExcelItem = [];
                    $estimateWorkExcelItem['work_excel_log_id'] = $estimateWorkExcelLogs->work_excel_log_id;
                    $estimateWorkExcelItem['estimate_id'] = $row->estimate;
                    $estimateWorkExcelItem['comp_id'] = $currentUser->comp_id;
                    $estimateWorkExcelItem['first_name'] = $row->first_name;
                    $estimateWorkExcelItem['last_name'] = $row->last_name;
                    $estimateWorkExcelItem['rate'] = (float)$row->rate;
                    if ($row->start_date) {
                        $estimateWorkExcelItem['start_date'] = date('Y-m-d', strtotime($row->start_date));
                    }
                    if ($row->in) {
                        $estimateWorkExcelItem['start_time'] = date('H:i:s', strtotime($row->in));
                    }
                    if ($row->end_date) {
                        $estimateWorkExcelItem['end_date'] = date('Y-m-d', strtotime($row->end_date));
                    }
                    if ($row->out) {
                        $estimateWorkExcelItem['end_time'] = date('H:i:s', strtotime($row->out));
                    }
                    $estimateWorkExcelItem['total_hours'] = $row->total_hours_per_record;
                    $estimateWorkExcelItem['created_at'] = new DateTime;
                    $estimateWorkExcelItem['updated_at'] = new DateTime;
                    $estimateWorkExcelItems[] = $estimateWorkExcelItem;
                }
            }
            if (!empty($estimateWorkExcelItem) && is_array($estimateWorkExcelItem)) {
                EstimateWorkExcel::insert($estimateWorkExcelItem);
            }
        }
        return self::index();
    }
}
