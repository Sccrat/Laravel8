<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterBox extends Model
{
  protected $table = 'wms_master_box';

  public $timestamps = false;

  protected $fillable = ['code14_id', 'master','peso','cliente_id'];

}
