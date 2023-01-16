<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MergedPosition extends Model
{
  protected $table = 'wms_merged_positions';

  protected $fillable = ['code', 'from_position_id', 'to_position_id','code128'];

  public $timestamps = false;

  public function zone_position_from()
  {
      return $this->belongsTo('App\Models\ZonePosition','from_position_id');
  }

  public function zone_position_to()
  {
      return $this->belongsTo('App\Models\ZonePosition','to_position_id');
  }
}
