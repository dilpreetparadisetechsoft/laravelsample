<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    protected $table = 'document_type';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'doc_type_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','doc_type_sno','doc_type_name','doc_type_status','created_by','updated_by','created_at','updated_at'];

    /**
     * Get the Roles Company associated with the DocumentType.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }
}
