<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
  protected $table = 'wms_product_types';

  protected $fillable = ['name', 'code', 'product_category_id'];

  public $timestamps = false;

  public function product_sub_types()
  {
   return $this->hasMany('App\Models\ProductSubType');
  }

  public function product_category()
  {
    return $this->belongsTo('App\Models\ProductCategory');
  }
}
