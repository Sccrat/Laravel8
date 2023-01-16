<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WavesCodes14 extends Model
{
  public $timestamps = false;
  protected $table = 'wms_waves_eancodes14';

  protected $fillable = ['wave_id', 'eancode14_id', 'stock_id'];

  public function wave()
  {
    return $this->belongsTo('App\Models\Waves', 'wave_id');
  }

  public function code14()
  {
    return $this->belongsTo('App\Models\EanCode14', 'eancode14_id');
  }

  public function stock()
  {
    return $this->belongsTo('App\Models\Stock', 'stock_id');
  }
}
