<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{

    public $timestamps = false;

    protected $table = 'wms_requisitions';

    protected $fillable = ['total_price','client_id','status','type','purchase_kind'];

    public function details(){
        return $this->hasMany('App\Models\RequisitionDetail');
    }

    public function client(){
        return $this->hasOne('App\Models\Client','id','client_id');
    }
}
