<?php

namespace App\Http\Controllers\Api\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Validator, DateTime, Config, Helpers, Hash, DB;
use App\Customer;
use App\Branch;
use App\JobStatus;
use App\Services;
use \App\User;
use \App\Estimate;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = getApiCurrentUser();
        $users = User::where('comp_id', $currentUser->comp_id)->select('first_name','user_id')->get();
        $branchs = Branch::where('comp_id', $currentUser->comp_id)->select('branch_name','branch_id')->get();
        $job_status = JobStatus::where('comp_id', $currentUser->comp_id)->select('name','status_id')->get();       
        $services = Services::where('comp_id', $currentUser->comp_id)->select('serv_name','serv_id')->get();
        
        $reportCategory = [
            'Sales' => [
                'revenue_summary' => 'Revenue Summary',
                'sales_summary' => 'Sales Summary',
                'sales_by_branch' => 'Sales by Branch',
                'sales_by_representative' => 'Sales by Representative',
                'sales_by_service_branch' => 'Sales by Service Branch',
                'sales_by_service_month' => 'Sales By Service Month',
                'sales_by_tier_by_Service' => 'Sales by tier, by Service',
                'sales_by_service_representative' => 'Sales By Service Representative',
                'closing_rate_by_service' => 'Closing rate by service'
            ],
            'Marketing' => [
                'by_source' => 'By Source'
            ],
            'Network' => [
                'by_network' => 'By Network',
                'by_month' => 'By month',
                'by_Status' => 'By Status'
            ],
            'Finance' => [
                'balance_reports' => 'Balance reports',
                'expected_collection' => 'Expected Collection',
                'collection_cycles' => 'Collection Cycles'
            ],
            'Management' => [
                'activity_reports' => 'Activity Reports',
                'customers' => 'Customers',
                'compliance' => 'Compliance'
            ]
        ];
        $years = [];
        for ($y=2018; $y <= date('Y'); $y++) { 
            $years[] = ['year_id' => $y, 'year_name' => $y];
        }
        $months = [
            ['month_id' => 1, 'month_name' => 'Jan'],
            ['month_id' => 2, 'month_name' => 'Feb'],
            ['month_id' => 3, 'month_name' => 'March'],
            ['month_id' => 4, 'month_name' => 'April'],
            ['month_id' => 5, 'month_name' => 'May'],
            ['month_id' => 6, 'month_name' => 'Jun'],
            ['month_id' => 7, 'month_name' => 'July'],
            ['month_id' => 8, 'month_name' => 'Aug'],
            ['month_id' => 9, 'month_name' => 'Sep'],
            ['month_id' => 10, 'month_name' => 'Oct'],
            ['month_id' => 11, 'month_name' => 'Nov'],
            ['month_id' => 12, 'month_name' => 'Dec']
        ];

        return response()->json(['status'=>true, 'message'=>'Report Filters', 'response'=>compact('users','branchs','job_status','services','reportCategory','years','months')], 200);
    }
    public function getReport(Request $request)
    {
        $reports = [];
        $tableHeadingYear = [];
        $report_type = 'all';
        $reportsData = response()->json(['status'=>true, 'message'=>'', 'response'=>compact('reports','tableHeadingYear','report_type')], 200);
        $report_category = $request->input('report_category');
        switch ($report_category) {
            case 'sales_summary':
                $reportsData = self::getSaleSummeryReport($request);
                break;
        }
        return $reportsData;
    }
    public static function getSaleSummeryReport($request)
    {
        $currentUser = getApiCurrentUser();        
        $prefix = DB::getTablePrefix();

        $column = 'IFNULL('.$prefix.'estimate.on_sale_date, '.$prefix.'estimate.updated_at)';

        $groupBy = DB::raw('YEAR('.$column.')');
        $select = DB::raw('(YEAR('.$column.')) as year');
        if ($request->input('report_type') == 'year') {
            $select = DB::raw('YEAR('.$column.') as year, MONTH('.$column.') as month');
            $groupBy = DB::raw('YEAR('.$column.'), MONTH('.$column.') ');
        }elseif ($request->input('report_type') == 'month') {
            $select = DB::raw('('.$prefix.'branch.branch_name) as branch_name, ('.$prefix.'branch.branch_id) as branch_id, (YEAR('.$column.')) as year, (MONTH('.$column.')) as month');
            $groupBy = DB::raw('YEAR('.$column.'), MONTH('.$column.'), ('.$prefix.'branch.branch_id)');
        }
        elseif ($request->input('report_type') == 'branch') {
            $select = DB::raw('('.$prefix.'branch.branch_name) as branch_name, ('.$prefix.'branch.branch_id) as branch_id, (YEAR('.$column.')) as year, (MONTH('.$column.')) as month');
            $groupBy = DB::raw('('.$prefix.'estimate.estimate_id)');
        }

        $reports = Estimate::leftJoin('estimate_invoice', 'estimate_invoice.estimate_id', 'estimate.estimate_id')
                    ->join('customer', 'customer.customer_id', 'estimate.customer_id')
                    ->join('branch', 'branch.branch_id', 'customer.branch_id')
                    ->join('job_status', 'job_status.status_id', 'estimate.status_id')
                    ->where(function ($query) use($request, $currentUser, $prefix, $column){
                        if (empty($request->input('status'))) {
                            $status = getSetting($currentUser->comp_id, 'report_default_status');
                        }else{
                            $status = $request->input('status');
                        }

                        $query->whereIn('estimate.status_id', $status);

                        if (!empty($request->input('branch'))) {
                            $query->whereIn('branch.branch_id', $request->input('branch'));
                        }

                        if (!empty($request->input('service'))) {
                            $query->whereIn('estimate_invoice.serv_id', $request->input('service'));
                        }

                        if (!empty($request->input('representative'))) {
                            $query->whereIn('estimate_invoice.user_id', $request->input('representative'));
                        }

                        if ($request->input('report_type') == 'year') {
                            $selectedValue = $request->input('selectedValue');
                            if (is_array($selectedValue) && !empty($selectedValue)) {
                                if (isset($selectedValue['year'])) {
                                    $query->whereRaw('YEAR('.$column.') = '.$selectedValue['year']);
                                }                                
                            }
                        }
                        if ($request->input('report_type') == 'month') {
                            $selectedValue = $request->input('selectedValue');
                            if (is_array($selectedValue) && !empty($selectedValue)) {
                                if (isset($selectedValue['year'])) {
                                    $query->whereRaw('YEAR('.$column.') = '.$selectedValue['year']);
                                }
                                if (isset($selectedValue['month'])) {
                                    $query->whereRaw('MONTH('.$column.') = '. $selectedValue['month']);
                                }                                
                            }                            
                        }
                        if ($request->input('report_type') == 'branch') {
                            $selectedValue = $request->input('selectedValue');
                            if (is_array($selectedValue) && !empty($selectedValue)) {
                                if (isset($selectedValue['year'])) {
                                    $query->whereRaw('YEAR('.$column.') = '.$selectedValue['year']);
                                }
                                if (isset($selectedValue['month'])) {
                                    $query->whereRaw('MONTH('.$column.') = '. $selectedValue['month']);
                                }
                                if (isset($selectedValue['branch'])) {
                                    $query->where('branch.branch_id', $selectedValue['branch']);
                                }                                
                            }                            
                        }

                        if (!empty($request->input('year'))) {
                            $year = $request->input('year');
                            $query->whereRaw('YEAR('.$column.') IN ('.implode('.', $year).')');
                        }

                        if (!empty($request->input('month'))) {
                            $month = $request->input('month');
                            $query->whereRaw('MONTH('.$column.') IN ('.implode('.', $month).')');
                        }

                        $query->where('estimate.comp_id', $currentUser->comp_id);
                        $query->where('estimate.estimate_status', 1);                        
                    })
                    ->select(
                        DB::raw('SUM('.$prefix.'estimate_invoice.invoice_amount) AS invoice_amount'),
                        DB::raw('SUM(grand_total) AS gross'),
                        DB::raw('SUM(sub_total) AS net_sale'),
                        DB::raw('SUM(total_collection) AS paid'),
                        DB::raw('SUM(collection) AS net_paid'),
                        DB::raw('SUM('.$prefix.'estimate_invoice.invoice_amount) - SUM(collection) AS balance'),
                        DB::raw('COUNT('.$prefix.'estimate.estimate_id) AS jobs'),
                        $select
                    )
                    ->groupBy($groupBy)
                    ->get();
        $report_type = $request->input('report_type');
        if ($report_type == 'all') {
            $tableHeadingYear = array('Year','Gross','Net Sale','Invoice Amount','Paid','Net Paid','AR','Jobs','Avg job'); 
            return response()->json(['status'=>true, 'message'=>'Year Reports.', 'response'=>compact('reports','tableHeadingYear','report_type')], 200);
        }elseif ($report_type == 'year') {
            $tableHeadingMonth = array('Year','Month','Gross','Net Sale','Invoice Amount','Paid','Net Paid','AR','Jobs','Avg job'); 
            $monthReports = $reports;
            return response()->json(['status'=>true, 'message'=>'Year Reports.', 'response'=>compact('monthReports','tableHeadingMonth','report_type')], 200);
        }elseif ($report_type == 'month') {
            $branchReports = $reports;
            $tableHeadingBranch = array('Year','Month','Branch','Gross','Net Sale','Invoice Amount','Paid','Net Paid','AR','Jobs','Avg job'); 
            return response()->json(['status'=>true, 'message'=>'Month Reports.', 'response'=>compact('branchReports','tableHeadingBranch','report_type')], 200);
        }elseif ($report_type == 'branch') {
            $branchAllReports = $reports;
            $tableHeadingBranchAll = array('Branch','Year','Month','Gross','Net Sale','Invoice Amount','Paid','Net Paid','AR','Jobs','Avg job'); 
            return response()->json(['status'=>true, 'message'=>'Branch Reports.', 'response'=>compact('branchAllReports','tableHeadingBranchAll','report_type')], 200);
        }        
    }
}
