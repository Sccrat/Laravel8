<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleTransformResultPackaging extends Model
{
  protected $table = 'wms_schedule_transform_result_packaging';

  // public $timestamps = false;

  protected $fillable = ['schedule_id','schedule_transform_id','product_id','quanty','container_id','have_code','ean14_id','status'];

  public function product()
  {
      return $this->belongsTo('App\Models\Product');
  }

  public function scheduleTransform()
  {
    return $this->belongsTo('App\Models\ScheduleTransform');
  }

  public function container()
  {
    return $this->belongsTo('App\Models\Container');
  }
  public function ean14()
  {
    return $this->belongsTo('App\Models\EanCode14','ean14_id');
  }

  public function scheduleTransformPackagingCount()
  {
	return $this->hasMany('App\Models\ScheduleTransformPackagingCount','schedule_transform_id');
  }

  public function schedule_transform_validate_adjust()
  {
	  return $this->hasMany('App\Models\ScheduleTransformValidateAdjust');
  }
}
