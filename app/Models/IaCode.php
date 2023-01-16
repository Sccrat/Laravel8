<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IaCode extends Model
{
    protected $table = 'wms_ia_codes';

    public $timestamps = false;

    protected $fillable = ['code_ia', 'name', 'table', 'field'];


}
