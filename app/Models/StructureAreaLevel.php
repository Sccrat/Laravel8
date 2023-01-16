<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StructureAreaLevel extends Model
{
    protected $table = 'wms_structure_area_levels';

    public $timestamps = false;

    protected $fillable = ['level', 'position', 'description', 'width', 'height', 'active', 'structure_area_id', 'weight', 'depth'];

    public function structure_area()
    {
      return $this->belongsTo('App\Models\StructureArea');
    }
}
