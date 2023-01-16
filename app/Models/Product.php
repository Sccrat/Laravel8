<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
  protected $table = 'wms_products';

  protected $fillable = ['remark','category_id','vendor_id','description','retail','reference','size','colour', 'product_sub_type_id','code', 'client_id', 'schema_id', 'brand_id', 'complement_size', 'ean', 'serial', 'unit', 'company_id', 'short_description', 'origin', 'presentation_id', 'lot', 'due_date', 'distribution_type', 'purchase', 'manufacture', 'sale','product_type_id','usercreated_id','userupdated_id'];

  public function product_sub_type()
  {
      return $this->belongsTo('App\Models\ProductSubType');
  }

  public function product_type()
  {
      return $this->belongsTo('App\Models\ProductType');
  }

  public function stock_count()
  {
    return $this->hasMany('App\Models\StockCount');
  }

  public function product_features()
  {
    return $this->hasMany('App\Models\ProductFeature');
  }

  public function brand()
  {
    return $this->hasOne('App\Models\Brand','id','brand_id');
  }

  public function client()
  {
    return $this->belongsTo('App\Models\Client');
  }

  public function schema()
  {
    return $this->belongsTo('App\Models\Schema');
  }

  public function category(){
      return $this->hasOne('App\Models\ProductCategory','id','category_id');
  }

  public function stock()
  {
    return $this->hasMany('App\Models\Stock');
  }

  public function vendor(){
      return $this->hasOne('App\Models\Client','id','vendor_id');
  }

  public function joinReferences()
  {
    return $this->hasMany('App\Models\JoinReferences','product_id_target')->where('active', 1);
  }

  public function detail()
  {
    return $this->hasMany('App\Models\EanCode14Detail');
  }

  public function presentation()
  {
    return $this->belongsTo('App\Models\Presentation');
  }

  public function product_ean14s()
  {
    return $this->hasMany('App\Models\ProductEan14');
  }
}
