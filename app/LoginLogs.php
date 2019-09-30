<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoginLogs extends Model
{
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'login_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','token','created_at','updated_at'];
}
