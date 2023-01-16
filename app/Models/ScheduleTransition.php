<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleTransition extends Model
{
    public $timestamps=false;
    protected $table = 'wms_schedule_transition';
    protected $fillable = ['schedule_id', 'transition_id'];

    public function stock_transition()
    {
      return $this->belongsTo('App\Models\StockTransition','transition_id');
    }
    public function schedule()
    {
      return $this->belongsTo('App\Models\Schedule');
    }

}
