<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    protected $table = 'notifications';
	/**
     * Table primary key
     *
     */
    protected $primaryKey = 'notification_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['task_id','user_ids','activity','type','status','created_by','created_at','updated_at'];
}
