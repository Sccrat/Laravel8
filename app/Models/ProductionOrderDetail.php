<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOrderDetail extends Model
{
    protected $table = 'wms_production_order_detail';

    public $timestamps = false;

    protected $fillable = ['production_order_id', 'product_id', 'quality', 'unit'];

    public function production_order()
    {
      return $this->belongsTo('App\Models\ProductionOrder');
    }
}
