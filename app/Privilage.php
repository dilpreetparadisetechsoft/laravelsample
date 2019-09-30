<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Privilage extends Model
{
    protected $table = 'privilage';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'privilage_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['module_id','action','add','edit','view','delete','type','user_id','created_by','created_at','updated_at'];
    /**
     * Get the users associated with the privilags.
     */
    public function users()
    {
        return $this->belongsToMany('App\User', 'user_id');
    }
    /**
     * Get the module associated with the privilags.
     */
    public function module()
    {
        return $this->hasOne('App\Modules', 'module_id');
    }
}
