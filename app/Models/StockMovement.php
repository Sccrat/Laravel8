<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $table = 'wms_stock_movements';
  	protected $fillable = [
		'id',
		'product_id',
		'product_reference',
		'product_ean',
		'product_quanty',
		'zone_position_code',
		'code128',
		'code14',
		'username',
		'warehouse_id',
		'action',
		'concept'
  	];
}
