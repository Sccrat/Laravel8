<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MachineType;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MachineTypeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $companyId = $request->input('company_id');
        $types = MachineType::where('active', true)->where('company_id', $companyId)->orderBy('name')->get();
        return $types->toArray();
    }


    public function getAllTypeMachines(Request $request)
    {
      $companyId = $request->input('company_id');
        $types = MachineType::where('company_id', $companyId)->orderBy('name')->get();
        return $types->toArray();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\MachineTypeRequest $request)
    {
      $data = $request->all();
      $companyId = $request->input('company_id');
      $data['active'] = true;
      $data['company_id'] = $companyId;
      MachineType::create($data);
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
      $machinetype = MachineType::findOrFail($id);
      return $machinetype->toArray();
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
      $machinetype = MachineType::findOrFail($id);

      $machinetype->name = array_key_exists('name', $data) ? $data['name'] : $machinetype->name;
      $machinetype->active = array_key_exists('active', $data) ? $data['active'] : $machinetype->active;
      // $machinetype->description = array_key_exists('description', $data) ? $data['description'] : $machinetype->description;

      $machinetype->save();

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
      $machinetype = MachineType::findOrFail($id);
      $machinetype->delete();

      return $this->response->noContent();
    }
}
