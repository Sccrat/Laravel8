<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleDispatch extends Model
{

  protected $table = 'wms_schedule_dispatch';

  public $timestamps = false;

  protected $fillable = ['schedule_id','city', 'driver_name', 'driver_identification', 'driver_phone', 'vehicle_plate', 'company', 'company_phone', 'responsible_id','warehouse_id','seal'];

  public function schedule()
  {
    return $this->belongsTo('App\Models\Schedule');
  }

  public function responsible()
  {
    return $this->belongsTo('App\Models\Person');
  }
   public function warehouse()
  {
    return $this->belongsTo('App\Models\Warehouse');
  }
}
