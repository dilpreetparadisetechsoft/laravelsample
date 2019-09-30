<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'company';
	/**
     * Table primary key
     *
     */
    protected $primaryKey = 'comp_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comp_code','comp_name','comp_logo','tag_line','comp_address','comp_gst_no','comp_pst_no','comp_qst_no','comp_status','finance_mail','created_by','updated_by','created_at','updated_at'];
    /**
     * Get the users for the company.
     */
    protected function users()
    {
        return $this->hasMany('App\User', 'comp_id');
    }
    /**
     * Get the branchs associated with the Company.
     */
    protected function branchs()
    {
        return $this->hasMany('App\Branch', 'comp_id');
    }
    /**
     * Get the kpis associated with the Company.
     */
    protected function kpis()
    {
        return $this->hasMany('App\Kpi', 'comp_id');
    }
    /**
     * Get the jobs associated with the Company.
     */
    protected function jobs()
    {
        return $this->hasMany('App\JobStatus', 'comp_id');
    }
    /**
     * Get the locations associated with the Company.
     */
    protected function locations()
    {
        return $this->hasMany('App\Locations', 'comp_id');
    }
    /**
     * Get the Taxs associated with the Company.
     */
    protected function taxs()
    {
        return $this->hasMany('App\Tax', 'comp_id');
    }
    /**
     * Get the leads associated with the company.
     */
    protected function leads()
    {
        return $this->hasMany('App\LeadSource', 'comp_id');
    }

    /**
     * Get the emailTemplates associated with the company.
     */
    protected function emailTemplates()
    {
        return $this->hasMany('App\EmailTemplate', 'comp_id');
    }
    /**
     * Get the emailAttachment associated with the company.
     */
    protected function emailAttachment()
    {
        return $this->hasMany('App\EmailAttachment', 'comp_id');
    }
    /**
     * Get the template associated with the company.
     */
    protected function template()
    {
        return $this->hasMany('App\Template', 'comp_id');
    }

    /**
     * Get the documentType associated with the company.
     */
    protected function documentType()
    {
        return $this->hasMany('App\DocumentType', 'comp_id');
    }

    /**
     * Get the TaskType associated with the company.
     */
    protected function taskType()
    {
        return $this->hasMany('App\TaskType', 'comp_id');
    }

    /**
     * Get the Services associated with the company.
     */
    protected function services()
    {
        return $this->hasMany('App\Services', 'comp_id');
    }
    /**
     * Get the Equipment associated with the company.
     */
    protected function equipment()
    {
        return $this->hasMany('App\Equipment', 'comp_id');
    }
    /**
     * Get the ChargeCode associated with the company.
     */
    protected function chargeCode()
    {
        return $this->hasMany('App\ChargeCode', 'comp_id');
    }
    /**
     * Get the kit associated with the company.
     */
    protected function kit()
    {
        return $this->hasMany('App\Kit', 'comp_id');
    }

    protected function estimates()
    {
        return $this->hasMany('App\Estimate', 'comp_id');
    }
    protected function invoices()
    {
        return $this->hasMany('App\Invoice', 'comp_id');
    }
    protected function invoicePayments()
    {
        return $this->hasMany('App\InvoicePayment', 'comp_id');
    }
    protected function estimateItems()
    {
        return $this->hasMany('App\EstimateItem', 'comp_id');
    }
}
