<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnlistProductsWaves extends Model
{
  public $timestamps = false;
  protected $table = 'wms_enlist_products_waves';

  protected $fillable = ['wave_id', 'product_id', 'order_quanty', 'quanty', 'picked_quanty'];

  public function wave()
  {
    return $this->belongsTo('App\Models\Waves', 'wave_id');
  }

  public function product()
  {
    return $this->belongsTo('App\Models\Product', 'product_id');
  }
}
