<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnlistProductsWavesUsers extends Model
{
  public $timestamps = false;
  protected $table = 'wms_enlist_products_waves_users';

  protected $fillable = ['enlistproductwave_id', 'user_id', 'quanty', 'picked_quanty'];

  public function enlistProductWave()
  {
    return $this->belongsTo('App\Models\EnlistProductsWaves', 'enlistproductwave_id');
  }

  public function user()
  {
    return $this->belongsTo('App\Models\User', 'user_id');
  }
}
