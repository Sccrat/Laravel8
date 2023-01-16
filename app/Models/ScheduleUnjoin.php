<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleUnjoin extends Model
{
      protected $table = 'wms_schedule_unjoin';

	  protected $fillable = ['schedule_id', 'remove_status','unjoin_status','store_status','status','warehouse_id'];

	  public $timestamps = false;

	  public function schedule()
	  {
	    return $this->belongsTo('App\Models\Schedule');
	  }
	  public function warehouse()
	  {
	    return $this->belongsTo('App\Models\Warehouse');
	  }
	  public function scheduleUnjoinDetail()
	  {
	    return $this->hasMany('App\Models\ScheduleUnjoinDetail');
	  }
}
