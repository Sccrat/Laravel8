<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnlistPackingWaves extends Model
{
  public $timestamps = false;
  protected $table = 'wms_packing_waves';

  protected $fillable = ['wave_id', 'product_id', 'stock_id', 'quanty', 'packaged_quanty', 'relocated'];

  public function wave()
  {
    return $this->belongsTo('App\Models\Waves', 'wave_id');
  }

  public function product()
  {
    return $this->belongsTo('App\Models\Product', 'product_id');
  }

  public function stock()
  {
    return $this->belongsTo('App\Models\Stock', 'stock_id');
  }
}
