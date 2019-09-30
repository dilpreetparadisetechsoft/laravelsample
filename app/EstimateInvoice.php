<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EstimateInvoice extends Model
{
    protected $table = 'estimate_invoice';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'estimate_invoice_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','estimate_id','customer_id','invoice_no','invoice_file_name','invoice_amount','invoice_date','invoice_status','paid_on','invoice_note','payment_note','payment_method','payment_status','collection','discount','tax','total_collection','pdf_name','due_date','created_by','updated_by','created_at','updated_at'];

}
