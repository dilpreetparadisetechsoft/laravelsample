<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkScopeTitles extends Model
{
    protected $table = 'work_scope_titles';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'wrk_scp_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','wrk_scp_sno','wrk_scp_name','wrk_scp_status','created_by','updated_by'];
}
