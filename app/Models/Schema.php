<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schema extends Model
{
  protected $table = 'wms_schemas';

  protected $fillable = ['name', 'active'];

  public function products()
  {
    return $this->hasMany('App\Models\Product');
  }
}
