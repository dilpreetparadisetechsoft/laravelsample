<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class States extends Model
{
	/**
     * Table primary key
     *
     */
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','country_id'];


    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function counteries()
    {
        return $this->belongsTo(Counteries::class);
    }

}

