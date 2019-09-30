<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeadSource extends Model
{
    protected $table = 'lead_source';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'lead_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','name','status','created_by','created_at','updated_at'];
    
    /**
     * Get the Company associated with the Lead source.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }

    /**
     * Get the customers for the company.
     */
    public function customers()
    {
        return $this->hasMany('App\Customer', 'lead_id');
    }
}
