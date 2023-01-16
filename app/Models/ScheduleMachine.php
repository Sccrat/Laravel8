<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleMachine extends Model
{
    protected $table = 'wms_schedule_machines';

    protected $fillable = ['schedule_id', 'machine_id'];

    public $timestamps = false;

    public function schedule()
    {
      return $this->belongsTo('App\Models\Schedule');
    }

    public function machine()
    {
      return $this->belongsTo('App\Models\Machine');
    }
}
