<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Occupation extends Model
{
	protected $table = 'occupations';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'occ_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','occ_sno','occ_name','occ_status','created_by','updated_by'];
}
