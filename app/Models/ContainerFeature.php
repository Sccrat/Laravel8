<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContainerFeature extends Model
{
  protected $table = 'wms_container_features';

  public $timestamps = false;

  protected $fillable = ['feature_id', 'container_id', 'value'];

  public function container()
  {
    return $this->belongsTo('App\Models\Container');
  }

  public function feature()
  {
    return $this->belongsTo('App\Models\Feature');
  }
}
