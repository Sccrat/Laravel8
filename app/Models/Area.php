<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'wms_areas';

    protected $fillable = ['name', 'is_storage'];

    protected $hidden = ['created_at', 'updated_at', 'company_id'];

    public function structure_area_detail()
    {
      return $this->hasMany('App\Models\StructureArea');
    }
}
