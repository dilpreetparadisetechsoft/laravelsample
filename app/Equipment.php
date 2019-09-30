<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $table = 'equipment';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'eq_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','eq_sno','name','eq_status','created_by','updated_by','created_at','updated_at'];
    /**
     * Get the Roles Company associated with the user.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }
}
