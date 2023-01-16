<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class LocationController extends BaseController
{
    public function getCities()
    {
      // $users = DB::table('cities')->get();

      $lol = Input::get('value');

      $users = DB::table('cities')
            ->join('countries', 'cities.country_code', '=', 'countries.code')
            ->select('cities.*', 'countries.name as country_name')
            ->where('cities.name', 'like', $lol . '%')
            ->get();

      return $users;
    }
}
