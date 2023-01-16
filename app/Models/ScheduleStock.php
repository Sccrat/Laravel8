<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleStock extends Model
{
  protected $table = 'wms_schedule_stocks';

  protected $fillable = ['schedule_id', 'client_id', 'product_type_id', 'warehouse_id', 'reference', 'inventory_type', 'persona_id', 'helper_id'];

  public $timestamps = false;

  public function schedule()
  {
    return $this->belongsTo('App\Models\Schedule');
  }
}
