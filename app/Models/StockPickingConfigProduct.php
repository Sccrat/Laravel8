<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockPickingConfigProduct extends Model
{
  public $timestamps=false;
  protected $table = 'wms_stock_picking_config_product';
  protected $fillable = ['stock_picking_config_id','product_id'];

  public function product()
  {
      return $this->belongsTo('App\Models\Product','product_id');
  }
  public function stock_picking_config()
  {
      return $this->belongsTo('App\Models\StockPickingConfig');
  }
  public function stock_product()
  {
      return $this->belongsTo('App\Models\Product','product_id');
  }
  public function stock_transition()
  {
      return $this->hasMany('App\Models\StockTransition','product_id','product_id');
  }
}
