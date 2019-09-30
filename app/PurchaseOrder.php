<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
	protected $table = 'purchase_order';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'purchase_order_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['estimate_id','customer_id','network_id','comp_id','purchase_order_no','invoice_to','equipment_req','terms_of_pay_insurance_files','desc_of_job','no_of_technicians','no_of_days','terms_of_payment','duration_of_project','date','expected_start_date','expected_end_date','total_amount','time_material','confirmation_of_ack','purchase_order_status','address','pdf_name','po_approved','res_id','created_by','updated_by'];
}
