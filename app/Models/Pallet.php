<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pallet extends Model
{
  protected $table = 'wms_pallet';

  protected $fillable = ['code14_id', 'code128_id','code_ean14','document_detail_id','quanty', 'good', 'seconds', 'sin_conf'];

  public function code14()
  {
    return $this->belongsTo('App\Models\EanCode14');
  }

  public function product_ean14()
  {
    return $this->hasMany('App\Models\ProductEan14','code_ean14','code_ean14');
  }

  public function document_detail()
  {
    return $this->belongsTo('App\Models\DocumentDetail');
  }

  public function ean14_detail()
  {
    return $this->belongsTo('App\Models\EanCode14Detail','document_detail_id','document_detail_id');
  }

  public function pruebini()
  {
    return $this->hasMany('App\Models\EanCode14', 'id', 'code14_id');
  }

  public function ean128()
  {
    return $this->belongsTo('App\Models\EanCode128','code128_id');
  }

}
