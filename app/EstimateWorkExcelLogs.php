<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EstimateWorkExcelLogs extends Model
{
    protected $table = 'estimate_work_excel_logs';
	/**
     * Table primary key
     *
     */
    protected $primaryKey = 'work_excel_log_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','branch_id','comp_id','start_date','end_date','excel_name','created_at','updated_at'];
}
