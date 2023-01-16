<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackingType extends Model
{
  protected $table = 'wms_packing_types';

  public $timestamps = false;

  protected $fillable = ['name', 'code'];

}
