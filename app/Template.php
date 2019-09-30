<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'template';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'temp_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','temp_sno','temp_name','temp_subject','temp_content','temp_type','temp_status','created_by','updated_by','created_at','updated_at'];

    /**
     * Get the Roles Company associated with the Template.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }
}
