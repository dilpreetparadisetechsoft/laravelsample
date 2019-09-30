<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Indicator extends Model
{
    protected $table = 'indicator';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'ind_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','ind_sno','ind_name','ind_status','created_by','updated_by','created_at','updated_at'];
}
