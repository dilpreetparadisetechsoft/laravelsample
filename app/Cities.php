<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cities extends Model
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
    protected $fillable = ['name','state_id'];


    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function states()
    {
        return $this->belongsTo(States::class);
    }

}
