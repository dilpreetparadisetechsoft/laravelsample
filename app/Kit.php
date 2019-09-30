<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kit extends Model
{
    protected $table = 'kit';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'kit_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','kit_sno','kit_name','chg_code_id','kit_status','created_by','updated_by','created_at','updated_at'];
    /**
     * Get the Roles Company associated with the user.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }
}
