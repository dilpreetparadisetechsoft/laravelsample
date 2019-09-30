<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kpi extends Model
{
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'kpi_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','comp_id','kpi_sno','kpi_target','kpi_indicator','kpi_month','kpi_annual','start_date','end_date','kpi_desc','kpi_quarterly','status','created_by','updated_by','created_at','updated_at'];

    /**
     * Get the Roles Company associated with the Kpi.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }

    /**
     * Get the users associated with the Kpi.
     */
    public function users()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
