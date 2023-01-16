<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'wms_settings';

    protected $fillable = ['key', 'value', 'description', 'company_id'];

    public $timestamps = false;
}
