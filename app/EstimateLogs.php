<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EstimateLogs extends Model
{
    protected $table = 'estimate_log';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'estimate_log_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['estimate_id','customer_id','comp_id','serv_id','user_id','tax_id','area','address','status_id','amount','discount','discount_amount','our_cost_value','profit','profit_val','overhead','overhead_val','profit_overhead','cog_val','tax','tax_amount','grand_total','sub_total','note','line_item','expiry_date','success','expected_collection','insurance','ins_comp_id','is_assigned','estimate_status','po_claim','building_type','estimate_items_log','complete_val','created_by','updated_by','created_at','updated_at'];
}
