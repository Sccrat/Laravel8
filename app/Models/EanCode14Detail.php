<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EanCode14Detail extends Model
{
    protected $table = 'wms_ean_codes14_detail';

    protected $fillable = ['ean_code14_id','product_id','quanty','good','seconds','sin_conf','quanty_receive','good_receive','seconds_receive','sin_conf_receive','document_detail_id','good_pallet','seconds_pallet','sin_conf_pallet'];

    public $timestamps = false;

    public function product()
	{
	  return $this->belongsTo('App\Models\Product');
	}

  public function ean_code14()
  {
    return $this->belongsTo('App\Models\EanCode14');
  }
}
