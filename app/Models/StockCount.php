<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCount extends Model
{
  protected $table = 'wms_stock_counts';

  public $timestamps = false;

  protected $fillable = ['product_id', 'zone_position_id', 'code_128_id', 'quanty', 'schedule_id','code_ean14','stock_id'];

  public function schedule()
  {
    return $this->belongsTo('App\Models\Schedule');
  }

  public function ean14()
  {
    return $this->belongsTo('App\Models\ProductEan14','code_ean14','code_ean14');
  }

   public function product()
  {
    return $this->belongsTo('App\Models\Product','product_id','id');
  }

   public function position()
  {
    return $this->belongsTo('App\Models\ZonePosition','zone_position_id','id');
  }
  public function stock()
  {
    return $this->belongsTo('App\Models\Stock','stock_id','id');
  }
}
