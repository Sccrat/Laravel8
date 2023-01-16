<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StructureType extends Model
{
    //
    protected $table = 'wms_structure_types';

    protected $fillable = ['name', 'company_id'];

    public function structure()
    {
      return $this->hasMany('App\Models\Structure');
    }
}
