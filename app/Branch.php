<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branch';
	/**
     * Table primary key
     *
     */
    protected $primaryKey = 'branch_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['branch_sno','comp_id','branch_name','reg_id','branch_status','loc_id','created_by','updated_by','created_at','updated_at'];

}
