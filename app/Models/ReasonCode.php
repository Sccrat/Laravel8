<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReasonCode extends Model
{
  protected $table = 'wms_reason_codes';

  protected $fillable = ['name', 'description','code','active','type', 'company_id'];

  public $timestamps = false;
}
