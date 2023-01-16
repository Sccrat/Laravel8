<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleImage extends Model
{
  protected $table = 'wms_schedule_images';

  protected $fillable = ['schedule_id', 'url','document_id','name_file'];

  public $timestamps = false;

  public function schedule()
  {
    return $this->belongsTo('App\Models\Schedule');
  }
}
