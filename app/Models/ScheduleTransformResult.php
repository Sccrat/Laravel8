<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleTransformResult extends Model
{
  protected $table = 'wms_schedule_transform_result';

  public $timestamps = false;

  protected $fillable = ['schedule_transform_id','transform_task_id','product_id','quanty'];

  public function product()
  {
      return $this->belongsTo('App\Models\Product');
  }

  public function scheduleTransform()
  {
    return $this->belongsTo('App\Models\ScheduleTransform');
  }
}
