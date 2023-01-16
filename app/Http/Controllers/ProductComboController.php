<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\ProductCombo;
use App\Models\ProductComboDetail;

class ProductComboController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $companyId = $request->input('company_id');
        $combos = ProductCombo::where('company_id', $companyId)->orderBy('name')->get();

        return $combos->toArray();
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
      $product = ProductCombo::create($data);
      //Add the product features
      if(array_key_exists('product_combo_detail', $data)) {
        $product->product_combo_detail()->createMany($data['product_combo_detail']);
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
      $product = ProductCombo::with('product_combo_detail.product')->findOrFail($id);
      return $product->toArray();
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
      $product = ProductCombo::findOrFail($id);

      $product->name = array_key_exists('name', $data) ? $data['name'] : $product->name;
      $product->ean = array_key_exists('ean', $data) ? $data['ean'] : $product->ean;
      $product->reference = array_key_exists('reference', $data) ? $data['reference'] : $product->reference;

      $product->save();

      //Delete the warehouse_features
      if(array_key_exists('product_combo_detail', $data)) {
        ProductComboDetail::where('product_combo_id', $id)->delete();
        $product->product_combo_detail()->createMany($data['product_combo_detail']);
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
