<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    protected $table = 'work_orders';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'work_order_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','estimate_id','customer_id','user_id','representative_id','wrk_sno','desc_of_job','preferred_date','completed_date','no_of_technicians','equipment_requested','no_of_days','duration_of_equipment','total_amount','note','wrk_status','pdf_name','schedule_date','notes','schedule_on','total_days','created_by','updated_by'];
}
