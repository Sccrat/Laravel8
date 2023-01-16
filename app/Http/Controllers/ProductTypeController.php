<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\ProductType;

class ProductTypeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $client = $request->input('client_id');
      $companyId = $request->input('company_id');
      //Check if we need to get the product types by client
      //$producttypes = new ProductType();
      $producttypes = ProductType::with('product_sub_types', 'product_category')->whereHas('product_category', function ($q) use ($companyId)
      {
        $q->where('company_id', $companyId);
      })->orderBy('name');
      if(isset($client)) {
        $producttypes = $producttypes->whereHas('product_sub_types', function ($q) use ($client)
        {
          $q->whereHas('products', function ($q) use ($client)
          {
            $q->where('client_id', $client);
          });
        });
      }

      $producttypes = $producttypes->get();

      return $producttypes->toArray();
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
       ProductType::create($data);

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
       $producttype = ProductType::findOrFail($id);
       return $producttype->toArray();
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
       $pType = ProductType::findOrFail($id);

       $pType->name = array_key_exists('name', $data) ? $data['name'] : $pType->name;
       $pType->active = array_key_exists('active', $data) ? $data['active'] : $pType->active;
       $pType->code = array_key_exists('code', $data) ? $data['code'] : $pType->code;
       $pType->product_category_id = array_key_exists('product_category_id', $data) ? $data['product_category_id'] : $pType->product_category_id;

       $pType->save();

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
       $pType = Person::findOrFail($id);

       $pType->active = array_key_exists('active', $data) ? $data['active'] : $pType->active;

       $pType->save();

       return $this->response->noContent();
     }
}
