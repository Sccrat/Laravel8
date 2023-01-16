<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Samples extends Model
{
    protected $table = 'wms_samples';

  	protected $fillable = ['warehouse_id','schedule_id', 'document_id', 'user_id','zone_id'];

  	public function sampleDetail()
  	{
   		return $this->hasMany('App\Models\SamplesDetail');
  	}
}
