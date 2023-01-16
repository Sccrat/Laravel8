<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presentation extends Model
{
  protected $table = 'wms_presentations';

  protected $fillable = ['presentation', 'measure', 'company_id'];

  public $timestapms = false;

  public function products()
  {
    return $this->hasMany('App\Models\Product');
  }
}
