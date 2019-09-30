<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Modules extends Model
{
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'module_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','type','created_by','created_at','updated_at'];

    /**
     * Get the Privilages for the module.
     */
    public function privilages()
    {
        return $this->belongsTo('App\Privilage', 'module_id');
    }
}
