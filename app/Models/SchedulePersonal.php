<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedulePersonal extends Model
{
  protected $table = 'wms_schedule_personal';

  protected $fillable = ['schedule_id', 'persona_id'];

  public $timestamps = false;

  public function schedule()
  {
    return $this->belongsTo('App\Models\Schedule');
  }

  public function persona()
  {
    return $this->belongsTo('App\Models\Person', 'persona_id');
  }
}
