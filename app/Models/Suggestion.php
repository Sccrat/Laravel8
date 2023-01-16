<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suggestion extends Model
{
  protected $table = 'wms_suggestions';

  protected $fillable = ['code', 'zone_position_id', 'stored'];

  public function zone_position()
  {
    return $this->belongsTo('App\Models\ZonePosition');
  }
}
