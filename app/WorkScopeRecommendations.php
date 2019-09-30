<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkScopeRecommendations extends Model
{
    protected $table = 'work_scope_recommendations';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'wrk_scp_rec_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['wrk_scp_id','comp_id','wrk_scp_rec_sno','wrk_scp_rec_name','wrk_scp_rec_status','created_by','updated_by'];
}
