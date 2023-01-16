<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCountPosition extends Model
{
public $timestamps=false;
  protected $table = 'wms_stock_count_position';

  protected $fillable = ['stock_id', 'product_id','quanty_stock','quanty_real','zone_position_id'];

  public function product()
  {
      return $this->belongsTo('App\Models\Product');
  }

  public function zone_position()
  {
      return $this->belongsTo('App\Models\ZonePosition');
  }
}
