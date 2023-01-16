<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductEan14 extends Model
{
  protected $table = 'wms_product_ean14';

  protected $fillable = ['product_id', 'code_ean14', 'quanty', 'container_type_id', 'container_id'];

  public $timestamps = false;

  public function product()
  {
    return $this->belongsTo('App\Models\Product');
  }
  public function stock()
  {
    return $this->hasMany('App\Models\Stock','code_ean14','code_ean14');
  }

  public function transformDetail()
  {
    return $this->hasOne('App\Models\ScheduleTransformDetail','code_ean14');
  }

  public function document_detail()
  {
    return $this->belongsTo('App\Models\DocumentDetail','code_ean14','code_ean14');
  }
  public function pallet()
  {
    return $this->hasOne('App\Models\Pallet','code_ean14','code_ean14');
  }
  public function code14_packing()
  {
    return $this->hasMany('App\Models\Eancodes14Packing','code_ean14','code_ean14');
  }

  public function containers()
  {
    return $this->belongsTo('App\Models\Container','container_id','id');
  }
}
