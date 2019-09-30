<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InsuranceCompany extends Model
{
    protected $table = 'insurance_company';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'ins_comp_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_id','ins_comp_sno','ins_comp_name','ins_comp_status','created_by','updated_by','created_at','updated_at'];

    /**
     * Get the Company associated with the InsuranceCompany.
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'comp_id');
    }
}
