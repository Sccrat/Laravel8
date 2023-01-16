<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Common\Products;
use Illuminate\Support\Facades\DB;
use App\Models\Stock;
use App\Models\ProductFeature;
use App\Models\ProductEan14;
use App\Models\Brand;
use App\Models\User;

class ProductController extends BaseController
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    $reference = $request->input('reference');
    $companyId = $request->input('company_id');
    // $products = Product::with('category')->where('company_id', $companyId);
    $products = DB::table('wms_products')
      ->leftjoin('wms_product_categories', 'wms_products.category_id', '=', 'wms_product_categories.id')
      ->where('wms_products.company_id', $companyId)
      ->select('wms_products.short_description', 'wms_products.description', 'wms_products.reference', 'wms_products.ean', 'wms_products.origin', 'wms_product_categories.name', 'wms_products.id', 'wms_products.active', 'wms_products.size', 'wms_products.colour', 'wms_products.remark')
      ->orderBy('id', 'desc')
      ->limit(28000);

    if (!empty($reference)) {
      $products->where('wms_products.reference', 'LIKE', '%' . $reference . '%');
    }

    return $products->orderBy('wms_products.description')->get();
  }

  public function getProductByAttributes(Request $request)
  {
    $code = $request->input('code');
    $reference = $request->input('reference');
    $product = Product::with('product_sub_type.product_type');
    $filter = false;
    if (!empty($code)) {
      $product->where('ean', $code);
      $filter = true;
    }
    if (!empty($reference)) {
      $product->where('reference', $reference);
      $filter = true;
    }
    if ($filter) {
      return $product->first();
    } else {
      return [];
    }
  }

  public function getProductsByPositionId(Request $request, $positionid)
  {
    $products = Products::GetProcutcsByPositionId($positionid);
    return $products;
  }

  public function getProcutByCode(Request $request, $code)
  {
    $products = Product::where('code', $code)->with('product_sub_type.product_type')->first();

    $result = [];
    $result['product'] = $products;
    $positions = Stock::where('product_id', $products['id'])->with('product', 'zone_position')->get();
    $result['positions'] = $positions;
    return $result;
  }

  public function getProcutsByType(Request $request, $type)
  {
    $products = Product::with('product_sub_type.product_type')->whereHas('product_sub_type', function ($q) use ($type) {
      $q->where('product_type_id', $type);
    })->get();

    //TODO:: validate if we can erase the old way
    // $products = Product::where('product_type_id',$type)->with('product_type')->get();
    return $products->toArray();
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $data = $request->all();

    $username = User::where('id', $data['session_user_id'])->first();

    $brand = Brand::where('name', '=', 'Maaji')->get()->toArray();
    $data['origin'] = 'local';
    $data['brand_id'] = $brand[0]['id'];
    $data['unit'] = 'unit';

    $data['usercreated_id'] = $username->id;

    $product = Product::create($data);

    // Add the product features
    if (array_key_exists('product_features', $data)) {
      $product->product_features()->createMany($data['product_features']);
    }

    //product_ean14s
    if (array_key_exists('product_ean14s', $data)) {
      $product->product_ean14s()->createMany($data['product_ean14s']);
    }

    return $this->response->created();
  }

  /**
   * Display the specified resource.
   *
   * @param int $id
   * @return array
   */
  public function show($id)
  {
    $product = Product::with('category', 'brand', 'product_features.feature', 'product_ean14s')->findOrFail($id);
    if ($product) {
      $product->vendor = DB::table('wms_clients as c')
        ->join('countries as customer_country', 'customer_country.id', '=', 'c.customer_country_id')
        ->join('countries as shipping_country', 'shipping_country.id', '=', 'c.shipping_country_id')
        ->join('cities as customer_city', 'customer_city.id', '=', 'c.customer_city_id')
        ->join('cities as shipping_city', 'shipping_city.id', '=', 'c.shipping_city_id')
        ->select(
          'c.id',
          'c.company_name as names',
          'c.customer_street as street',
          'c.customer_street_2 as street_2',
          'c.shipping_company_name as shipping_names',
          'c.shipping_street',
          'c.shipping_street_2',
          'customer_country.name as country',
          'shipping_country.name as shipping_country',
          'customer_city.name as city',
          'shipping_city.name as shipping_city',
          'c.customer_zip_code as zip_code',
          'c.shipping_zip_code as shipping_zip_code',
          'c.type',
          'c.contact_name_1',
          'c.contact_name_2'
        )
        ->where('c.id', $product->vendor_id)
        ->first();
    }
    return $product->toArray();
  }

  /**
   * Update the specified resource in storage.
   *
   * @param \Illuminate\Http\Request $request
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    $data = $request->all();
    $product = Product::findOrFail($id);

    $product->description = array_key_exists('description', $data) ? $data['description'] : $product->description;
    //$product->product_type_id = array_key_exists('product_type_id', $data) ? $data['product_type_id'] : $product->product_type_id;
    //        $product->product_sub_type_id = array_key_exists('product_sub_type_id', $data) ? $data['product_sub_type_id'] : $product->product_sub_type_id;
    $product->category_id = array_key_exists('category_id', $data) ? $data['category_id'] : $product->category_id;
    $product->code = array_key_exists('code', $data) ? $data['code'] : $product->code;
    $product->reference = array_key_exists('reference', $data) ? $data['reference'] : $product->reference;
    $product->colour = array_key_exists('colour', $data) ? $data['colour'] : $product->colour;
    $product->size = array_key_exists('size', $data) ? $data['size'] : $product->size;
    $product->active = array_key_exists('active', $data) ? $data['active'] : $product->active;
    $product->ean = array_key_exists('ean', $data) ? $data['ean'] : $product->ean;
    $product->short_description = array_key_exists('short_description', $data) ? $data['short_description'] : $product->short_description;
    // $product->short_description = array_key_exists('product_category', $data) ? $data['product_category'] : $product->short_description;

    $username = User::where('id', $data['session_user_id'])->first();
    $product->userupdated_id = $username->id;
    $product->save();

    //Delete the warehouse_features
    if (array_key_exists('product_features', $data)) {
      ProductFeature::where('product_id', $id)->delete();
      $product->product_features()->createMany($data['product_features']);
    }

    if (array_key_exists('product_ean14s', $data)) {
      ProductEan14::where('product_id', $id)->delete();
      $product->product_ean14s()->createMany($data['product_ean14s']);
    }

    return $this->response->noContent();
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $data = $request->all();
    $product = Product::findOrFail($id);

    $product->active = array_active_exists('active', $data) ? $data['active'] : $product->active;

    $product->save();

    return $this->response->noContent();
  }

  public function getColors(Request $request)
  {
    $data = $request->all();
    $colors = DB::table('wms_colors');

    if (!empty($data['search'])) {
      $colors->orWhere('alternative_id', 'LIKE', $data['search'] . '%');
      $colors->orWhere('name', 'LIKE', $data['search'] . '%');
      $colors->orWhere('alternative_name', 'LIKE', $data['search'] . '%');
    }

    return $colors->get();
  }

  public function getSizes(Request $request)
  {
    $data = $request->all();
    $sizes = DB::table('wms_sizes');


    if (!empty($data['complement'])) {
      $sizes->where('is_complement', 1);
    } else {
      $sizes->where('is_complement', 0);
    }

    if (!empty($data['search'])) {

      $sizes->where(function ($q) use ($data) {
        $q->orWhere('alternative_id', 'LIKE', $data['search'] . '%');
        $q->orWhere('name', 'LIKE', $data['search'] . '%');
        $q->orWhere('alternative_name', 'LIKE', $data['search'] . '%');
        $q->orWhere('alternative_name2', 'LIKE', $data['search'] . '%');
      });
    }

    return $sizes->get();
  }

  public function getProductsBySearch($search)
  {
    return Product::with('presentation', 'product_features', 'stock')
      ->where('short_description', 'LIKE', '%' . $search . '%')
      ->orWhere('description', 'LIKE', '%' . $search . '%')
      ->orWhere('reference', 'LIKE', $search . '%')
      ->get()->toArray();
  }

  public function getMaterials()
  {
    return Product::from('wms_products as p')
      ->leftJoin('wms_product_categories as pc', 'p.category_id', '=', 'pc.id')
      ->where('pc.name', 'Materiales')
      ->select([
        'p.id',
        'p.description'
      ])
      ->get()->toArray();
  }
}
