<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    protected $table = 'wms_features';

    public $timestamps = false;

    protected $fillable = ['name', 'company_id'];
}
