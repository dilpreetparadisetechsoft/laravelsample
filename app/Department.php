<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
	protected $table = 'department';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'dep_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','created_by','created_at','updated_at'];

    /**
     * Get the users associated with the Department.
     */
    public function users()
    {
        return $this->hasMany('App\User', 'dep_id');
    }
}
