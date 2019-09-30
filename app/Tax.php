<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    /**
     * Table primary key
     *
     */
	protected $table = 'tax';
    protected $primaryKey = 'tax_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','name','tax','created_at','updated_at'];

    /**
     * Get the Company associated with the Tax.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }

    /**
     * Get the locations associated with the Tax.
     */
    public function locations()
    {
        return $this->hasMany('App\Locations', 'tax_id');
    }
}
