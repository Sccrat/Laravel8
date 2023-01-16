<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleEAN14 extends Model
{
    protected $table = 'wms_schedule_ean14';

    protected $fillable = ['schedule_id', 'ean14_id',];

    public $timestamps = false;

    public function schedule()
    {
      return $this->belongsTo('App\Models\Schedule');
    }
    
    public function ean_code14()
    {
      return $this->belongsTo('App\Models\EanCode14', 'ean14_id');
    }
}
