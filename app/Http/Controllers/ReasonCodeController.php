<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\ReasonCode;

class ReasonCodeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $companyId = $request->input('company_id');
      $reasonCodes = ReasonCode::orderBy('name')->where('company_id', $companyId)->get();
      return $reasonCodes->toArray();
    }

    public function getAllReasonCodes(Request $request)
    {
      $companyId = $request->input('company_id');
      $reasonCodes = ReasonCode::orderBy('name')->where('company_id', $companyId)->get();
      return $reasonCodes->toArray();
    }

    public function getAllReasonCodesPicking(Request $request)
    {
      $reasonCodes = ReasonCode::where('type','type_picking')->orderBy('name')->get();
      return $reasonCodes->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\ReasonCodeRequest $request)
    {
      $data = $request->all();
      $data['company_id'] = $request->input('company_id');
      ReasonCode::create($data);
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
      $reasonCodes = ReasonCode::findOrFail($id);
      return $reasonCodes->toArray();
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
      $reasonCodes = ReasonCode::findOrFail($id);

      $reasonCodes->name = array_key_exists('name', $data) ? $data['name'] : $reasonCodes->name;
      $reasonCodes->active = array_key_exists('active', $data) ? $data['active'] : $reasonCodes->active;
      $reasonCodes->description = array_key_exists('description', $data) ? $data['description'] : $reasonCodes->description;
      $reasonCodes->code = array_key_exists('code', $data) ? $data['code'] : $reasonCodes->code;
      $reasonCodes->type = array_key_exists('type', $data) ? $data['type'] : $reasonCodes->type;

      $reasonCodes->save();

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
      $reasonCodes = ReasonCode::findOrFail($id);
      $reasonCodes->delete();

      return $this->response->noContent();
    }
}
