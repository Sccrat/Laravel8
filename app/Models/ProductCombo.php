<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCombo extends Model
{
  protected $table = 'wms_product_combos';

  protected $fillable = ['name', 'reference', 'ean', 'company_id'];

  public $timestamps = false;

  function product_combo_detail()
  {
    return $this->hasMany('App\Models\ProductComboDetail');
  }
}
