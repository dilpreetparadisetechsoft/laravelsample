<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobStatus extends Model
{
    protected $table = 'job_status';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'status_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','name','created_at','updated_at'];

    /**
     * Get the Roles Company associated with the Branch.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }
    /**
     * Get the users for the company.
     */
    public function customers()
    {
        return $this->hasMany('App\Customer', 'status_id', 'job_status_id');
    }
    /**
     * Get the Estimate for the company.
     */
    public function estimates()
    {
        return $this->hasMany('App\Estimate', 'status_id', 'job_status_id');
    }
}
