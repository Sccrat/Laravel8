<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\ProductSubType;

class ProductSubTypeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $companyId = $request->input('company_id');
        $pSubTypes = ProductSubType::with('product_type.product_category')->whereHas('product_type.product_category', function ($q) use ($companyId)
        {
          $q->where('company_id', $companyId);
        })->orderBy('name')->get();
        return $pSubTypes->toArray();
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
        ProductSubType::create($data);

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
        $pSubtype = ProductSubType::with('product_type.product_category')->findOrFail($id);
        return $pSubtype->toArray();
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
        $pSubtype = ProductSubType::findOrFail($id);

        $pSubtype->name = array_key_exists('name', $data) ? $data['name'] : $pSubtype->name;
        $pSubtype->active = array_key_exists('active', $data) ? $data['active'] : $pSubtype->active;
        $pSubtype->code = array_key_exists('code', $data) ? $data['code'] : $pSubtype->code;
        $pSubtype->product_type_id = array_key_exists('product_type_id', $data) ? $data['product_type_id'] : $pSubtype->product_type_id;



        $pSubtype->save();

        return $this->response->noContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
      $data = $request->all();
      $pSubtype = ProductSubType::findOrFail($id);

      $pSubtype->active = array_key_exists('active', $data) ? $data['active'] : $pSubtype->active;

      $pSubtype->save();

      return $this->response->noContent();
    }
}
