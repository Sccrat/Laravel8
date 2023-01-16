<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
  protected $table = 'wms_groups';

  protected $fillable = ['name', 'active', 'company_id'];

  public function personal()
  {
    return $this->hasMany('App\Models\Person');
  }

}
