<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleCountPosition extends Model
{
  public $timestamps=false;
  protected $table = 'wms_schedule_count_position';

  protected $fillable = ['schedule_id', 'zone_position_id'];

  public function stock_picking_config()
  {
      return $this->belongsTo('App\Models\StockPickingConfig','zone_position_id');
  }

  public function zone_position()
  {
    return $this->belongsTo('App\Models\ZonePosition');
  }

}
