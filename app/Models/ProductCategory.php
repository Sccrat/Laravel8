<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
  protected $table = 'wms_product_categories';

  protected $fillable = ['name', 'code', 'active', 'company_id','zone_id'];

  public $timestamps = false;

  public function product_types()
  {
    return $this->hasMany('App\Models\ProductType');
  }
}
