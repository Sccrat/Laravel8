<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleReceipt extends Model
{

  protected $table = 'wms_schedule_receipts';

  public $timestamps = false;

  protected $fillable = ['schedule_id', 'officer_name', 'city', 'officer_phone', 'driver_name', 'driver_identification', 'driver_phone', 'vehicle_plate', 'company', 'company_phone', 'receipt_type_id', 'warehouse_id', 'zone_id', 'seal', 'officer', 'responsible_id' ,'client', 'bl', 'container_number', 'container_weight', 'provider', 'validation_status'];

  public function schedule()
  {
    return $this->belongsTo('App\Models\Schedule');
  }

  public function warehouse()
  {
    return $this->belongsTo('App\Models\Warehouse');
  }

  public function responsible()
  {
    return $this->belongsTo('App\Models\Person');
  }

  public function receipt_type()
  {
    return $this->belongsTo('App\Models\ReceiptType');
  }

  public function receiptsAdditional()
  {
    return $this->hasMany('App\Models\ScheduleReceiptAdditional');
  }
}
