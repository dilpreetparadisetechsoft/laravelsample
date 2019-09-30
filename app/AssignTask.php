<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AssignTask extends Model
{
    protected $table = 'assign_task';
	/**
     * Table primary key
     *
     */
    protected $primaryKey = 'task_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['estimate_id','customer_id','comp_id','task_sno','title','representative','start_date','due_date','level','notes','inspection','customer_check','unit','guest_email','status','created_by','updated_by','created_at','updated_at'];
}
