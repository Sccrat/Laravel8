<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleValidateAdjust extends Model
{

      protected $table = 'wms_schedule_validate_adjust';

      public $timestamps = false;


      protected $fillable = ['schedule_id','document_detail_id'];

      public function schedule()
      {
        return $this->belongsTo('App\Models\Schedule');
      }
      public function document_detail()
      {
        return $this->belongsTo('App\Models\DocumentDetail');
      }
      public function detailCount()
      {
        return $this->hasMany('App\Models\DocumentDetailCount','document_detail_id','document_detail_id')->whereRaw('count_parent is null');
      }

}
