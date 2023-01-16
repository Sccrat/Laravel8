<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleCountDetail extends Model
{
  protected $table = 'wms_schedule_count_detail';

  protected $fillable = ['schedule_id', 'product_id', 'document_detail_id','count','document'];

  public $timestamps = false;

  public function schedule()
  {
    return $this->belongsTo('App\Models\Schedule');
  }

  public function product()
  {
      return $this->belongsTo('App\Models\Product');
  }
  public function documentDetail()
  {
    return $this->belongsTo('App\Models\DocumentDetail');
  }
}
