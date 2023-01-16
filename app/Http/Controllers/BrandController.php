<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Brand;

class BrandController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $companyId = $request->input('company_id');
        $brands = Brand::where('company_id', $companyId)->orderBy('name')->get();
        return $brands->toArray();
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
      Brand::create($data);

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
      $brand = Brand::findOrFail($id);

      return $brand->toArray();
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
      $brand = Brand::findOrFail($id);

      $brand->name = array_key_exists('name', $data) ? $data['name'] : $brand->name;
      $brand->active = array_key_exists('active', $data) ? $data['active'] : $brand->active;

      $brand->save();

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
      $brand = Brand::findOrFail($id);
      $brand->delete();

      return $this->response->noContent();
    }
}
