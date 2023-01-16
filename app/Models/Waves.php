<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Waves extends Model
{
  public $timestamps = true;
  protected $table = 'wms_waves';

  protected $fillable = ['UUID', 'documents'];
}
