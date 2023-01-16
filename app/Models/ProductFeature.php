<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductFeature extends Model
{
  protected $table = 'wms_product_features';

  public $timestamps = false;

  protected $fillable = ['feature_id', 'product_id', 'value'];

  public function product()
  {
    return $this->belongsTo('App\Models\Product');
  }

  public function feature()
  {
    return $this->belongsTo('App\Models\Feature');
  }
}
