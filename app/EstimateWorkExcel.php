<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EstimateWorkExcel extends Model
{
    protected $table = 'estimate_work_excel';
	/**
     * Table primary key
     *
     */
    protected $primaryKey = 'work_excel_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['work_excel_log_id','estimate_id','comp_id','first_name','last_name','rate','start_date','start_time','end_date','end_time','created_at','updated_at'];
}
