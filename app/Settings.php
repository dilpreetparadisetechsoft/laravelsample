<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $table = 'settings';
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
    protected $fillable = ['comp_id','key','value'];
}
