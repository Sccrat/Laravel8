<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\ZoneConcept;

class ZoneConceptController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $companyId = $request->input('company_id');
      $storage = $request->input('storage');

      $concepts = ZoneConcept::where('company_id', $companyId)->orderBy('name');
      if($storage) {
        $concepts = $concepts->where('is_storage', true)->first();
        if(!empty($concepts)) {
          return $concepts->toArray();
        } else {
          return [];
        }
      }

      $concepts = $concepts->get();
      return $concepts->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\ZoneConceptRequest $request)
    {
      $companyId = $request->input('company_id');
      $data = $request->all();
      $data['company_id'] = $companyId;

      ZoneConcept::create($data);
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
      $zonepconcept = ZoneConcept::findOrFail($id);
      return $zonepconcept->toArray();
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
      $zoneconcept = ZoneConcept::findOrFail($id);

      $zoneconcept->name = array_key_exists('name', $data) ? $data['name'] : $zoneconcept->name;
      $zoneconcept->active = array_key_exists('active', $data) ? $data['active'] : $zoneconcept->active;
      $zoneconcept->color = array_key_exists('color', $data) ? $data['color'] : $zoneconcept->color;
      $zoneconcept->is_storage = array_key_exists('is_storage', $data) ? $data['is_storage'] : $zoneconcept->is_storage;

      $zoneconcept->save();
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
      $zoneconcept = ZoneConcept::findOrFail($id);
      $zoneconcept->delete();

      return $this->response->noContent();
    }
}
