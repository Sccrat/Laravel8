<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Setting;

class SettingsController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $companyId = $request->input('company_id');
        $settings = Setting::where('company_id', $companyId)->where('show', 1)->get();
        return $settings->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function store(Requests\SettingRequest $request)
     {
       $data = $request->all();
       Setting::create($data);
       return $this->response->created();
     }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $setting = Setting::findOrFail($id);
      return $setting->toArray();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function update(Request $request, $id)
     {
       $data = $request->all();
       $setting = Setting::findOrFail($id);

       $setting->key = array_key_exists('key', $data) ? $data['key'] : $setting->key;
       $setting->value = array_key_exists('value', $data) ? $data['value'] : $setting->value;
       $setting->description = array_key_exists('description', $data) ? $data['description'] : $setting->description;

       $setting->save();

       return $this->response->noContent();
     }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $setting = Setting::findOrFail($id);
      $setting->delete();

      return $this->response->noContent();
    }
}
