<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnlistProducts extends Model
{
  public $timestamps=false;
  protected $table = 'wms_enlist_products';

  protected $fillable = ['product_id', 'city_id','quanty','status','destiny_ware','document_id','parent_product_id','condition','semi_condition','without_conditioning','condition_warehouse','semi_condition_warehouse','without_conditioning_warehouse','unit','code_ean14','picked_quanty','good','seconds','schedule_id', 'is_material','returned'];

  public function city()
  {
      return $this->belongsTo('App\Models\City','city_id');
  }
  public function product_ean14()
  {
    return $this->belongsTo('App\Models\ProductEan14','code_ean14','code_ean14');
  }

  public function product()
  {
      return $this->belongsTo('App\Models\Product','product_id');
  }

  public function document_detail()
  {
      return $this->belongsTo('App\Models\DocumentDetail','product_id','product_id');
  }

  public function document()
  {
      return $this->belongsTo('App\Models\Document','document_id');
  }

  public function warehouse()
  {
    return $this->belongsTo('App\Models\Warehouse','destiny_ware','id');
  }

  public function condition_warehouse()
  {
    return $this->belongsTo('App\Models\Warehouse','condition_warehouse','id');
  }
  public function semi_condition_warehouse()
  {
    return $this->belongsTo('App\Models\Warehouse','semi_condition_warehouse','id');
  }
  public function without_conditioning_warehouse()
  {
    return $this->belongsTo('App\Models\Warehouse','without_conditioning_warehouse','id');
  }

  public function ean_codes_14()
  {
    return $this->hasMany('App\Models\Eancodes14Packing','document_id','document_id');
  }

  public function stock()
  {
    return $this->hasMany('App\Models\Stock','product_id','product_id');
  }

}
