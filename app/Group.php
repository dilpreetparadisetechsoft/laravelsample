<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'group_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','group_sno','group_name','group_status','created_by','updated_by'];
}
