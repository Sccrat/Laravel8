<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSubType extends Model
{
  protected $table = 'wms_product_sub_types';

  protected $fillable = ['name', 'product_type_id', 'code'];

  public $timestamps = false;

  public function product_type()
  {
    return $this->belongsTo('App\Models\ProductType');
  }

  public function products()
  {
   return $this->hasMany('App\Models\Product');
  }
}
