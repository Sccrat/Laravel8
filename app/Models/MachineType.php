<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineType extends Model
{
  protected $table = 'wms_machine_types';

  protected $fillable = ['name', 'active', 'company_id'];

  public function machines()
  {
   return $this->hasMany('App\Models\Machine');
  }
}
