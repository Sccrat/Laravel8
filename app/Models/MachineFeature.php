<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineFeature extends Model
{
  protected $table = 'wms_machine_features';

  public $timestamps = false;

  protected $fillable = ['feature_id', 'machine_id', 'value'];

  public function machine()
  {
    return $this->belongsTo('App\Models\Machine');
  }

  public function feature()
  {
    return $this->belongsTo('App\Models\Feature');
  }
}
