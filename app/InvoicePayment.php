<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'payment_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['invoice_id','comp_id','estimate_id','invoice_amount','pay_type','amount','total_amount','tax','pay_status','pay_date','created_by','updated_by','created_at','updated_at'];

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
     * Get the Invoice associated with the Services.
     */
    public function invoice()
    {
        return $this->belongsTo('App\Invoice', 'invoice_id');
    }
}
