<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    /**
     * Table primary key
     *
     */
	protected $table= 'email_template';
    protected $primaryKey = 'email_temp_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','email_temp_sno','email_temp_name','email_temp_subject','email_temp_message','email_place_holder','email_temp_status','created_by','updated_by','created_at','updated_at'];
    
    /**
     * Get the Roles Company associated with the EmailTemplate.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }
}
