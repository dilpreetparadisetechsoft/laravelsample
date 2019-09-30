<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EstimateInvoiceLogs extends Model
{
    protected $table = 'estimate_invoice_logs';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'invoice_log_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['estimate_invoice_id','action','amount','discount','tax','total_collection','created_by','updated_by','created_at','updated_at'];
}
