<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
  protected $table = 'wms_stock';

  protected $fillable = ['zone_position_id', 'code128_id','code14_id','product_id','quanty','reason_code_id','active','code_ean14','document_detail_id','quanty_14','good','seconds'];

  public function product()
  {
      return $this->belongsTo('App\Models\Product');
  }

  public function zone_position()
  {
      return $this->belongsTo('App\Models\ZonePosition');
  }

  public function ean128()
  {
      return $this->belongsTo('App\Models\EanCode128','code128_id');
  }
  public function ean14()
  {
      return $this->belongsTo('App\Models\EanCode14','code_ean14','id');
  }
  public function document_detail()
  {
    return $this->belongsTo('App\Models\DocumentDetail','document_detail_id','id');
  }
  public function ean13()
  {
      return $this->belongsTo('App\Models\Product','product_id');
  }

  public function stock_count()
  {
    return $this->hasOne('App\Models\StockCount', 'stock_id')->orderBy('id', 'desc');
  }

  public function stock_counts()
  {
    return $this->hasMany('App\Models\StockCount', 'stock_id');
  }
  public function stock_picking_config()
  {
      return $this->hasMany('App\Models\StockPickingConfig','product_id');
  }
  public function detail()
  {
    return $this->hasMany('App\Models\EanCode14Detail','product_id');
  }
  public function stock_transition()
  {
      return $this->hasMany('App\Models\StockTransition','product_id','product_id');
  }
  public function product_ean14s()
  {
    return $this->hasMany('App\Models\ProductEan14','code_ean14','code_ean14');
  }

  public function ean_codes_14_packing()
  {
      return $this->belongsTo('App\Models\Eancodes14Packing','id','stock_id');
  }


  // public function merged()
  // {
  //   $pos = $this->zone_position_id;
  //   return $this->hasOne('App\Models\MergedPosition')->where('from_position_id','>=',$pos)->where('from_position_id','<=',$pos);
  // }
}
