<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitionDetail extends Model
{
    public $timestamps = false;

    protected $table = 'wms_requisition_details';

    protected $fillable = ['product_id','requisition_id','service_id','quantity','total_price'];
}
