<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressTask extends Model
{
  protected $table = 'wms_progress_task_enlist';

  protected $fillable = ['schedule_id', 'quanty','real_quanty'];

  public $timestamps = false;

  public function schedule()
  {
      return $this->belongsTo('App\Models\Schedule');
  }

}
