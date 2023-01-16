<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserve extends Model
{
  protected $table = 'wms_reserves';

  protected $fillable = ['position_id', 'reserved_id', 'reserve_type'];

  public function client()
  {
    return $this->belongsTo('App\Models\Client', 'id', 'reserved_id');
  }

  public function product_type()
  {
    return $this->belongsTo('App\Models\ProductType', 'id', 'reserved_id');
  }
}
