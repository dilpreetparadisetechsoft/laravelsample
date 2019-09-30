<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskType extends Model
{
    protected $table = 'task_type';
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
    protected $fillable = ['comp_id','task_name','task_status','created_at','updated_at'];

    /**
     * Get the Company associated with the TaskType.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }
}
