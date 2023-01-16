<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkArea extends Model
{
  protected $table = 'wms_work_areas';

  protected $fillable = ['name', 'active', 'from_row', 'to_row', 'from_position', 'to_position', 'structure_area_id'];

  public function structure_area()
  {
    return $this->belongsTo('App\Models\StructureArea');
  }

  public function personal()
  {
    return $this->hasMany('App\Models\Persona');
  }
}
