<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AssignTaskComments extends Model
{
    protected $table = 'assign_task_comments';
	/**
     * Table primary key
     *
     */
    protected $primaryKey = 'task_comment_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['task_id','user_id','tags','comments','file','created_by','created_at','updated_at'];
}
