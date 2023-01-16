<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StructureArea extends Model
{
  protected $table = 'wms_structure_areas';

  protected $fillable = ['description', 'levels', 'positions', 'active','structure_id', 'area_id', 'quantity','structure_area_levels', 'width_position', 'height_position', 'rows', 'modules', 'weight', 'depth' ];

  public function structure()
  {
   return $this->belongsTo('App\Models\Structure');
  }

  public function area()
  {
   return $this->belongsTo('App\Models\Area');
  }

  public function structure_area_levels()
  {
    return $this->hasMany('App\Models\StructureAreaLevel');
  }

  public function machines()
  {
    return $this->hasMany('App\Models\Machine');
  }

  public function work_areas()
  {
    return $this->hasMany('App\Models\WorkArea');
  }
}
