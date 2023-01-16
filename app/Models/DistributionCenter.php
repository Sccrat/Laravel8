<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionCenter extends Model
{
  protected $table = 'wms_distribution_centers';

  protected $fillable = ['code', 'name', 'city_id', 'address', 'active', 'real_code', 'company_id'];

  public function warehouses()
  {
    return $this->hasMany('App\Models\Warehouse');
  }

  public function city()
  {
    return $this->belongsTo('App\Models\City');
  }

  public function distribution_center_features()
  {
    return $this->hasMany('App\Models\DistributionCenterFeature');
  }
}
