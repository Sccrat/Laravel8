<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\ReceiptType;
use App\Models\Client;
use App\Models\DocumentDetail;
use App\Common\Settings;
use App\Models\ProductEan14;

class ReceiptTypeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $types = ReceiptType::get();
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    public function defaultClientSoberana()
    {

      $client = Client::first();

      return $client->toArray();
    }

    public function getReceiptTypeReturn(Request $request)
    {
      $data = $request->all();

      $companyId = $data['company_id'];

      $settingsObj = new Settings($companyId);
      $devolucion = $settingsObj->get('returns');

      $types = ReceiptType::where('name',$devolucion)->get();

      return $types->toArray();
    }

    public function updatebatch(Request $request)
    {

      $data = $request->all();

    //   $valor = 0;
      $lote = array_key_exists('lote', $data) ? $data['lote'] : null;
      $vence = array_key_exists('vence', $data) ? $data['vence'] : null;
      $codeEan14 = array_key_exists('codeEan14', $data) ? $data['codeEan14'] : null;
      $document_id = array_key_exists('document_id', $data) ? $data['document_id'] : null;

      $validate = DocumentDetail::where('code_ean14', $codeEan14)->where('batch', $lote)->where('document_id', $document_id)->first();

      if ($validate) {
        //   return 5;
          if ($validate->different_batch) {
              $validate->cartons +=1;
              $validate->unit = $validate->cartons*$validate->unit;
              $validate->quanty_received +=1;
              $validate->save();
              $valor = 1;
          }else {
              $valor = 0;
          }
      }else {

         $cartons = ProductEan14::where('code_ean14',$codeEan14)->first();

         $objeto = [
            "document_id"=>$document_id,
            "unit"=>1*$cartons->quanty,
            "quanty"=>$cartons->quanty,
            "quanty_received"=>1,
            "cartons"=>1,
            "product_id"=>$cartons->product_id,
            "code_ean14"=>$codeEan14,
            "batch"=>$lote,
            "expiration_date"=>$vence,
            "different_batch"=>1
         ];
         $dato = DocumentDetail::create($objeto);

         $valor = 1;
      }

    //   DocumentDetail::where('code_ean14', $codeEan14)->update(['batch' => $lote,'expiration_date' => $vence]);

      return $valor;
    }


}
