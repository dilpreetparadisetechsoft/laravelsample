<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Estimate extends Model
{
    protected $table = 'estimate';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'estimate_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['customer_id','comp_id','serv_id','user_id','tax_id','area','address','status_id','amount','discount','discount_amount','our_cost_value','profit','profit_val','overhead','overhead_val','profit_overhead','cog_val','tax','tax_amount','grand_total','sub_total','note','line_item','expiry_date','success','expected_collection','insurance','ins_comp_id','is_assigned','estimate_status','po_claim','building_type','complete_val','created_by','updated_by','created_at','updated_at'];


    /**
     * Get the Company associated with the Lead source.
     */
    public function customers()
    {
        return $this->belongsTo('App\Customer', 'customer_id');
    }
    /**
     * Get the Company associated with the Lead source.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }
    /**
     * Get the Company associated with the Lead source.
     */
    public function services()
    {
        return $this->belongsTo('App\Services', 'serv_id');
    }
    /**
     * Get the Company associated with the Lead source.
     */
    public function jobs()
    {
        return $this->belongsTo('App\JobStatus', 'status_id', 'status_id');
    }

    public function invoices()
    {
        return $this->hasMany('App\Invoice', 'estimate_id');
    }

    public function invoicePayments()
    {
        return $this->hasMany('App\InvoicePayment', 'estimate_id');
    }
    /**
     * Get the Roles Company associated with the user.
     */
    public function estimateItem()
    {
        return $this->belongsTo('App\EstimateItem', 'estimate_id');
    }
}
