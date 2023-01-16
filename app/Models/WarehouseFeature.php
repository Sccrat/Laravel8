<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseFeature extends Model
{
    protected $table = 'wms_warehouse_features';

    public $timestamps = false;

    protected $fillable = ['feature_id', 'warehouse_id', 'comparation', 'value'];

    public function warehouse()
    {
      return $this->belongsTo('App\Models\Warehouse');
    }

    public function feature()
    {
      return $this->belongsTo('App\Models\Feature');
    }
}
