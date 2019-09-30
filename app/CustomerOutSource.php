<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerOutSource extends Model
{
    protected $table= 'customer_out_source';
	/**
     * Table primary key
     *
     */
    protected $primaryKey = 'cust_out_src_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['network_id','customer_id','comp_id','cust_out_src_sno','note','send_email','status','created_by','updated_by'];
}
