<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EstimateItem extends Model
{
    protected $table = 'estimate_item';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'estimate_item_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['estimate_id','chg_code_id','comp_id','desc','unit_price','unit_of_measurement','days','remarks','uom','our_cost','total_charge_code','total_our_cost','created_by','updated_by','created_at','updated_at'];

    /**
     * Get the Company associated with the Services.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }
    /**
     * Get the Estimate associated with the Services.
     */
    public function estimate()
    {
        return $this->belongsTo('App\Estimate', 'estimate_id');
    }
    /**
     * Get the Estimate associated with the Services.
     */
    public function chargeCode()
    {
        return $this->belongsTo('App\ChargeCode', 'chg_code_id');
    }
}
