<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table= 'customer';
	/**
     * Table primary key
     *
     */
    protected $primaryKey = 'customer_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','branch_id','customer_sno','customer_name','customer_address','customer_postal_code','email','phone','mobile','loc_id','build_id','job_status_id','language','profile','serv_id','lead_id','cust_status','created_by','updated_by','created_at','updated_at'];

    /**
     * Get the Company associated with the Customer.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }
    /**
     * Get the Branch record associated with the Customer.
     */
    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
    }
    /**
     * Get the Locations record associated with the Customer.
     */
    public function location()
    {
        return $this->belongsTo('App\Locations', 'loc_id');
    }

    /**
     * Get the JobStatus associated with the Customer.
     */
    public function jobs()
    {
        return $this->belongsTo('App\JobStatus', 'job_status_id', 'status_id');
    }
    /**
     * Get the Services associated with the Customer.
     */
    public function services()
    {
        return $this->belongsTo('App\Services', 'serv_id');
    }
    /**
     * Get the LeadSource associated with the Customer.
     */
    public function leadSource()
    {
        return $this->belongsTo('App\LeadSource', 'lead_id');
    }

    public function estimates()
    {
        return $this->hasMany('App\Estimate', 'estimate_id');
    }

}
