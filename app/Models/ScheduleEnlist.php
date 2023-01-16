<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleEnlist extends Model
{
  public $timestamps=false;
  protected $table = 'wms_schedule_enlist';
  protected $fillable = ['name','schedule_id','start_date','end_date','status','user_id','parent_schedule_id'];

  public function schedule()
  {
      return $this->belongsTo('App\Models\Schedule','schedule_id');
  }

  public function user()
  {
      return $this->belongsTo('App\Models\User');
  }

  public function progress()
  {
      return $this->belongsTo('App\Models\ProgressTask','schedule_id','schedule_id');
  }
}
