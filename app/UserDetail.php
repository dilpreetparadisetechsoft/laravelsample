<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $table = 'user_detail';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'ud_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','image','address','region','dob','doj','created_at','updated_at'];

    /**
     * Get the user associated with the user Detail.
     */
    protected function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
