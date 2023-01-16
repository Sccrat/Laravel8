<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Structure extends Model
{
    //
    protected $table = 'wms_structures';

    protected $fillable = ['name', 'parent_id', 'structure_type_id', 'active', 'company_id', 'address', 'city_id', 'code', 'real_code'];

    public function structure_type()
    {
      return $this->belongsTo('App\Models\StructureType');
    }

    public function city()
    {
      return $this->belongsTo('App\Models\City');
    }

    public function structure_area()
    {
      return $this->hasMany('App\Models\StructureArea');
    }
}
