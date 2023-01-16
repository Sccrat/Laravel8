<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\IaCode;

class IaCodeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $iacodes = IaCode::get();
        return $iacodes->toArray();
    }

    public function show($id)
    {
      $iacode = IaCode::findOrFail($id);
      return $iacode->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\IaCodeRequest $request)
    {
      $data = $request->all();
      IaCode::create($data);
      return $this->response->created();
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
      $iacode = IaCode::findOrFail($id);

      $iacode->code_ia = array_key_exists('code_ia', $data) ? $data['code_ia'] : $iacode->code_ia;
      $iacode->name = array_key_exists('name', $data) ? $data['name'] : $iacode->name;
      $iacode->table = array_key_exists('table', $data) ? $data['table'] : $iacode->table;
      $iacode->field = array_key_exists('field', $data) ? $data['field'] : $iacode->field;

      $iacode->save();

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
      $iacode = IaCode::findOrFail($id);
      $iacode->delete();

      return $this->response->noContent();
    }
}
