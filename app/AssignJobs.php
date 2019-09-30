<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AssignJobs extends Model
{
    protected $table = 'assign_jobs';
	/**
     * Table primary key
     *
     */
    protected $primaryKey = 'assign_job_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['estimate_id','comp_id','assign_job_sno','title','assigned_user','site_address','importance_level','start_date','start_time','end_time','no_of_days','total_days','pick_up_date','status','job_status','notes','created_by','updated_by','created_at','updated_at'];

    protected function findOrCreate($id)
    {
        $obj = static::find($id);
        return $obj ?: new static;
    }
}
