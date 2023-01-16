<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContainerClasification extends Model
{
  protected $table = 'wms_container_clasifications';

  protected $fillable = ['name', 'active'];

  public function containers()
  {
   return $this->hasMany('App\Models\Container');
  }
}
