<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionCenterFeature extends Model
{
    protected $table = 'wms_distribution_center_features';

    public $timestamps = false;

    protected $fillable = ['feature_id', 'distribution_center_id', 'comparation', 'value'];

    public function distribution_center()
    {
      return $this->belongsTo('App\Models\DistributionCenter');
    }

    public function feature()
    {
      return $this->belongsTo('App\Models\Feature');
    }
}
