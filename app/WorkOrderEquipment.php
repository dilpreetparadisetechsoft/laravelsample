<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkOrderEquipment extends Model
{
    protected $table = 'work_order_equipments';
    /**
     * Table primary key
     *
     */
    protected $primaryKey = 'work_order_eq_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['work_order_id','eq_id','no_of_days','no_of_quantities','created_by','updated_by'];
}
