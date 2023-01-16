<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EanCode128 extends Model
{
  protected $table = 'wms_ean_codes128';

  protected $fillable = ['document_id', 'code128','canceled','container_id','reason_code_id', 'weight', 'height', 'schedule_id','company_id','stored'];

  public function container()
  {
      return $this->belongsTo('App\Models\Container');
  }

  public function pallet()
  {
    return $this->hasMany('App\Models\Pallet','code128_id');
  }

  public function schedule_document()
  {
    return $this->belongsTo('App\Models\ScheduleDocument', 'document_id', 'document_id')->whereHas('schedule', function ($q)
    {
      $q->where('schedule_type', 'receipt');
    });
  }
  public function ean14()
  {
    return $this->belongsToMany('App\Models\EanCode14', 'wms_pallet','code128_id','code14_id');
  }
  public function stock_transition()
  {
    return $this->belongsTo('App\Models\StockTransition', 'code128_id');
  }
  public function merged_position()
  {
    return $this->belongsTo('App\Models\MergedPosition', 'code128');
  }

  public function stock()
  {
    return $this->belongsTo('App\Models\Stock','id','code128_id');
  }

  public function document()
  {
    return $this->belongsTo('App\Models\Document','document_id');
  }
}
