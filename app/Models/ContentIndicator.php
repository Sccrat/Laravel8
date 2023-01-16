<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentIndicator extends Model
{
  protected $table = 'wms_content_indicators';

  protected $fillable = ['content_indicator', 'quanty','product_id','container_id'];

  public function product()
  {
    return $this->belongsTo('App\Models\Product');
  }

  public function container()
  {
    return $this->belongsTo('App\Models\Container');
  }
}
