<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChargeCode extends Model
{
    protected $table = 'charge_code';
	/**
     * Table primary key
     *
     */
    protected $primaryKey = 'chg_code_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','chg_code_sno','chg_code_name','chg_code','description','unit_price','unit_of_measurement','our_cost','gl_code','count_in_wo','chg_code_status','created_by','updated_by','created_at','updated_at'];
    /**
     * Get the Roles Company associated with the user.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }
    /**
     * Get the Roles Company associated with the user.
     */
    public function estimateItem()
    {
        return $this->belongsTo('App\EstimateItem', 'chg_code_id');
    }
}
