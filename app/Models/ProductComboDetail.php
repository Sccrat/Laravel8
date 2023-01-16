<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductComboDetail extends Model
{
  protected $table = 'wms_product_combo_details';

  protected $fillable = ['product_combo_id', 'product_id'];

  public $timestamps = false;

  function product_combo()
  {
    return $this->belongsTo('App\Models\ProductCombo');
  }

  function product()
  {
    return $this->belongsTo('App\Models\Product');
  }
}
