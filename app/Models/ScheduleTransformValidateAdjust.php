<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleTransformValidateAdjust extends Model
{
  protected $table = 'wms_schedule_transform_validate_adjust';
  
  protected $fillable = ['schedule_id','schedule_transform_result_packaging_id'];

  public $timestamps = false;

  public function schedule()
  {
    return $this->belongsTo('App\Models\Schedule');
  }

  public function schedule_transform_result_packaging()
  {
    return $this->belongsTo('App\Models\ScheduleTransformResultPackaging');
  }
}
