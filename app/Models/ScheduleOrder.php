<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleOrder extends Model
{
  protected $table = 'wms_schedule_orders';

  protected $fillable = ['schedule_id', 'order_id'];

  public $timestamps = false;

  public function schedule()
  {
    return $this->belongsTo('App\Models\Schedule');
  }

  public function order()
  {
    return $this->belongsTo('App\Models\Order');
  }
}
