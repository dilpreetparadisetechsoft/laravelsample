<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerContactHistory extends Model
{
    protected $table= 'customer_contact_history';
	/**
     * Table primary key
     *
     */
    protected $primaryKey = 'cust_cont_history_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['customer_id','comp_id','cust_cont_history_sno','communication_mode','note','contact_date','contact_time','status','created_by','updated_by'];
}
