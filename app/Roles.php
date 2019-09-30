<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'role_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['role','created_by','created_at','updated_at'];
}
