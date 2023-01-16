<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoxDriver extends Model
{
public $timestamps=false;
  protected $table = 'wms_box_driver';

  protected $fillable = ['plate', 'code14_id','order_number','quanty'];

  public function ean14()
  {
      return $this->belongsTo('App\Models\EanCode14','code14_id','id');
  }

  public function document()
  {
      return $this->belongsTo('App\Models\Document','id','order_number');
  }

  // public function stock()
  // {
  //     return $this->belongsTo('App\Models\Stock','code_ean14','code14_id');
  // }
}
