<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StructureCode extends Model
{
    protected $table = 'wms_structure_codes';

    public $timestamps = false;

    protected $fillable = ['ia_code_id', 'packaging_type'];

    public function ia_code()
    {
        return $this->belongsTo('App\Models\IaCode');
    }

}
