<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['name', 'active', 'company_id'];

    protected $table = 'wms_brands';

    public $timestamps = false;

    function products()
    {
      return $this->hasMany('App\Models\Product');
    }
}
