<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailAttachment extends Model
{
    protected $table = 'email_attachment';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'email_attach_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','email_attach_sno','email_attach_name','email_attach_type','email_attach_file','email_attach_status','created_by','updated_by','created_at','updated_at'];

    /**
     * Get the Roles Company associated with the EmailAttachment.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }
}
