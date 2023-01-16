<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\ZoneType;

class ZoneTypeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $companyId = $request->input('company_id');
        $zoneTypes = ZoneType::where('company_id', $companyId)->orderBy('name')->get();
        return $zoneTypes->toArray();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\ZoneTypeRequest $request)
    {
      $companyId = $request->input('company_id');
      $data = $request->all();
      $data['company_id'] = $companyId;
      ZoneType::create($data);
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
        $zonetype = ZoneType::findOrFail($id);
        return $zonetype->toArray();
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
      $zonetype = ZoneType::findOrFail($id);

      $zonetype->name = array_key_exists('name', $data) ? $data['name'] : $zonetype->name;
      $zonetype->active = array_key_exists('active', $data) ? $data['active'] : $zonetype->active;
      $zonetype->is_storage = array_key_exists('is_storage', $data) ? $data['is_storage'] : $zonetype->is_storage;

      $zonetype->save();

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
       $zonetype = ZoneType::findOrFail($id);
       $zonetype->delete();

       return $this->response->noContent();
     }
}
