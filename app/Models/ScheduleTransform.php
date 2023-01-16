<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleTransform extends Model
{
      protected $table = 'wms_schedule_transform';

	  protected $fillable = ['schedule_id', 'remove_status','transform_status','store_status','status','warehouse_id','type_transform'];

	  public $timestamps = false;

	  public function schedule()
	  {
	    return $this->belongsTo('App\Models\Schedule');
	  }
	  public function warehouse()
	  {
	    return $this->belongsTo('App\Models\Warehouse');
	  }
	  public function scheduleTransformDetail()
	  {
	    return $this->hasMany('App\Models\ScheduleTransformDetail');
	  }	  
	  public function scheduleTransformResult()
	  {
	    return $this->hasMany('App\Models\ScheduleTransformResult');
	  }
	  public function scheduleTransformResultPackaging()
	  {
	    return $this->hasMany('App\Models\ScheduleTransformResultPackaging');
	  }
}
