<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZoneFeature extends Model
{
  protected $table = 'wms_zone_features';

  public $timestamps = false;

  protected $fillable = ['feature_id', 'zone_id', 'value', 'comparation'];

  public function zone()
  {
    return $this->belongsTo('App\Models\Zone');
  }

  public function feature()
  {
    return $this->belongsTo('App\Models\Feature');
  }
}
