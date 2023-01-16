<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
  protected $table = 'wms_charges';

  protected $fillable = ['name','active', 'company_id'];

  public function personal()
  {
    return $this->hasMany('App\Models\Person');
  }
}
