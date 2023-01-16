<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleComment extends Model
{
    protected $table = 'wms_schedule_comments';

    protected $fillable = ['schedule_id', 'comment', 'author', 'type'];

    public $timestamps = false;

    public function schedule()
    {
      return $this->belongsTo('App\Models\Schedule');
    }
}
