<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Charge;

class ChargeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $companyId = $request->input('company_id');
      $name = $request->input('name');
      $charges = Charge::where('active', true)->where('company_id', $companyId);
      if(isset($name)) {
        $charges = $charges->where('name', urldecode($name));
      }
      $charges = $charges->get();
      return $charges->toArray();
    }

    public function getAllCharges(Request $request)
    {
      $companyId = $request->input('company_id');
      $charges = Charge::where('company_id', $companyId)->orderBy('name')->get();
      return $charges->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\ChargeRequest $request)
    {
      $companyId = $request->input('company_id');
      $data = $request->all();
      $data['company_id'] = $companyId;
      Charge::create($data);
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
      $charges = Charge::findOrFail($id);
      return $charges->toArray();
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
      $charge = Charge::findOrFail($id);

      $charge->name = array_key_exists('name', $data) ? $data['name'] : $charge->name;
      $charge->active = array_key_exists('active', $data) ? $data['active'] : $charge->active;
      // $machinetype->description = array_key_exists('description', $data) ? $data['description'] : $machinetype->description;

      $charge->save();

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
      $charge = Charge::findOrFail($id);
      $charge->delete();

      return $this->response->noContent();
    }
}
