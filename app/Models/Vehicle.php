<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $table = 'wms_vehicle';

    public $timestamps = false;

    protected $fillable = ['type', 'vehicle','plate', 'status'];
}
