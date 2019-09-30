<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    protected $table = 'interests';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'int_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','int_sno','int_name','int_status','created_by','updated_by'];
}
