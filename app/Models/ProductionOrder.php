<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOrder extends Model
{
    protected $table = 'wms_production_order';

    public $timestamps = false;

    protected $fillable = ['client_id', 'observation'];

    public function detail()
    {
        return $this->hasMany('App\Models\ProductionOrderDetail');
    }
}
