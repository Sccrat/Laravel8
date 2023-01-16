<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\StructureCode;
use App\Models\EanCode14;
use App\Models\EanCode128;
use App\Models\Product;
use App\Models\ProductEan14;
use App\Enums\PackagingType;
use App\Common\Codes;

class StructureCodeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $StructureCode = StructureCode::get();
        return $StructureCode->toArray();
    }

    public function getStructureByPackingId($id)
    {
      $StructureCode = Codes::GetStructureCode($id);
      // $StructureCode = StructureCode::with('ia_code')
      // ->where('packaging_type', $id)
      // ->orderBy('id')
      // ->get();
      return $StructureCode;
    }

    public function getTypeCode(Request $request)
    {
      $companyId =  $request->input('company_id');
      $data = $request->all();
      if ($data['flag']) {
        $code =  $data['code'];
      }else {
        $ean14_largo = $data['code'];
        $code =  $ean14_largo;
      }
      // return $code;


      $ean128    =  EanCode128::where('code128',$code)
                    ->where('company_id', $companyId)->first();

      $ean14     =  ProductEan14::where('code_ean14',$code)
                    ->first();

      $ean13     =  Product::where('ean',$code)
                    ->where('company_id', $companyId)->first();

      $codeType = "";
      $res = [
        'code'=>'',
        'codeType'=>'',
      ];
      $res['code'] = [];
      //Optimized conditions BETA
      if (!empty($ean128) && empty($ean14) && empty($ean13)) {
        $codeType = PackagingType::Logistica;
        $res['code'] = $ean128;
      } else if (!empty($ean14) && empty($ean13)) {
        $codeType = PackagingType::Empaque;
        $res['code'] = $ean14;
      } else if (!empty($ean13)) {
        $codeType = PackagingType::Producto;
        $res['code'] = $ean13;
      } else {
        return $this->response->error('not_found', 404);
      }
      // if (!empty($ean128) && empty($ean14) && empty($ean13)) {
      //   $codeType = PackagingType::Logistica;
      //   $res['code'] = $ean128;
      // }else if (empty($ean128) && !empty($ean14) && empty($ean13)) {
      //   $codeType = PackagingType::Empaque;
      //   $res['code'] = $ean14;
      // }else if (empty($ean128) && empty($ean14) && !empty($ean13)) {
      //   $codeType = PackagingType::Producto;
      //   $res['code'] = $ean13;
      // }

      $res['codeType'] = $codeType;
      return $res;
    }

    public function show($id)
    {
      $StructureCode = StructureCode::findOrFail($id);
      return $StructureCode->toArray();
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
      $id = $data['codes'][0]['packaging_type'];

      StructureCode::where('packaging_type', $id)->delete();

      //Save the new positions
      StructureCode::insert($data['codes']);
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
      //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $StructureCode = StructureCode::findOrFail($id);
      $StructureCode->delete();

      return $this->response->noContent();
    }
}
