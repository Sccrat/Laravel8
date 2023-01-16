<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleReceiptAdditional extends Model
{
  protected $table = 'wms_schedule_receipts_additional';

  protected $fillable = ['schedule_receipts_id','product_id','document_id','approve_additional','active'];

  public function receipt()
  {
    return $this->belongsTo('App\Models\ScheduleReceipt');
  }

  public function product()
  {
      return $this->belongsTo('App\Models\Product');
  }

  public function document()
  {
      return $this->belongsTo('App\Models\Document');
  }
}
