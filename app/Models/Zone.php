<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
  protected $table = 'wms_zones';

  protected $fillable = ['code', 'name', 'real_code','weight', 'height', 'depth', 'warehouse_id', 'rows', 'levels', 'modules', 'positions', 'width', 'zone_type_id', 'is_secondary', 'is_damaged'];

  public function warehouse()
  {
    return $this->belongsTo('App\Models\Warehouse');
  }

  public function zone_type()
  {
    return $this->belongsTo('App\Models\ZoneType');
  }

  public function zone_positions()
  {
    return $this->hasMany('App\Models\ZonePosition')->orderBy('row', 'asc')->orderBy('level', 'asc')->orderBy('module', 'asc')->orderBy('position', 'asc');
  }

  public function zone_features()
  {
    return $this->hasMany('App\Models\ZoneFeature');
  }
}
