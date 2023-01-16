<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\DocumentDetail;
use DB;

class DocumentDetailController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $companyId = $request->input('company_id');
      $receiptType = $request->input('receipt_type');
      $documents = Document::with('detail')->where('company_id', $companyId);

      if(isset($receiptType)) {
        $documents = $documents->where('receipt_type_id', $receiptType);
      }
      //$documents = Document::with('detail')->get();
      $documents = $documents->get();
      return $documents->toArray();
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
        $data = $request->all();
        $return = DocumentDetail::create($data);
        if (!empty($data['products'])) {
          $return->detailMultiple()->createMany($data['products']);
        }
        return $return;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
      $withoutean14= $request->input('withoutean14');


      $query = DB::table('wms_document_details')
      ->join('wms_documents', 'wms_document_details.document_id', '=', 'wms_documents.id')
      ->leftjoin('wms_products', 'wms_document_details.product_id', '=', 'wms_products.id')
      ->leftjoin('wms_ean_codes14', 'wms_document_details.id', '=', 'wms_ean_codes14.document_detail_id')
      ->leftjoin('wms_document_detail_multiples', 'wms_document_details.id', '=', 'wms_document_detail_multiples.document_detail_id')
      ->leftjoin('wms_products as products_multiple', 'products_multiple.id', '=', 'wms_document_detail_multiples.product_id')
      ->where('wms_document_details.document_id', $id)
      // ->whereNotNull('wms_machines.warehouse_id')
      // ->whereNotNull('wms_machines.zone_id')

      // Traemos campos del detalle y del documento, adicional al momento de traer el numero de cajas validamos
      // Si ya tiene cajas validadas en el cierre wms_document_details.quanty_received y si no las tiene usamos
      // las que se especifican en el documento wms_document_details.cartons
      ->select('wms_document_details.id','wms_documents.number',
        'wms_document_details.quanty',
        'wms_document_details.id as document_details_id ',
        DB::raw('IF(wms_document_details.quarantine IS NOT NULL, wms_document_details.quarantine,0) as quarantine'),
        DB::raw('IF(wms_document_details.quanty_received IS NOT NULL,
                    IF(wms_document_details.quanty_received > wms_document_details.cartons,
                          wms_document_details.cartons,
                          wms_document_details.quanty_received)
                    ,wms_document_details.cartons) - COUNT(wms_ean_codes14.id) as cartons'),
        DB::raw('COUNT(wms_ean_codes14.id) as codes'),'wms_ean_codes14.id as ean14_id',


        DB::raw('IF(wms_products.reference IS NOT NULL,
          wms_products.reference,
          GROUP_CONCAT(products_multiple.reference SEPARATOR " ")) as reference'),

        DB::raw('IF(wms_products.ean IS NOT NULL,
          wms_products.ean,
          GROUP_CONCAT(products_multiple.ean SEPARATOR " ")) as code'),

        DB::raw('IF(wms_products.description IS NOT NULL,
          wms_products.description,
          GROUP_CONCAT(products_multiple.description SEPARATOR "-")) as description'),

        'wms_document_details.product_id')
      ->orderBy('wms_document_details.id')
      ->groupBy('wms_document_details.id');

      // Con esta validacion filtramos si queremos trarer el detalle de un documento que no tenga creado un ean14
      if(!empty($withoutean14)) {
        // $query->where('ean14_id IS NULL');
        $query->havingRaw('cartons > 0 OR  quarantine > 0');
      }

      $details  = $query->get();

      foreach ($details as $key => $value) {
        $documentDetail = DocumentDetail::findOrFail($value->id);
        $products = $documentDetail->detailMultiple()->get()->toArray();
        $details[$key]->products = [];
        if (count($products) > 0) {
          $details[$key]->products = $products;
        }else{
            $product = ['product_id'=>$details[$key]->product_id,'quanty'=>$details[$key]->quanty];
            array_push($details[$key]->products, $product);
        }

      }

      return $details;
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
      $data = $request->all();
      $documents = DocumentDetail::findOrFail($id);

      $documents->quanty_received = array_key_exists('quanty_received', $data) ? $data['quanty_received'] : $documents->quanty_received;
      $documents->is_additional = array_key_exists('is_additional', $data) ? $data['is_additional'] : $documents->is_additional;
      $documents->observations = array_key_exists('observations', $data) ? $data['observations'] : $documents->observations;
      $documents->quarantine = array_key_exists('quarantine', $data) ? $data['quarantine'] : $documents->quarantine;
      $documents->damaged_cartons = array_key_exists('damaged_cartons', $data) ? $data['damaged_cartons'] : $documents->damaged_cartons;
      $documents->weight = array_key_exists('weight', $data) ? $data['weight'] : $documents->weight;
      $documents->count_status = array_key_exists('count_status', $data) ? $data['count_status'] : $documents->count_status;
      $documents->approve_additional = array_key_exists('approve_additional', $data) ? $data['approve_additional'] : $documents->approve_additional;
      $documents->good_receive = array_key_exists('good_receive', $data) ? $data['good_receive'] : $documents->good_receive;
      $documents->seconds_receive = array_key_exists('seconds_receive', $data) ? $data['seconds_receive'] : $documents->seconds_receive;
      $documents->sin_conf_receive = array_key_exists('sin_conf_receive', $data) ? $data['sin_conf_receive'] : $documents->sin_conf_receive;
      $documents->new_quanty_receive = array_key_exists('new_quanty_receive', $data) ? $data['new_quanty_receive'] : $documents->new_quanty_receive;

      $documents->save();

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


    public function getDocumentDetailById($id)
    {
      $document = DocumentDetail::with('ean14','product.product_ean14s','detailCount.child_count.ean14',
        'detailCount.ean14','detailCount.detailMultipleCount.product','detailMultiple.product','product_ean14.containers','document.receipt_type')->where('document_id', $id)
      //->orderBy('number')
      ->get();
      return $document->toArray();
    }
    public function getDocumentDetailByStatus($status)
    {
      $document = DocumentDetail::with('ean14','product','detailCount.child_count.ean14',
        'detailCount.ean14','detailCount.detailMultipleCount.product','detailMultiple.product')->where('status', $status)
      //->orderBy('number')
      ->get();
      return $document->toArray();
    }
    public function updateAllDetailByDocument(Request $request,$id)
    {
      $data = $request->all();
      // $idDocument = array_key_exists('document_id', $data) ? $data['document_id'] : NULL;
      $idDocument = $id;
      $countStatus = array_key_exists('count_status', $data) ? $data['count_status'] : NULL;
      if (!empty($idDocument)) {
        $documentsToUpdate =  DocumentDetail::where('document_id',$idDocument);
        $updateObj = [];
        if (!empty($countStatus)) {
            $updateObj['count_status'] = $countStatus;
        }

        if (count($updateObj) > 0) {
            $affectedRows = $documentsToUpdate->update($updateObj);
            return $affectedRows;
        }else{
          return $this->response->noContent();
        }
      }else{
        return $this->response->noContent();
      }
    }

    public function updateDocCountStatus (Requests\DocumentDetailRequest $request, $id)
    {
        $data = $request->all();
        $status = $data['status'];

        DocumentDetail::where('id', $id)
                      ->update(['status' => $status]);
    }
}
