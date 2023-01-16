<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'admin_roles';

    protected $fillable = ['name'];

    public $timestamps = false;

    public function role_companies()
    {
      return $this->hasMany('App\Models\RoleCompany');
    }
}
