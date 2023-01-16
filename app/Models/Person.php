<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
  protected $table = 'wms_personal';

  protected $fillable = ['name', 'last_name', 'identification', 'status', 'zone_id', 'group_id', 'charge_id', 'distribution_center_id', 'warehouse_id', 'secondary_group_id', 'vinculation_type_id', 'company_id'];

  public function zone()
  {
    return $this->belongsTo('App\Models\Zone');
  }

  public function warehouse()
  {
    return $this->belongsTo('App\Models\Warehouse');
  }

  public function schedule_receipts()
  {
    return $this->hasMany('App\Models\ScheduleReceipt');
  }

  public function distribution_center()
  {
    return $this->belongsTo('App\Models\DistributionCenter');
  }

  public function charge()
  {
    return $this->belongsTo('App\Models\Charge');
  }

  public function group()
  {
    return $this->belongsTo('App\Models\Group');
  }

  public function secondary_group()
  {
    return $this->belongsTo('App\Models\Group');
  }

  public function vinculation_type()
  {
    return $this->belongsTo('App\Models\vinculation_type');
  }

  public function user()
  {
    return $this->hasOne('App\Models\User', 'personal_id', 'id');
  }
}
