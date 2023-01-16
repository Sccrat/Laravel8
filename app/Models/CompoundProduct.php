<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompoundProduct extends Model
{
  protected $table = 'wms_compound_product';

  protected $fillable = ['ean13','product_id','quanty','parent_product_id','reference','measured_unit'];

  public function product()
  {
      return $this->belongsTo('App\Models\Product');
  }

  

}
