<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerDocs extends Model
{
    protected $table= 'customer_docs';
	/**
     * Table primary key
     *
     */
    protected $primaryKey = 'cust_doc_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['customer_id','comp_id','doc_type_id','cust_doc_sno','description','file_name','status','created_by','updated_by'];
}
