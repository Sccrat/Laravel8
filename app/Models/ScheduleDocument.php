<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleDocument extends Model
{
  protected $table = 'wms_schedule_documents';

  protected $fillable = ['schedule_id', 'document_id', 'finished'];

  public $timestamps = false;

  public function schedule()
  {
    return $this->belongsTo('App\Models\Schedule');
  }

  public function document()
  {
    return $this->belongsTo('App\Models\Document');
  }

  public function codes128()
  {
    return $this->hasMany('App\Models\EanCode128', 'document_id', 'document_id');
  }

  public function schedule_dispatch()
  {
    return $this->belongsTo('App\Models\ScheduleDispatch','schedule_id','schedule_id');
  }
}
