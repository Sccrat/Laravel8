<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\ProductCategory;

class ProductCategoryController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $companyId = $request->input('company_id');
      $productCategories = ProductCategory::where('company_id', $companyId)->with('product_types.product_sub_types')->orderBy('name')->get();
      return $productCategories->toArray();
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
      ProductCategory::create($data);
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
      $productCategory = ProductCategory::findOrFail($id);
      return $productCategory->toArray();
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
      // return $data;
      $productCategory = ProductCategory::findOrFail($id);

      $productCategory->name = array_key_exists('name', $data) ? $data['name'] : $productCategory->name;
      $productCategory->code = array_key_exists('code', $data) ? $data['code'] : $productCategory->code;
      $productCategory->active = array_key_exists('active', $data) ? $data['active'] : $productCategory->active;
      $productCategory->zone_id = array_key_exists('zone_id', $data) ? $data['zone_id'] : $productCategory->zone_id;

      $productCategory->save();

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
      $productCategory = ProductCategory::findOrFail($id);
      $productCategory->delete();

      return $this->response->noContent();
    }
}
