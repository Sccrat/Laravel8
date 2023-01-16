<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StructureAreaDetail extends Model
{
  protected $table = 'wms_structure_areas_detail';

  protected $fillable = ['description', 'quantity', 'structure_area_id', 'area_id'];
  //protected $fillable = ['name', 'levels', 'positions', 'active', ]
  public function structure_area()
  {
   return $this->belongsTo('App\Models\StructureArea');
  }

  public function area()
  {
   return $this->belongsTo('App\Models\Area');
  }
}
