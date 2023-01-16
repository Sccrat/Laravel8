<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleCompany extends Model
{
    protected $table = 'admin_role_companies';

    protected $fillable = ['role_id', 'menu_template', 'company_id'];

    public $timestamps = false;

    public function role()
    {
      return $this->belongsTo('App\Models\Role');
    }

    public function company()
    {
      return $this->belongsTo('App\Models\Company');
    }
}
