<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VinculationType extends Model
{
  protected $table = 'wms_vinculation_types';

  protected $fillable = ['name', 'active'];

  public function personal()
  {
    return $this->hasMany('App\Models\Person');
  }
}
