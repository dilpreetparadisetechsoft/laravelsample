<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AssignJobsTech extends Model
{
    protected $table = 'assign_job_tech';
	/**
     * Table primary key
     *
     */
    protected $primaryKey = 'job_tech_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['assign_job_id','job_techinians','job_date','start_time','end_time','created_at','updated_at'];
}
