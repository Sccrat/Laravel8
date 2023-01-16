<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositionFeature extends Model
{
  protected $table = 'wms_position_features';

  public $timestamps = false;

  protected $fillable = ['feature_id', 'zone_position_id', 'value', 'comparation', 'free_value'];

  public function zone_position()
  {
    return $this->belongsTo('App\Models\ZonePosition');
  }

  public function feature()
  {
    return $this->belongsTo('App\Models\Feature');
  }
}
