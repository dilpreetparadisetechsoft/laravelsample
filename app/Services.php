<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'serv_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','serv_sno','serv_name','serv_status','created_by','updated_by','created_at','updated_at'];

    /**
     * Get the Company associated with the Services.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }
    /**
     * Get the Customer for the Services.
     */
    public function customers()
    {
        return $this->hasMany('App\Customer', 'serv_id');
    }
    /**
     * Get the estimates for the Services.
     */
    public function estimates()
    {
        return $this->hasMany('App\Estimate', 'serv_id');
    }
}
