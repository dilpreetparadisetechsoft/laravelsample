<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Locations extends Model
{
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'loc_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','tax_id','city','state','country','created_at','updated_at'];

    /**
     * Get the Company associated with the Locations.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }
    /**
     * Get the Tax associated with the Locations.
     */
    public function tax()
    {
        return $this->belongsTo('App\Tax', 'tax_id');
    }
    /**
     * Get the Customer for the Locations.
     */
    public function customers()
    {
        return $this->hasMany('App\Customer', 'loc_id');
    }
}
