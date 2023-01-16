<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
  protected $table = 'wms_warehouses';

  protected $fillable = ['code', 'name', 'real_code', 'address', 'distribution_center_id'];

  public function zones()
  {
    return $this->hasMany('App\Models\Zone');
  }

  public function distribution_center()
  {
    return $this->belongsTo('App\Models\DistributionCenter');
  }

  public function schedule_receipts()
  {
    return $this->hasMany('App\Models\ScheduleReceipt');
  }

  public function warehouse_features()
  {
    return $this->hasMany('App\Models\WarehouseFeature');
  }
}
