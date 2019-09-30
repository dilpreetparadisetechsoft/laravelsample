<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PipeLineStage extends Model
{
	protected $table = 'pipe_line_stages';
	/**
	 * Table primary key
	 *
	 */
	protected $primaryKey = 'pip_id';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['comp_id','pip_sno','pip_name','pip_status','created_by','updated_by'];
}
