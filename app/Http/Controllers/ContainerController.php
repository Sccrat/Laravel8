<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Models\ContainerFeature;

class ContainerController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $iscontainer_type_id = $request->input('container_type_id');
      $companyId = $request->input('company_id');

      $containers = Container::with('container_type','container_clasification')->where('company_id', $companyId)->orderBy('name');

      if(isset($iscontainer_type_id)) {
        $containers = $containers->where('container_type_id', $iscontainer_type_id);
      }

      $containers = $containers->get();
      return $containers->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $data = $request->all();
      $container = Container::create($data);

      if(array_key_exists('container_features', $data)) {
        $container->container_features()->createMany($data['container_features']);
      }

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
      $container = Container::with('container_features.feature','container_type')->findOrFail($id);
      return $container->toArray();
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
      $container = Container::findOrFail($id);

      $container->name = array_key_exists('name', $data) ? $data['name'] : $container->name;
      $container->code = array_key_exists('code', $data) ? $data['code'] : $container->code;
      $container->width = array_key_exists('width', $data) ? $data['width'] : $container->width;
      $container->depth = array_key_exists('depth', $data) ? $data['depth'] : $container->depth;
      $container->weight = array_key_exists('weight', $data) ? $data['weight'] : $container->weight;
      $container->height = array_key_exists('height', $data) ? $data['height'] : $container->height;
      $container->description = array_key_exists('description', $data) ? $data['description'] : $container->description;
      // $container->is_unidad_empaque = array_key_exists('is_unidad_empaque', $data) ? $data['is_unidad_empaque'] : $container->is_unidad_empaque;
      // $container->is_unidad_logistica = array_key_exists('is_unidad_logistica', $data) ? $data['is_unidad_logistica'] : $container->is_unidad_logistica;

      $container->container_type_id = array_key_exists('container_type_id', $data) ? $data['container_type_id'] : $container->container_type_id;
      // $container->clasification_container_id = array_key_exists('clasification_container_id', $data) ? $data['clasification_container_id'] : $container->clasification_container_id;

      $container->save();

      //Delete the warehouse_features
      if(array_key_exists('container_features', $data)) {
        ContainerFeature::where('container_id', $id)->delete();
        $container->container_features()->createMany($data['container_features']);
      }

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
        //
    }
}
