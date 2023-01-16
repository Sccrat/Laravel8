<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentDetail;
use App\Models\EanCode14;
use App\Models\Stock;
use App\Models\StockTransition;
use App\Models\ZonePosition;
use Exception;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use App\Common\Settings;

class StockTransitionController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $productType = $request->input('product_type_id');
      $warehouse = $request->input('warehouse_id');
      $reference = $request->input('reference');
      $ean128 = $request->input('ean128');
      $ean13 = $request->input('ean13');
      $client = $request->input('client_id');

      $stock = StockTransition::with(
        'product.product_sub_type.product_type',
        'zone_position.zone.warehouse',
        'product.client',
        'ean128',
        'ean14',
        'document_detail.document',
        'ean13'
        )->orderBy('zone_position_id');

      if (isset($ean128)) {
        $stock = $stock->orWhereHas('ean128',function ($q) use ($ean128)
        {
            $q->where('code128','LIKE',$ean128.'%');
        });
      }

      if (isset($ean13)) {
        $stock = $stock->orWhereHas('product',function ($q) use ($ean13)
        {
            $q->where('ean','LIKE',$ean13.'%');
        });
      }

      if(isset($productType)) {
        $stock = $stock->whereHas('product.product_sub_type', function ($q) use ($productType) {
          $q->where('product_type_id', $productType);
        });
      }

      // if(isset($warehouse)) {
      //   $stock = $stock->whereHas('zone_position.zone', function ($q) use ($warehouse) {
      //     $q->where('warehouse_id', $warehouse);
      //   });
      // }

      if(isset($reference)) {
        $stock = $stock->whereHas('product', function ($q) use ($reference) {
          $q->where('reference', $reference)->orWhere('code', $reference)->orWhere('ean', $reference);
        });
      }

      if(isset($client)) {
        $stock = $stock->whereHas('product', function ($q) use ($client) {
          $q->where('client_id', $client);
        });
      }

      $stock = $stock->get();

      return $stock->toArray();
    }

    public function getDataTransitionByDocument($id, Request $request){
      $data = $request->all();
      $settingsObj = new Settings($data['company_id']);
      $dispatch = $settingsObj->get('position_dispatch');

      $transition = StockTransition::from("wms_stock_transition as st")
      ->join("wms_ean_codes14 as e14", "st.code_ean14", "=", "e14.id")
      ->join("wms_document_details as dd", "st.document_detail_id", "=", "dd.id")
      ->join("wms_documents as d", "dd.document_id", "=", "d.id")
      ->selectRaw("
        e14.code14 as ean14,
        SUM(st.quanty) as unidades
      ")
      ->where("d.id", $id)
      ->groupBy("e14.id")
      ->get();

      $despacho = Document::join('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
      ->join('wms_stock', function($query){
          $query->on('wms_document_details.id', '=',  'wms_stock.document_detail_id');
          $query->on('wms_document_details.product_id', '=', 'wms_stock.product_id');
      })
      ->join('wms_zone_positions', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
      ->join('wms_zones', 'wms_zone_positions.zone_id', '=', 'wms_zones.id')
      ->join("wms_ean_codes14 as e14", "wms_stock.code_ean14", "=", "e14.id")
      ->selectRaw("
          e14.code14 as ean14,
          SUM(wms_stock.quanty) as unidades
        ")
      ->where('wms_zones.name', $dispatch)
      ->where('wms_documents.id', $id)
      ->groupBy("e14.id")
      ->get();

      $encabezado = Document::join("wms_document_details as dd", "wms_documents.id", "=", "dd.document_id")
      ->join("wms_clients as c", "wms_documents.client", "=", "c.id")
      ->selectRaw('
        wms_documents.number as documento,
        c.name as cliente')
      ->where("wms_documents.id", $id)
      ->first();

      return ["transition" => $transition->toArray(), "despacho" => $despacho->toArray(), "encabezado" => $encabezado];
    }

    public function saveReubicarPacking(Request $request){
      $data = $request->all();
      $documentId = array_key_exists('document', $data) ? $data['document'] : NULL;
      $positionDestino = array_key_exists('positionDestino', $data) ? $data['positionDestino'] : NULL;
      $ean14 = array_key_exists('ean14', $data) ? $data['ean14'] : NULL;

      $settingsObj = new Settings($data['company_id']);
      $dispatch = $settingsObj->get('position_dispatch');

      DB::beginTransaction();

      try{

          $dataEan14 = EanCode14::where('code14', $ean14)->where('document_id', $documentId)->first();
          if(!$dataEan14){
              throw new RuntimeException('El EAN 14 no existe o no está asociado a este pedido'); 
          }

          // return $dataEan14;

          $dataDocumentDetail = DocumentDetail::where('document_id', $documentId)
            ->selectRaw('group_concat(wms_document_details.id) as idDocumento')
            ->first();

          // return $dataDocumentDetail;

          // $positionDespacho = ZonePosition::join('wms_stock', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
          //   ->selectRaw(
          //       "wms_zone_positions.id")
          //   ->where('name', $dispatch)
          //   ->where('code', $positionDestino)
          //   ->first();
          $positionDespacho = DB::table('wms_zone_positions')
            ->join('wms_zones', 'wms_zone_positions.zone_id', '=', 'wms_zones.id')
            ->selectRaw(
                "wms_zone_positions.id")
            ->where('wms_zones.name', $dispatch)
            ->where('wms_zone_positions.code', $positionDestino)
            ->first();
            // return [$positionDespacho];

          if(!$positionDespacho){
            throw new RuntimeException('No se encontró la zona de despacho. Verifique por favor'); 
          }

          // $dataTransition = StockTransition::whereIn('document_detail_id', [$dataDocumentDetail->idDocumento])->where('code_ean14', $dataEan14->id)->toSql();
          $dataTransition = DB::Select(
            "SELECT
              * 
            FROM
              `wms_stock_transition` 
            WHERE
              `code_ean14` = $dataEan14->id");

          if($dataTransition){
            foreach ($dataTransition as $transition) {
              Stock::create([
                'product_id' => $transition->product_id,
                'zone_position_id' => $positionDespacho->id,
                'quanty' => $transition->quanty,
                'code_ean14' => $dataEan14->id,
                'document_detail_id' => $transition->document_detail_id,
                'quanty_14' => $transition->quanty,
              ]);
              StockTransition::where('id', $transition->id)->where('code_ean14', $dataEan14->id)->delete();
            }
          }

          // return $dataTransition;

          if(count($dataTransition) == 0){
            throw new RuntimeException('El EAN 14 ya fue leído o no está en la zona de transición');
          }

          DB::commit();
          return response([], 201);
      }catch(Exception $e){
          DB::rollBack();
          if($e instanceof RuntimeException){
              return response(["message" => $e->getMessage()], 409);
          }
          return response(["message" => $e->getMessage()], 500);
      }
  }
}
