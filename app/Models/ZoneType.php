<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZoneType extends Model
{
  protected $table = 'wms_zone_types';

  protected $fillable = ['name', 'active', 'is_storage', 'company_id'];

  public function zones()
  {
    return $this->hasMany('App\Models\Zone');
  }
}
