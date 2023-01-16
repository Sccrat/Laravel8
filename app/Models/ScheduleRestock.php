<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleRestock extends Model
{
      protected $table = 'wms_schedule_restock';

	  protected $fillable = ['schedule_id', 'warehouse_id','relocate_status','status'];

	  public $timestamps = false;

	  public function schedule()
	  {
	    return $this->belongsTo('App\Models\Schedule');
	  }
	  public function warehouse()
	  {
	    return $this->belongsTo('App\Models\Warehouse');
	  }
	  public function scheduleRestockDetail()
	  {
	    return $this->hasMany('App\Models\ScheduleRestockDetail');
	  }
}
