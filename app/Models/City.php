<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    public $timestamps=false;
    protected $table = 'cities';

    protected $fillable = ['name', 'country_code', 'district', 'population','dispatch_time'];

    public function structures()
    {
      return $this->hasMany('App\Models\Structure');
    }

    public function country()
    {
      return $this->belongsTo('App\Models\Country');
    }

    public function clients()
    {
      return $this->hasMany('App\Models\Client');
    }

    // public function children()
    // {
    //   return $this->clients();
    // }

}
