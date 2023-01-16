<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ZonePosition extends Model
{
  protected $table = 'wms_zone_positions';

  protected $fillable = ['level', 'module', 'row', 'position', 'description', 'code', 'active', 'zone_id', 'concept_id'];

  public function zone()
  {
    return $this->belongsTo('App\Models\Zone');
  }

  public function concept()
  {
    return $this->belongsTo('App\Models\ZoneConcept');
  }

  public function zone_position_features()
  {
    return $this->hasMany('App\Models\PositionFeature');
  }

  public function stocks()
  {
    return $this->hasMany('App\Models\Stock');
  }

}
