<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleRestockDetail extends Model
{
  protected $table = 'wms_schedule_restock_detail';

  protected $fillable = ['restock_id','product_id','position_id','code128_id','code14_id','quanty','detail_status'];

  public $timestamps = false;

  public function product()
  {
      return $this->belongsTo('App\Models\Product');
  }

  public function zone_position()
  {
      return $this->belongsTo('App\Models\ZonePosition','position_id');
  }

  public function ean128()
  {
      return $this->belongsTo('App\Models\EanCode128','code128_id');
  }
  public function ean14()
  {
      return $this->belongsTo('App\Models\EanCode14','code14_id');
  }
  public function ean13()
  {
      return $this->belongsTo('App\Models\Product','product_id');
  }
  public function scheduleRestock()
  {
    return $this->belongsTo('App\Models\ScheduleRestock');
  }
}
