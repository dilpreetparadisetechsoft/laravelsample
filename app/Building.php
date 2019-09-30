<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    protected $table = 'building';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'build_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','build_sno','build_name','build_status','created_by','updated_by','created_at','updated_at'];
    /**
     * Get the Roles Company associated with the user.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }
}
