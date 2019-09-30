<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Networks extends Model
{
	protected $table = 'networks';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'network_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['occ_id','branch_id','pip_id','comp_id','user_id','group_id','int_id','network_sno','comp_name','address','postal_code','language','website','email','phone','contact_persons','network_status','created_by','updated_by'];
}
