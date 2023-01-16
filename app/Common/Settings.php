<?php

namespace App\Common;
use App\Models\Setting;

/**
 * Custom class for confoguration settings stored in the table wms_settings
 */
class Settings
{

  private $settings;
  function __construct($companyId)
  {
    $this->settings = Setting::where('company_id', $companyId)->get();
    // $this->settings = Setting::all();
  }

  public function Get($key)
  {
    $settings = $this->settings;
    $wcode = $settings->filter(function ($setting) use ($key) {
      return $setting->key == $key;
    })->first();

    return empty($wcode->value)?NULL:$wcode->value;
    //return $wcode->toArray();
    //return DB::table('wms_settings')->where('key', $key)->pluck('value');
  }
}
