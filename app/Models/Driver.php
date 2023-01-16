<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
  protected $table = 'wms_driver';

  public $timestamps = false;

  protected $fillable = ['driver_name', 'driver_lastname','driver_identification','driver_phone', 'status'];

}
