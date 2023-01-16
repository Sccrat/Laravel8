<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContainerType extends Model
{
  protected $table = 'wms_container_types';

  public $timestamps = false;

  protected $fillable = ['name', 'active','code_container_type','packaging_type', 'company_id'];

  public function containers()
  {
   return $this->belongsTo('App\Models\Container');
  }
}
