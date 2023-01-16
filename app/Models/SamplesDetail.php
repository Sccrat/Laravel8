<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamplesDetail extends Model
{
    protected $table = 'wms_samples_detail';

  	protected $fillable = ['sample_id','ean14_id','package_ean14_id', 'product_id', 'quanty', 'weight_reference'];

  	public $timestamps = false;

    public function ean14()
    {
      return $this->belongsTo('App\Models\EanCode14');
    }
}
