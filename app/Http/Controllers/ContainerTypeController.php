<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\ContainerType;

class ContainerTypeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $ispackaging_type = $request->input('packaging_type');
      $companyId = $request->input('company_id');

      $types = ContainerType::where('active', true)->where('company_id', $companyId)->orderBy('name');

      if(isset($ispackaging_type)) {
        $types = $types->where('packaging_type', $ispackaging_type);
      }

      // if(isset($isLogistica)) {
      //   $types = $types->where('is_unidad_logistica', true);
      // }

      $types = $types->get();
      return $types->toArray();
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function store(Request $request)
     {
       $companyId = $request->input('company_id');
       $data = $request->all();
       $data['company_id'] = $companyId;
       ContainerType::create($data);
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
       $contaynertype = ContainerType::findOrFail($id);
       return $contaynertype->toArray();
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
       $contaynertype = ContainerType::findOrFail($id);

       $contaynertype->name = array_key_exists('name', $data) ? $data['name'] : $contaynertype->name;
       $contaynertype->active = array_key_exists('active', $data) ? $data['active'] : $contaynertype->active;
       $contaynertype->packaging_type = array_key_exists('packaging_type', $data) ? $data['packaging_type'] : $contaynertype->packaging_type;
       $contaynertype->code_container_type = array_key_exists('code_container_type', $data) ? $data['code_container_type'] : $contaynertype->code_container_type;

       $contaynertype->save();

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
       $contaynertype = ContainerType::findOrFail($id);
       $contaynertype->delete();

       return $this->response->noContent();
     }
}
