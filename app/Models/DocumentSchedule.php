<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSchedule extends Model
{
  public $timestamps=false;
  protected $table = 'wms_document_schedule';
  protected $fillable = ['document_id','schedule_id'];

  public function schedule()
  {
      return $this->belongsTo('App\Models\Schedule','schedule_id');
  }

  public function document()
  {
      return $this->belongsTo('App\Models\Document','document_id');
  }

  public function enlist_products()
  {
      return $this->hasMany('App\Models\EnlistProducts','document_id','document_id');
  }

  public function box_driver()
  {
      return $this->hasMany('App\Models\BoxDriver','order_number','document_id');
  }
}
