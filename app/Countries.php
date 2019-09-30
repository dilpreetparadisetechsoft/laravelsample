<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Countries extends Model
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
    protected $fillable = ['name','sortname','phonecode'];
}
