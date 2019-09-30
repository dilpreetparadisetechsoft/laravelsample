<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    protected $table = 'events';
	/**
     * Table primary key
     *
     */
    protected $primaryKey = 'event_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','task_id','event_name','event_date','event_start_time','event_end_time','event_type','meeting_with','event_loc','guests','note','status','created_by','updated_by','created_at','updated_at'];
}
