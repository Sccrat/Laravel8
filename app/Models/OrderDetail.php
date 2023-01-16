<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
  protected $table = 'wms_order_details';

  protected $fillable = ['order_number', 'reference', 'size', 'colour', 'plu', 'pvp', 'ean', 'description', 'quanty', 'package', 'value', 'total'];

  public function order()
  {
    return $this->belongsTo('App\Models\Order', 'order_number', 'number');
  }
}
