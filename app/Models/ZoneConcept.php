<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZoneConcept extends Model
{
  protected $table = 'wms_zone_concepts';

  public $timestamps = false;

  protected $fillable = ['name', 'active','color','is_storage', 'company_id', 'real_name', 'real_is_storage'];

  public function positions()
  {
    return $this->hasMany('App\Models\ZonePosition');
  }
}
