<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
  protected $table = 'admin_companies';

  protected $fillable = ['name', 'active'];

  public function users()
  {
    return $this->hasMany('App\Models\User');
  }
}
