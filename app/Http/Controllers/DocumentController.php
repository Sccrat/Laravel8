<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Requisition;
use App\Models\Services;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Enums\DocType;
use App\Common\Settings;
use App\Models\City;
use App\Models\EnlistProducts;
use App\Models\Product;
use App\Models\ScheduleEnlist;
use Illuminate\Support\Facades\DB;
use App\Enums\SettingsKey;
// use App\Common\UserCommon;
use App\Models\User;
use App\Models\Stock;
use App\Models\Schedule;
use App\Enums\ScheduleAction;
use App\Enums\ScheduleType;
use App\Enums\ScheduleStatus;
use App\Common\SchedulesFunctions;
use App\Models\StockTransition;
use App\Models\ScheduleTransition;
use App\Models\DocumentDetail;
use App\Models\ZonePosition;
use App\Models\SchedulePacking;
use App\Common\Codes;
use App\Models\EanCode14;
use App\Models\EanCode14Detail;
use App\Models\SchedulePositionPacking;
use App\Models\Eancodes14Packing;
use App\Models\PackingZones;
use App\Models\ProgressTask;
use App\Models\ZoneConcept;
use App\Common\SoberanaServices;
use App\Imports\ComExImport;
use App\Models\Warehouse;
use App\Models\Zone;
use App\Models\StockCount;
use App\Models\ScheduleDispatch;
use App\Models\DocumentSchedule;
use App\Transformers\ScheduleTransformer;
use App\Models\BoxDriver;
use App\Models\Pallet;
use App\Models\ProductEan14;
use App\Models\EanCode128;
use App\Models\ScheduleStock;
use DateTimeZone;
use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Client;
use App\Models\Container;
use App\Models\EanCode14Serial;
use App\Models\Person;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\EnlistPackingWaves;
use App\Models\EnlistProductsWaves;
use App\Models\EnlistProductsWavesUsers;
use App\Models\MasterBox;
use App\Models\ScheduleEAN14;
use Dingo\Api\Http\Response;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use App\Models\ProductCategory;
use App\Models\ScheduleDocument;
use App\Models\ScheduleImage;
use Storage;
use App\Models\StockCountPosition;
use App\Models\StockMovement;
use App\Models\Waves;
use App\Models\WavesCodes14;
use Maatwebsite\Excel\Facades\Excel;
use Image;


class DocumentController extends BaseController
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  // public function index(Request $request)
  // {
  //   $companyId = $request->input('company_id');
  //   $receiptType = $request->input('receipt_type');
  //   $is_special = $request->input('is_special');
  //
  //   $docType = $request->input('doc_type');
  //
  //   //Check if is a departure
  //   if (isset($docType) && $docType == DocType::Departure) {
  //     $documents = Document::with('detail.product.stock')->where('document_type', $docType)
  //     ->where('company_id', $companyId)
  //     ->get();
  //     return $documents->toArray();
  //   }
  //
  //   // Adjuntamos a la informacion del documento la informacion de cada detalle relacionado y por cada detalle su producto y conteo
  //   $documents = Document::with('detail.detailCount.child_count','detail.product','scheduleDocument.schedule.schedule_receipt')
  //   ->where('company_id', $companyId);
  //
  //   if(isset($receiptType)) {
  //     $documents = $documents->where('receipt_type_id', $receiptType);
  //   }
  //   if (empty($is_special)) {
  //     $documents = $documents->whereRaw('is_special is null');
  //   }
  //   $documents = $documents->get();
  //   // $documentsAr = $documents->toArray();
  //
  //   // Traigo solamente los conteos que tiene un valor mayor a 0, por que los que tienen 0 son cajas que no existen
  //   // for ($i=0; $i < count($documentsAr); $i++) {
  //   //   # code...
  //   // }
  //   // $documents = $documents->whereHas('detail.detailCount', function ($query)
  //   //       {
  //   //           return $query->where('detailCount.quanty','>',0);
  //   //       });
  //   //$documents = Document::with('detail')->get();
  //   // return $documentsAr;
  //   return $documents->toArray();
  // }

  public function index(Request $request)
  {
    $receiptType = $request->input('receipt_type');
    $is_special = $request->input('is_special');
    $companyId = $request->input('company_id');
    $scheduleId = $request->input('schedule_id');
    $warehouse_destination = $request->input('warehouse_destination');

    $docType = $request->input('doc_type');

    // return $request;

    // $number = $request->input('number');


    //Check if is a departure
    if (isset($docType) && $docType == DocType::Departure) {
      // return 5;

      $documents = City::with(['children.products.stock.zone_position.zone.zone_type', 'children.products.parent_product.stock_parent', 'children.products.stock.zone_position.zone.warehouse', 'children' => function ($query) use ($docType) {
        $query->whereHas('products.document_detail.document', function ($query) use ($docType, $companyId) {
          $query->where('document_type', $docType)->where('company_id', $companyId);
        });
      }, 'children.products' => function ($query) use ($docType) {
        $query->whereHas('document_detail.document', function ($query) use ($docType, $companyId) {
          $query->where('document_type', $docType)->where('company_id', $companyId);
        });
      }, 'children.products.document_detail' => function ($query) use ($docType, $companyId) {
        $query->whereHas('document', function ($query) use ($docType) {
          $query->where('document_type', $docType)->where('company_id', $companyId);
        });
      }, 'children.products.document_detail.document'])
        // ->groupBy('pp.reference', 'pp.description', 'd.id','pp.id')
        ->whereHas('children.products.document_detail.document', function ($query) use ($docType, $companyId) {
          $query->where('document_type', $docType)->where('company_id', $companyId);
        })
        ->get();

      return $documents->toArray();
    }
    // Adjuntamos a la informacion del documento la informacion de cada detalle relacionado y por cada detalle su producto y conteo
    $documents = Document::with('detail.detailCount.child_count', 'detail.product', 'scheduleDocument.schedule.schedule_receipt', 'clientdocument')->where('company_id', $companyId);
    if (isset($receiptType)) {
      // return $receiptType;
      $documents = $documents->where('receipt_type_id', $receiptType);
    }
    if (empty($is_special)) {
      $documents = $documents->whereRaw('is_special is null');
    }
    $documents = $documents->get();

    $schedule = Schedule::where('id', $scheduleId)->first();

    $documentos = array();

    foreach ($documents as $value) {
      if ($value['id'] === $schedule['parent_schedule_id']) {
        $documentos = $value;
      }
    }

    return [$documentos->toArray()];
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
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    //
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
    $documents = Document::findOrFail($id);

    $documents->status = array_key_exists('status', $data) ? $data['status'] : $documents->status;
    $documents->is_partial = array_key_exists('is_partial', $data) ? $data['is_partial'] : $documents->is_partial;
    $documents->reason_code_id = array_key_exists('reason_code_id', $data) ? $data['reason_code_id'] : $documents->reason_code_id;

    $documents->has_error = array_key_exists('has_error', $data) ? $data['has_error'] : $documents->has_error;
    $documents->count_status = array_key_exists('count_status', $data) ? $data['count_status'] : $documents->count_status;

    $documents->save();

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
    //
  }

  public function getDocumentsByWarehouses(Request $request)
  {
    $data = $request->all();
    $companyId = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $receiveType = array_key_exists('receiveType', $data) ? $data['receiveType'] : NULL;
    $warehouse_origin = array_key_exists('warehouse_origin', $data) ? $data['warehouse_origin'] : NULL;
    $warehouse_destination = array_key_exists('warehouse_destination', $data) ? $data['warehouse_destination'] : NULL;
    // $companyId = $request->input('company_id');
    // $receiptType = $request->input('receipt_type');
    // $is_special = $request->input('is_special');
    // $companyId = $request->input('company_id');

    // $docType = $request->input('doc_type');

    // Adjuntamos a la informacion del documento la informacion de cada detalle relacionado y por cada detalle su producto y conteo
    $documents = Document::with('detail.detailCount.child_count', 'detail.product', 'scheduleDocument.schedule.schedule_receipt')->where('company_id', $companyId)->where('receipt_type_id', $receiveType)->where('warehouse_origin', $warehouse_origin)->where('warehouse_destination', $warehouse_destination)->get();


    // $documents = $documents->get();

    return $documents->toArray();
  }

  public function getDocumentsByWarehouse(Request $request)
  {
    $data = $request->all();
    $companyId = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $warehouse_destination = array_key_exists('warehouse_destination', $data) ? $data['warehouse_destination'] : NULL;


    // Adjuntamos a la informacion del documento la informacion de cada detalle relacionado y por cada detalle su producto y conteo
    $documents = Document::with('detail.detailCount.child_count', 'detail.product', 'scheduleDocument.schedule.schedule_receipt')->where('company_id', $companyId)->where('warehouse_destination', $warehouse_destination)->get();


    // $documents = $documents->get();

    return $documents->toArray();
  }

  public function updateDocumentDetailQuantyReceivedPallet(Request $request)
  {
    $data = $request->all();
    $companyId = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $information = array_key_exists('information', $data) ? $data['information'] : NULL;

    foreach ($information as $value) {
      foreach ($value['details'] as $value1) {
        $sum = $value1['quanty_received'] + $value1['is_additional'];
        $config = DocumentDetail::where('id', $value1['id'])->update(['quanty_received_pallet' => $sum]);
      }
    }

    // Adjuntamos a la informacion del documento la informacion de cada detalle relacionado y por cada detalle su producto y conteo


    // $documents = $documents->get();

    return $config;
  }

  public function updateDetailPallet(Request $request)
  {
    $data = $request->all();
    $companyId = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $id = array_key_exists('id', $data) ? $data['id'] : NULL;
    $quanty_received_pallet = array_key_exists('quanty_received_pallet', $data) ? $data['quanty_received_pallet'] : NULL;

    // foreach ($array as  $value) {

    $config = DocumentDetail::where('id', $id)->update(['quanty_received_pallet' => $quanty_received_pallet]);

    // }

    // Adjuntamos a la informacion del documento la informacion de cada detalle relacionado y por cada detalle su producto y conteo


    // $documents = $documents->get();

    return $config;
  }

  public function getAllDepartureDocument(Request $request)
  {
    $data = $request->all();
    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    $departure = DocType::Departure;

    $username = User::where('id', $data['session_user_id'])->first();

    $document = DB::table('wms_documents')
      // ->leftJoin('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
      ->leftJoin('wms_clients', 'wms_documents.client', '=', 'wms_clients.id')
      ->leftJoin('cities', 'wms_clients.city_id', '=', 'cities.id')
      // ->leftJoin('wms_ean_codes14', 'wms_documents.id', '=', 'wms_ean_codes14.document_id')
      ->groupBy('wms_documents.id')
      ->where('wms_documents.document_type', $departure)
      ->where('wms_documents.company_id', $companyId)
      ->where('wms_documents.status', 'packing')
      ->orwhere('wms_documents.status', 'transsition')
      ->orwhere('wms_documents.status', 'Por facturar SAYA')
      // ->where('wms_documents.count_status','!=', 1)
      ->select('wms_documents.number', 'wms_documents.external_number', 'wms_documents.observation', 'wms_documents.total_cost', 'total_benefit', 'wms_documents.client', 'wms_documents.status', 'wms_documents.count_status', 'wms_documents.id', 'wms_documents.min_date', 'wms_documents.city as name', 'wms_documents.lead_time as max_date', 'cities.dispatch_time', 'wms_clients.name as clientName', 'wms_documents.warehouse_origin', 'wms_documents.warehouse_destination', DB::raw("'$username->name' as responsible "))
      ->get();

    // $document = DB::table('wms_documents')
    //     ->leftJoin('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
    //     ->leftJoin('wms_clients', 'wms_documents.client', '=', 'wms_clients.id')
    //     ->leftJoin('cities', 'wms_clients.city_id', '=', 'cities.id')
    //     ->groupBy('wms_documents.id')
    //     ->where('wms_documents.document_type', $departure)
    //     ->where('wms_documents.company_id', $companyId)
    //     ->select('wms_documents.number', 'wms_documents.total_cost', 'total_benefit', 'wms_documents.client', 'wms_documents.status', 'wms_documents.id', 'wms_documents.min_date', 'cities.name', 'wms_documents.lead_time as max_date', 'cities.dispatch_time', 'wms_clients.name as clientName', DB::raw("'$username->name' as responsible, SUM(wms_document_details.unit) as totalUnit"))
    //     ->get();


    //$document['name_user'] = $username->name;

    return $document;
  }

  public function getDocumentDispatch(Request $request)
  {

    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    $dispatch = $settingsObj->get('position_dispatch');
    // return $dispatch;
    $data = $request->all();
    //  return $data['number'];
    // $document = DB::table('wms_eancodes14_packing')
    // // ->join('wms_stock', 'wms_eancodes14_packing.code128_id', '=', 'wms_stock.code128_id')
    // // ->join('wms_products', 'wms_stock.product_id', '=', 'wms_products.id')
    // // ->join('wms_zone_positions', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
    // // ->join('wms_zones', 'wms_zone_positions.zone_id', '=', 'wms_zones.id')
    // // ->join('wms_enlist_products', 'wms_products.id', '=', 'wms_enlist_products.product_id')
    // // ->join('wms_documents', 'wms_enlist_products.document_id', '=', 'wms_documents.id')
    // // ->join('wms_warehouses', 'wms_zones.warehouse_id', '=', 'wms_warehouses.id')
    // // ->join('wms_eancodes14_packing', 'wms_stock.code128_id', '=', 'wms_eancodes14_packing.code128_id')
    // // ->where('wms_zones.name',$dispatch)
    // ->where('wms_eancodes14_packing.document_id',$id)
    // // ->groupBy('wms_stock.document_detail_id')
    // ->select('wms_eancodes14_packing.document_id','wms_eancodes14_packing.code128_id','wms_eancodes14_packing.quanty_14')->get();
    // $document = DB::table('wms_stock')
    //     ->leftJoin('wms_products', 'wms_stock.product_id', '=', 'wms_products.id')
    //     ->leftJoin('wms_zone_positions', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
    //     ->leftJoin('wms_zones', 'wms_zone_positions.zone_id', '=', 'wms_zones.id')
    //     ->leftJoin('wms_enlist_products', 'wms_products.id', '=', 'wms_enlist_products.product_id')
    //     ->leftJoin('wms_documents', 'wms_enlist_products.document_id', '=', 'wms_documents.id')
    //     ->leftJoin('wms_warehouses', 'wms_zones.warehouse_id', '=', 'wms_warehouses.id')
    //     ->leftJoin('wms_eancodes14_packing', 'wms_stock.code128_id', '=', 'wms_eancodes14_packing.code128_id')
    //     ->where('wms_zones.name', $dispatch)
    //     ->whereIn('wms_eancodes14_packing.document_id', $data['number'])
    //     ->groupBy('wms_eancodes14_packing.id')
    //     ->select('wms_stock.quanty', 'wms_products.reference', 'wms_zone_positions.code', 'wms_zones.name', 'wms_documents.number', 'wms_products.description', 'wms_enlist_products.unit', 'wms_enlist_products.status', 'wms_warehouses.name as warehouse_origin', 'wms_products.id as product_id', 'wms_zone_positions.id as zone_position_id', 'wms_stock.code_ean14', 'wms_warehouses.id as warehouse_id', 'wms_stock.document_detail_id', 'wms_eancodes14_packing.quanty_14', 'wms_stock.code128_id', 'wms_eancodes14_packing.document_id')->get();

    $document = DB::table('wms_documents')
      ->leftJoin('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
      ->leftJoin('wms_stock', function ($query) {
        $query->on('wms_document_details.id', '=',  'wms_stock.document_detail_id');
        $query->on('wms_document_details.product_id', '=', 'wms_stock.product_id');
      })
      ->leftJoin('wms_products', 'wms_document_details.product_id', '=', 'wms_products.id')
      ->leftJoin('wms_zone_positions', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
      ->leftJoin('wms_zones', 'wms_zone_positions.zone_id', '=', 'wms_zones.id')
      ->leftJoin("wms_ean_codes14 as e14", "wms_stock.code_ean14", "=", "e14.id")
      ->leftJoin('wms_warehouses', 'wms_zones.warehouse_id', '=', 'wms_warehouses.id')
      ->selectRaw("
            e14.code14 as code_ean14,
            SUM(wms_stock.quanty) as quanty,
            wms_products.reference,
            wms_zone_positions.code,
            wms_zones.name,
            wms_products.ean,
            SUM(wms_stock.quanty) as unit,
            wms_warehouses.name as warehouse_origin,
            wms_products.id as product_id,
            wms_zone_positions.id as zone_position_id,
            wms_warehouses.id as warehouse_id,
            wms_stock.document_detail_id,
            SUM(wms_stock.quanty) as quanty_14,
            wms_stock.code128_id,
            wms_documents.id as document_id,
            wms_documents.count_status,
            wms_documents.status,
            e14.weight
        ")
      ->where('wms_zones.name', $dispatch)
      ->whereIn('wms_documents.id', $data['number'])
      ->groupBy("e14.id")
      ->get();

    // "product_id"=>$value->product_id,
    //           "zone_position_id"=>$value->zone_position_id,
    //           "code128_id"=>$value->code128_id,
    //           "code14_id"=>$value->code14_id,
    //           "quanty"=>$value->quanty,
    //           "action"=>"income",
    //           "concept"=>"relocate",
    //           "warehouse_id"=>$value->warehouse_id,
    //           "user_id"=>""


    return $document;
  }

  public function createSchedulesMaterials(Request $request)
  {
    $data = $request->all();
    $settingsObj = new Settings();
    $session_user_id = array_key_exists('session_user_id', $data) ? $data['session_user_id'] : NULL;
    $dato = array_key_exists('dato', $data) ? $data['dato'] : NULL;
    $schedule_id = array_key_exists('schedule_id', $data) ? $data['schedule_id'] : NULL;

    $parent = Schedule::where('id', $schedule_id)->first();
    $usuario = User::with('person')->where('id', $session_user_id)->first();
    if ($dato === 'condition') {

      $chargeUserName = $settingsObj->get('stock_group');

      $user = User::whereHas('person.group', function ($q) use ($chargeUserName) {
        $q->where('name', $chargeUserName);
      })->whereHas('person', function ($q) use ($usuario) {
        $q->where('warehouse_id', $usuario->person->warehouse_id);
      })->first();

      if (empty($user)) {
        return $this->response->error('user_not_found', 404);
      }

      $taskSchedulesW = [
        'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
        'name' => 'Realizar picking empaque :',
        'schedule_type' => ScheduleType::EnlistPlan,
        'schedule_action' => ScheduleAction::Picking_empaque,
        'status' => ScheduleStatus::Process,
        'user_id' => $usuario->id,
        'parent_schedule_id' => $parent->parent_schedule_id
      ];
      $scheduleW = Schedule::create($taskSchedulesW);
      $taskSchedulesW['schedule_id'] = $scheduleW->id;
      ScheduleEnlist::create($taskSchedulesW);

      $taskSchedules = [
        'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
        'name' => 'Realizar empaque zonas de picking :',
        'schedule_type' => ScheduleType::EnlistPlan,
        'schedule_action' => ScheduleAction::Packing,
        'status' => ScheduleStatus::Process,
        'user_id' => $usuario->id,
        'parent_schedule_id' => $parent->parent_schedule_id
      ];
      $schedule = Schedule::create($taskSchedules);
      $taskSchedules['schedule_id'] = $schedule->id;
      ScheduleEnlist::create($taskSchedules);
    } elseif ($dato === 'semi_condition') {
      $chargeUserName = $settingsObj->get('stock_group');

      $user = User::whereHas('person.group', function ($q) use ($chargeUserName) {
        $q->where('name', $chargeUserName);
      })->whereHas('person', function ($q) use ($usuario) {
        $q->where('warehouse_id', $usuario->person->warehouse_id);
      })->first();

      if (empty($user)) {
        return $this->response->error('user_not_found', 404);
      }

      $taskSchedulesW = [
        'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
        'name' => 'Requerimiento de materiales:',
        // 'schedule_type' => ScheduleType::Pallet,
        // 'schedule_action' => ScheduleAction::Generate,
        'status' => ScheduleStatus::Process,
        'user_id' => $usuario->id
      ];
      $scheduleW = Schedule::create($taskSchedulesW);

      $taskSchedules = [
        'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
        'name' => 'Acondicionamiento:',
        // 'schedule_type' => ScheduleType::Pallet,
        // 'schedule_action' => ScheduleAction::Generate,
        'status' => ScheduleStatus::Process,
        'user_id' => $usuario->id
      ];
      $schedule = Schedule::create($taskSchedules);
    } elseif ($dato === 'without_conditioning') {

      $chargeUserName = $settingsObj->get('stock_group');

      $user = User::whereHas('person.group', function ($q) use ($chargeUserName) {
        $q->where('name', $chargeUserName);
      })->whereHas('person', function ($q) use ($usuario) {
        $q->where('warehouse_id', $usuario->person->warehouse_id);
      })->first();

      if (empty($user)) {
        return $this->response->error('user_not_found', 404);
      }

      // $taskSchedulesW = [
      // 'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0].' '.explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      // 'name' => 'Requerimiento de materiales:',
      // // 'schedule_type' => ScheduleType::Pallet,
      // // 'schedule_action' => ScheduleAction::Generate,
      // 'status' => ScheduleStatus::Process,
      // 'user_id' => $usuario->id
      // ];
      // $scheduleW = Schedule::create($taskSchedulesW);

      $taskSchedules = [
        'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
        'name' => 'Acondicionamiento:',
        // 'schedule_type' => ScheduleType::Pallet,
        // 'schedule_action' => ScheduleAction::Generate,
        'status' => ScheduleStatus::Process,
        'user_id' => $usuario->id
      ];
      $schedule = Schedule::create($taskSchedules);
    }


    // return $document;


  }

  public function getDocumentPlan(Request $request)
  {


    $data = $request->all();
    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    // $concept_position = $settingsObj->get('concept_position');

    // return $companyId;
    $documents = DocumentDetail::with('client.city', 'document', 'product.stock.zone_position.zone.warehouse', 'product.stock.ean14', 'product.stock.zone_position.zone.zone_type', 'product.stock.zone_position.concept')
      ->whereHas('document', function ($query) use ($companyId) {
        $query->where('company_id', $companyId);
      })->whereIn('document_id', $data)->get();

    return $documents->toArray();
  }

  public function getDocumentBatch(Request $request)
  {
    $data = $request->all();

    //TODO:: poner settings
    $raw = "(SELECT SUM(wms_stock.quanty) FROM wms_stock, wms_zone_positions, wms_zones, wms_zone_types, wms_products as hijo, wms_products as papa, wms_document_details as dod " .
      "WHERE wms_stock.zone_position_id = wms_zone_positions.id AND " .
      "wms_zone_positions.zone_id = wms_zones.id  AND " .
      "wms_zones.zone_type_id = wms_zone_types.id AND wms_zone_types.name = 'Estantería' AND " .
      "hijo.parent_product_id = papa.id AND " .
      "wms_stock.product_id = dod.product_id AND " .
      "dod.product_id = p.id AND " .
      "wms_stock.product_id = p.id GROUP BY wms_stock.product_id) as picking";

    $raw = '(SELECT sum(wms_stock.quanty) ' .
      'FROM ' .
      'wms_document_details, ' .
      'wms_products, ' .
      'wms_stock ' .
      'WHERE ' .
      'wms_document_details.product_id = wms_products.id ' .
      'AND wms_stock.product_id = wms_products.id ' .
      'AND wms_document_details.document_id = d.id ' .
      'AND wms_products.parent_product_id = pp.id) as nomame';


    $products = DB::table('wms_documents as d')
      ->join('wms_document_details as dd', 'd.id', '=', 'dd.document_id')
      ->join('wms_products as p', 'dd.product_id', '=', 'p.id')
      ->join('wms_products as pp', 'p.parent_product_id', '=', 'pp.id')
      ->groupBy('pp.reference', 'pp.description', 'd.id', 'pp.id')
      ->whereIn('d.id', $data)
      ->select('d.id as did', 'pp.id as pid', 'pp.reference', 'pp.description', DB::raw('SUM(dd.unit) as unit'), DB::raw($raw))
      ->get();

    // $users = DB::table('users')
    //       ->join('contacts', 'users.id', '=', 'contacts.user_id')
    //       ->join('orders', 'users.id', '=', 'orders.user_id')
    //       ->select('users.*', 'contacts.phone', 'orders.price')
    //       ->get();
    return $products;
  }

  public function getAllDepartureDocumentClients(Request $request)
  {
    $data = $request->all();
    // $settingsObj = new Settings();
    // $departure = DocType::Departure;

    $datos = City::with('clients.products.document_detail')->get();


    return $datos;
  }

  public function save(Request $request)
  {
    $data = $request->all();
    $datos = $data['datos'];
    // return $datos;

    $quanty = 0;

    // $obj=[];
    // $delete = EnlistProducts::where('city_id',0)->delete();
    // foreach ($datos['clients'] as  $value) {
    $productos = [];
    foreach ($datos['detail'] as &$value3) {
      $bodega = $value3['document_id'];
      $productos[] = [
        "product_id" => $value3['product_id'],
        "cartons" => $value3['cartons']
      ];
      $value3['cantidad_stock'] = 0;
      foreach ($value3['product']['stock'] as $inventario) {

        // return $inventario;
        $value3['cantidad_stock'] += $inventario['quanty_14'];
      }

      if ($value3['cantidad_stock'] > $value3['cartons']) {
        $quanty = $value3['cartons'];
      } else {
        $quanty = $value3['cantidad_stock'];
      }


      $obj = [
        'city_id' => $datos['city_id'],
        'product_id' => $value3['product']['id'],
        'quanty' => $quanty,
        'document_id' => $bodega,
        'code_ean14' => $value3['code_ean14'],
        'unit' => $value3['unit']
      ];
      $createData = EnlistProducts::create($obj);
    }

    // return $datos['detail'];

    // }

    return $createData;
  }

  public function saveMulti(Request $request)
  {
    $data = $request->all();
    $datos = $data['datos'];

    $quanty = 0;
    // $obj=[];

    foreach ($datos as $key => $value5) {
      foreach ($value5['children'] as $value) {

        foreach ($value['products'] as $value1) {

          foreach ($value1['document_detail'] as $value3) {
            $bodega = $value3['document_id'];
          }

          foreach ($value1['stock'] as $value2) {

            if ($value1['id']) {
              $quanty += $value2['quanty'];
            } else {
              $quanty = $value2['quanty'];
            }
          }
          $obj = [
            'city_id' => $value['city_id'],
            'product_id' => $value1['id'],
            'quanty' => $quanty,
            'document_id' => $bodega
          ];
          $createData = EnlistProducts::create($obj);
        }
      }
    }


    return $createData;
  }

  public function saveGeneral(Request $request)
  {
    $data = $request->all();
    $datos = $data['datos'];

    $quanty = 0;
    // $obj=[];
    $delete = EnlistProducts::where('city_id', '!=', 0)->delete();
    foreach ($datos['products'] as $value) {
      $bodega = $value['document_id'];

      foreach ($value['product']['stock'] as $value2) {

        // if ($value['product_id']) {
        //   $quanty += $value2['quanty'];
        // }
        // else {
        //   $quanty = $value2['quanty'];
        // }

      }
      $obj = [
        // 'city_id'=>$value['city_id'],
        'product_id' => $value['product']['id'],
        // 'quanty'=>$quanty,
        'document_id' => $bodega,
        'order_quanty' => $value['unit'],
        'parent_product_id' => $datos['id'],
        'condition' => $datos['conditioned'],
        'semi_condition' => 0,
        'without_conditioning' => $datos['sin_acondicionadas']
      ];
      $createData = EnlistProducts::create($obj);
    }

    return $createData;
  }

  public function saveGeneralG(Request $request)
  {
    $data = $request->all();
    $datos = $data['datos'];

    $quanty = 0;
    // $obj=[];
    foreach ($datos as $value2) {
      foreach ($value2['products'] as $value) {
        $bodega = $value['document_id'];

        $obj = [
          // 'city_id'=>$value['city_id'],
          'product_id' => $value['product']['id'],
          // 'quanty'=>$quanty,
          'document_id' => $bodega,
          'order_quanty' => $value['unit'],
          'parent_product_id' => $value2['id']
        ];
        $createData = EnlistProducts::create($obj);
      }
    }


    return $createData;
  }

  public function delete(Request $request)
  {
    $data = $request->all();
    $datos = $data['datos'];

    $consult = EnlistProducts::where('city_id', $datos)->delete();


    return $consult;
  }

  public function createBox(Request $request)
  {
    $data = $request->all();
    $arreglo = $data['arreglo'];
    // $picking = $data['picking'];
    $picking = array_key_exists('picking', $data) ? $data['picking'] : NULL;
    $document_id = 0;

    foreach ($arreglo as $value) {

      $caja = str_random(7);
      $validate = SchedulePacking::where('product_id', $value['product_id'])->first();
      if (!$picking) {
        $delete = Stock::where('product_id', $value['product_id'])->where('code14_id', $value['code14_id'])->first();
      } else {
        $position = ZonePosition::where('code', $value['code'])->first();
        $delete = Stock::where('product_id', $value['product_id'])->where('zone_position_id', $position->id)->first();
      }
      if ($validate) {
        $validate->increment('quanty', 1);
        if ($delete) {
          $delete->decrement('quanty', 1);
        }
      } else {

        if (!$picking) {
          foreach ($value['ean14']['code14_packing'] as $valor) {
            $document_id = $valor['document_id'];
          }
        } else {
          $document_id = $value['document_id'];
        }

        $objeto = [
          "product_id" => $value['product_id'],
          "quanty" => 1,
          "document_id" => $document_id,
          "code14_id" => $caja,

        ];

        if ($delete) {
          $delete->decrement('quanty', 1);
        }
      }
    }
    if (!$validate) {
      $consult = SchedulePacking::create($objeto);
    }
    return $data;
  }

  public function getDataPlanByPosition(Request $request)
  {
    $data = $request->all();
    $id = $data['code'];
    $schedule_id = $data['schedule_id'];

    $position = ZonePosition::where('code', $id)->first();

    if ($position) {
      $consult = Stock::with('product.document_detail', 'ean14.code14_packing.document', 'ean13')->where('zone_position_id', $position->id)->get();

      $validate = SchedulePositionPacking::where('zone_position_id', $position->id)->first();
      $real_quanty = SchedulePositionPacking::where('zone_position_id', $position->id)->get();

      if (!empty($real_quanty)) {
        foreach ($real_quanty as $value_real) {
          $datos = SchedulePacking::where('product_id', $value_real->product_id)->first();

          foreach ($consult as $value2) {
            // return $this->response->error('not_working_position'.$value_real->code14_id.'es igual'.$value2->code14_id.'position'.$position->id, 404);

            if ($value_real->code14_id === $value2->code14_id) {
              $value2['real_quanty'] = $value_real->real_quanty;
              $value2['packing_unit'] = $datos['quanty'];
            }
          }
        }
      }

      if (!$validate) {
        $real_quanty_acum = 0;
        foreach ($consult as $value) {

          $real_quanty_acum += $value->quanty;
          $obj = [
            "zone_position_id" => "$position->id",
            "schedule_id" => $schedule_id,
            "real_quanty" => $value->quanty,
            "product_id" => $value->product_id,
            "code14_id" => $value->code14_id
          ];
          SchedulePositionPacking::create($obj);
        }

        $val = ProgressTask::where('schedule_id', $schedule_id)->first();
        if (!$val) {
          $cargar = [
            "schedule_id" => $schedule_id,
            "real_quanty" => $real_quanty_acum
          ];
          ProgressTask::create($cargar);
        }
      }
    } else {
      return $this->response->error('not_working_position', 404);
    }


    return ['stock' => $consult, 'real' => $real_quanty, 'zone_position_id' => $position->id];
  }

  public function getDataPlanByPositionByproduct(Request $request)
  {

    $data = $request->all();
    $ids = $data['ids'];

    $consult = SchedulePacking::with('product')->whereIn('product_id', $ids)->get();


    return $consult->toArray();
  }

  public function generateCode14(Request $request)
  {

    $data = $request->all();

    $packaging_type = $data['packaging_type'];

    $detailSelected = $data['detailSelected'];
    $schedule_id = $data['schedule_id'];

    $code = $data['code'];
    $document_id = $data['document_id'];

    $zonePosition = ZonePosition::where('code', $code)->first();

    $zone_position_id = $zonePosition->id;

    //Consultamos la estrucutra para el tipo de embalaje
    $structurecodes = Codes::GetStructureCode($packaging_type);

    $html = '';
    //Esto fala cogerlo dinámicamente
    DB::transaction(function () use ($detailSelected, &$html, $structurecodes, $data, $code, $zone_position_id, $schedule_id, $document_id) {

      // Contador para identificar los grupos de 3 codigos para hacer el salto de pagina
      $breackCount = 0;
      $detailCount = 0;
      $detailLen = count($detailSelected);

      //Recorremos los detallesOrden a los cuales se les quiere generar código
      //  foreach ($detailSelected as  $valueDetail) {
      $detailCount++;
      $numcartons = 1;
      $quantyproducts = $data['quanty'];


      //  $code13 =  $data['code'];
      // $reference =  $data['reference'];
      //$document_detail_id =  $data['id'];

      //  $product_id =  $data['product_id'];
      $container_code = $data['container_code'];
      // $description =  $data['description'];
      $container_id = $data['container_id'];

      // $products = $data['products'];

      //  $reason_code = !empty($data['reason_code'])?$data['reason_code']:NULL;

      // Determina si existe o no conteo
      //  $count = !empty($data['count'])?$data['count']:false;

      // Si existe conteo se crean los codigos apoarti de este arreglo
      // Sino se usa la cantidad de cartons descrita en cada detalle del documento
      $damaged = !empty($data['damaged']) ? $data['damaged'] : false;
      $quarantine = !empty($data['quarantine']) ? $data['quarantine'] : false;
      //La cantidad de códigos a generar, se define según el número de cartones por cada detalle
      //  for ($i=0; $i < $numcartons ; $i++) {

      $breackCount++;
      $newstructurecode14 = '7704121' . $container_code;
      //Recorremos la estructura y generamos la estrucutra de los códigos IA
      foreach ($structurecodes as $structure) {
        $ia_code = $structure['ia_code'];
        $code_ia = $ia_code['code_ia'];
        //Validamos si el código IA debe tomar datos de alguna tabla
        if ((!empty($ia_code['table']) && empty($ia_code['field'])) || (!empty($ia_code['field']) && empty($ia_code['table']))) {
          return $this->response->error('No se pueden generar los códigos, porque el código GTIN' . $code_ia . 'está mal configurado', 404);
        } else if (!empty($ia_code['table']) && !empty($ia_code['field'])) {
          $table = $ia_code['table'];
          $field = $ia_code['field'];

          $results = DB::select(DB::raw('SELECT ' . $field . ' FROM ' . $table . ' WHERE id = ' . $document_detail_id . ''));
          if (is_null($results)) {
            return $this->response->error('No se pueden generar los códigos, porque el código GTIN' . $code_ia . 'está mal configurado', 404);
          } else {
            $array = json_decode(json_encode($results[0]), True);

            $newstructurecode14 .= '(' . $code_ia . ')' . $array['number'];
          }
        } else {
          $newstructurecode14 .= '(' . $ia_code['code_ia'] . ')';
        }
      }


      $new14 = [
        'code14' => $newstructurecode14,
        // 'code13'=> $code13,
        'document_detail_id' => null,
        'quanty' => $quantyproducts,
        'container_id' => $container_id,
        //  'reason_code_id'=>$reason_code,
        'stored' => 1,
        'damaged' => $damaged,
        'quarantine' => $quarantine
      ];

      $savedcode14 = EanCode14::create($new14);
      $newsavedcode14 = $savedcode14->code14 . $savedcode14->id;
      $savedcode14->code14 = $newsavedcode14;
      $savedcode14->save();
      $savedcode14->detail()->createMany($detailSelected);

      $id14 = $savedcode14->id;

      $findStock = EanCode14Detail::where('ean_code14_id', $id14)->get();
      //$findStock = EanCode14::with('detail')->where('id',$id14)->get()->toArray();

      $datatransition = [];
      if (!empty($findStock)) {

        foreach ($findStock as $value) {

          //buscar posición a descontar
          $stockPosition = Stock::where('zone_position_id', $zone_position_id)
            ->where('product_id', $value->product_id)
            ->first();

          // Inserta los registros del stock a la tabla de transicion
          $objTransition = [
            'product_id' => $value->product_id,
            'zone_position_id' => $zone_position_id,
            // 'code128_id'=>$value['code128_id'],
            'code14_id' => $value->ean_code14_id,
            'quanty' => $value->quanty,
            // TODO: Agregar enum a la action
            'action' => 'output',
            'concept' => 'dispatch',
            //  'warehouse_id'=>$value['zone_position']['zone']['warehouse_id'],
            'user_id' => $data['session_user_id'],
          ];

          $StockTransition = StockTransition::create($objTransition);

          $progress = ProgressTask::where('schedule_id', $schedule_id)->first();
          if ($progress) {
            $progress->increment('quanty', $value->quanty);
          }

          $packing = [
            "code14_id" => $value->ean_code14_id,
            "document_id" => $document_id
          ];
          Eancodes14Packing::create($packing);

          //
          // $stockPosition->decrement('quanty',$value->quanty);
          //
          // if($stockPosition->quanty<=0)
          // {
          //
          //    $stockPosition->delete();
          //
          // }

        }
      }

      $html .= Codes::getEan14Html($savedcode14->id);

      $html .= "<div style='page-break-after: always;' class='breack-page'></div>";

      $delete = SchedulePacking::truncate();

      // $delete->delete();
      //  }
      //  }
      //  }
    });

    if (empty($html)) {
      return $this->response->error('No se generó ningun codigo', 404);
    }

    return $html;
  }

  public function deleteG(Request $request)
  {
    $data = $request->all();
    $datos = $data['datos'];

    $consult = EnlistProducts::where('parent_product_id', $datos)->delete();


    return $consult;
  }

  public function saveWarehouseG(Request $request)
  {
    $data = $request->all();
    // $datos = $data['datos'];
    $datos = array_key_exists('dato', $data) ? $data['dato'] : null;
    $condition = array_key_exists('condition', $data) ? $data['condition'] : null;
    $id = array_key_exists('id', $data) ? $data['id'] : null;
    $status = array_key_exists('status', $data) ? $data['status'] : null;

    if ($condition === 'acond') {
      $config = EnlistProducts::where('parent_product_id', $id)->update(['condition' => $datos, 'status' => $status]);
    } elseif ($condition === 'semi') {
      $config = EnlistProducts::where('parent_product_id', $id)->update(['semi_condition' => $datos, 'status' => $status]);
    } elseif ($condition === 'bod_con') {
      $config = EnlistProducts::where('parent_product_id', $id)->update(['condition_warehouse' => $datos, 'status' => $status]);
    } elseif ($condition === 'bod_semi') {
      $config = EnlistProducts::where('parent_product_id', $id)->update(['semi_condition_warehouse' => $datos, 'status' => $status]);
    } elseif ($condition === 'bod_sin') {
      $config = EnlistProducts::where('parent_product_id', $id)->update(['without_conditioning_warehouse' => $datos, 'status' => $status]);
    } else {
      $config = EnlistProducts::where('parent_product_id', $id)->update(['without_conditioning' => $datos, 'status' => $status]);
    }

    return $config;
  }

  public function saveInicial(Request $request)
  {
    $data = $request->all();

    foreach ($data as $value) {
      $config = EnlistProducts::where('parent_product_id', $value['id'])->update(['condition' => $value['unidades_acon'], 'semi_condition' => $value['unidades_semi_suggestionG'], 'without_conditioning' => $value['unidades_sin_suggestion']]);
    }


    return $config;
  }

  public function saveWarehouseD(Request $request)
  {
    $data = $request->all();
    // $datos = $data['datos'];
    $datos = array_key_exists('dato', $data) ? $data['dato'] : null;
    $status = array_key_exists('status', $data) ? $data['status'] : null;
    $id = array_key_exists('id', $data) ? $data['id'] : null;
    $product_id = array_key_exists('product_id', $data) ? $data['product_id'] : null;

    if ($status === 'acond') {
      $config = EnlistProducts::where('city_id', $id)->where('product_id', $product_id)->update(['condition' => $datos]);
    } elseif ($status === 'semi') {
      $config = EnlistProducts::where('city_id', $id)->where('product_id', $product_id)->update(['semi_condition' => $datos]);
    } elseif ($status === 'bod_con') {
      $config = EnlistProducts::where('city_id', $id)->where('product_id', $product_id)->update(['condition_warehouse' => $datos]);
    } elseif ($status === 'bod_semi') {
      $config = EnlistProducts::where('city_id', $id)->where('product_id', $product_id)->update(['semi_condition_warehouse' => $datos]);
    } elseif ($status === 'bod_sin') {
      $config = EnlistProducts::where('city_id', $id)->where('product_id', $product_id)->update(['without_conditioning_warehouse' => $datos]);
    } else {
      $config = EnlistProducts::where('city_id', $id)->where('product_id', $product_id)->update(['without_conditioning' => $datos]);
    }

    return $config;
  }

  public function getEnlistByScheduleId($id)
  {

    // return $id;
    // $consult = EnlistProducts::where('schedule_id',$id)->get();

    // $consult = Product::with('enlist_product.warehouse','enlist_product.condition_warehouse','enlist_product.semi_condition_warehouse','enlist_product.without_conditioning_warehouse','enlist_product.document')
    // ->whereHas('enlist_product', function ($q) use ($id)
    // {
    //   $q->where('schedule_id',$id);
    // })
    // ->get();

    $consult = EnlistProducts::with('product', 'product_ean14', 'document')
      ->where('schedule_id', $id)->get();


    return $consult->toArray();
  }

  public function getWarehouseByScheduleId(Request $request)
  {

    $data = $request->all();
    $id = array_key_exists('id', $data) ? $data['id'] : NULL;
    $session_user_id = array_key_exists('session_user_id', $data) ? $data['session_user_id'] : NULL;

    $consult = User::with('person')->where('id', $session_user_id)
      ->first();


    return $consult->toArray();
  }

  public function getEnlistProducts(Request $request)
  {
    $data = $request->all();

    $consult = EnlistProducts::with('city', 'product.stock.zone_position.zone.warehouse', 'product.document_detail.client', 'product.stock.product', 'product.stock.zone_position.zone.zone_type')
      ->whereHas('product.document_detail', function ($q) use ($data) {
        $q->whereIn('document_id', $data);
      })
      ->where('city_id', '!=', '')
      ->get();


    return $consult->toArray();
  }

  public function generateTask(Request $request)
  {
    $data = $request->all();
    $datos = $data['data'];
    // $document_id = $data['document_id'];
    $consu = EnlistProducts::get();

    foreach ($consu as $value) {

      if ($value['condition'] === 0 || $value['semi_condition'] === 0 || $value['without_conditioning'] === 0 || $value['condition_warehouse'] === 0 || $value['semi_condition_warehouse'] === 0 || $value['without_conditioning_warehouse'] === 0) {
        return $this->response->error('alert_without_plan', 404);
      }
    }


    $settingsObj = new Settings();
    $chargeUserName = $settingsObj->get('leader_charge');
    $axu = 0;
    foreach ($datos as $value) {

      $user = User::whereHas('person.group', function ($q) use ($chargeUserName) {
        $q->where('name', $chargeUserName);
      })->whereHas('person', function ($q) use ($value) {
        $q->where('warehouse_id', $value['warehouse_id']);
      })->first();

      if (empty($user)) {
        return $this->response->error('user_not_found', 404);
      }
      if (!$axu || $axu !== $value['warehouse_id']) {
      }

      // $consult = EnlistProducts::where('product_id',$value['product_id'])->update(['destiny_ware' => $value['warehouse_id']]);

      // $consultD = EnlistProducts::where('document_id',null)->update(['document_id' => $document_id]);

    }
    $taskSchedules = [
      'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'name' => 'Gestionar planeación:',
      'schedule_type' => ScheduleType::EnlistPlan,
      'schedule_action' => ScheduleAction::Enlist,
      'status' => ScheduleStatus::Process,
      'user_id' => $user->id
    ];

    $schedule = Schedule::create($taskSchedules);
    $consultS = EnlistProducts::where('schedule_id', null)->update(['schedule_id' => $schedule->id]);
    $aux = $value['warehouse_id'];
    // return $datos;
    return $consultS;
  }

  public function generateTaskG(Request $request)
  {
    $data = $request->all();
    // $warehouse_id = $data['warehouse_id'];
    $parent_id = $data['parent_id'];
    // return $parent_id;

    // $parent_id = $data['parent_id'];
    $documents = $data['documents'];
    // return $data['session_user_id'];

    $username = User::with('person')->where('id', $data['session_user_id'])->where('company_id', $data['company_id'])->first();
    // return $username;
    $warehouse_id = $username->person->warehouse_id;

    $settingsObj = new Settings($data['company_id']);
    $chargeUserName = $settingsObj->get('leader_charge');

    $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
      $q->where('name', $chargeUserName);
    })->whereHas('person', function ($q) use ($warehouse_id) {
      $q->where('warehouse_id', $warehouse_id);
    })->first();
    // return $user;

    if (empty($user)) {
      return $this->response->error('user_not_found', 404);
    }

    $search = Document::whereIn('id', $documents)->get();
    $update = Document::whereIn('id', $documents)->update(['status' => 'Planeado']);
    $concat = "";
    foreach ($search as $value) {
      $concat .= $value['client'] . ':' . $value['number'] . ' , ';
    }

    $taskSchedules = [
      'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'name' => 'Gestionar planeación:' . ' ' . $concat,
      'schedule_type' => ScheduleType::EnlistPlan,
      'schedule_action' => ScheduleAction::Enlist,
      'status' => ScheduleStatus::Process,
      'user_id' => $user->id,
      'company_id' => $data['company_id']
    ];

    $schedule = Schedule::create($taskSchedules);

    foreach ($parent_id as $key => $value) {
      // foreach ($value['clients'] as  $value1) {
      foreach ($value['detail'] as $value2) {

        $consultS = EnlistProducts::where('product_id', $value2['product']['id'])->where('schedule_id', null)->update(['schedule_id' => $schedule->id]);
      }
      // }
    }


    // return $datos;
    return $consultS;
  }

  public function saveG(Request $request)
  {
    $data = $request->all();
    $datos = $data['data'];
    foreach ($datos as $key => $value) {
      foreach ($value['clients'] as $value1) {
        foreach ($value1['detail'] as $value2) {

          $consultP = EnlistProducts::where('product_id', $value2['product']['id'])->update(['without_conditioning' => $value2['product']['sin_acondicionar']]);

          $consultC = EnlistProducts::where('product_id', $value2['product']['id'])->update(['condition' => $value2['product']['conditioned']]);
          // foreach ($value2['product']['stock'] as  $value3) {
          //
          // }
        }
      }
    }

    // return $datos;
    return $consultP;
  }

  public function saveProgress(Request $request)
  {
    $data = $request->all();
    $real_quanty = $data['real_quanty'];
    $schedule_id = $data['schedule_id'];

    $validate = ProgressTask::where('schedule_id', $schedule_id)->first();
    if (!$validate) {
      $objeto = [
        "schedule_id" => $schedule_id,
        "real_quanty" => $real_quanty
      ];

      ProgressTask::create($objeto);
    }


    // return $datos;
    // return $consultP;

  }

  public function validateGeneralPed(Request $request)
  {
    $data = $request->all();
    $datos = $data['data'];
    $warehouse_id = $data['warehouse_id'];
    $settingsObj = new Settings();
    // $documents = $data['documents'];

    $warehouse_id_acond = 0;
    $warehouse_id_sin = 0;

    $delete = EnlistProducts::where('schedule_id', null)->delete();
    foreach ($datos as $key => $value) {
      foreach ($value['clients'] as $value1) {
        foreach ($value1['detail'] as $value2) {

          if ($value2['product']['conditioned']) {
            $warehouse_id_acond = $warehouse_id;
          } else {
            $warehouse_id_acond = 0;
          }

          if ($value2['product']['sin_acondicionar']) {
            $warehouse_id_sin = $warehouse_id;
          } else {
            $warehouse_id_sin = 0;
          }

          $enlist = [
            'product_id' => $value2['product']['id'],
            'document_id' => $value2['document_id'],
            'condition' => $value2['product']['conditioned'],
            'without_conditioning' => $value2['product']['sin_acondicionar'],
            'parent_product_id' => $value2['product']['parent_product_id'],
            'without_conditioning_warehouse' => $warehouse_id_sin,
            'condition_warehouse' => $warehouse_id_acond,
            'order_quanty' => $value2['unit'],
            'status' => 'planed'

          ];

          $create = EnlistProducts::create($enlist);
        }
      }
    }

    $chargeUserName = $settingsObj->get('stock_group');

    $user = User::whereHas('person.group', function ($q) use ($chargeUserName) {
      $q->where('name', $chargeUserName);
    })->whereHas('person', function ($q) use ($warehouse_id) {
      $q->where('warehouse_id', $warehouse_id);
    })->first();

    if (empty($user)) {
      return $this->response->error('user_not_found', 404);
    }

    $taskSchedulesW = [
      'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'name' => 'Requerimiento de materiales:',
      // 'schedule_type' => ScheduleType::Pallet,
      // 'schedule_action' => ScheduleAction::Generate,
      'status' => ScheduleStatus::Process,
      'user_id' => $user->id
    ];
    $scheduleW = Schedule::create($taskSchedulesW);

    // return $datos;
    return $create;
  }

  public function getAllDocumentsDeparture($id)
  {


    $settingsObj = new Settings();
    $PickingName = $settingsObj->get('picking_type');

    $document = Document::with(['detail.product.stock' => function ($q) use ($PickingName) {
      $q->whereHas('zone_position.zone.zone_type', function ($q) use ($PickingName) {
        $q->where('name', $PickingName);
      });
    }])
      ->where('id', $id)
      ->get();


    return $document->toArray();
  }

  public function getScheduleById($id)
  {
    // return $id;

    $document = ScheduleEnlist::with('schedule', 'user.person', 'progress')->where('parent_schedule_id', $id)->get();

    return $document->toArray();
  }

  public function getEnlistWithoutScheduleId($id)
  {
    // return $id;
    $document = DB::table('wms_enlist_products')
      ->join('wms_products', 'wms_enlist_products.product_id', '=', 'wms_products.id')
      ->leftjoin('wms_stock', 'wms_products.id', '=', 'wms_stock.product_id')
      ->join('wms_documents', 'wms_enlist_products.document_id', '=', 'wms_documents.id')
      // ->join('wms_document_details', 'wms_enlist_products.document_id', '=', 'wms_document_details.document_id')
      ->where('wms_enlist_products.schedule_id', null)
      ->where('wms_enlist_products.document_id', $id)
      ->groupBy('wms_enlist_products.product_id')
      // ->distinct()
      ->select('wms_documents.number', 'wms_products.reference', 'wms_enlist_products.quanty as cartons', 'wms_enlist_products.quanty')
      ->get();
    // $document = EnlistProducts::with('product.stock','document','product_ean14.document_detail')->where('schedule_id',null)->where('document_id',$id)->get();


    return $document;
  }

  public function getSchedulesEnlistById($id)
  {

    // $document = EnlistProducts::with('product','document')->where('document_id',$id)->get();

    $document = DB::table('wms_enlist_products')
      ->where('document_id', $id)
      // ->groupBy('schedule_id')
      ->value('schedule_id');

    $settingsObj = new Settings();
    $position = $settingsObj->get('transit_receive');
    $WorkAreaConcept = $settingsObj->get('zone_concept_work_area');
    $WorkAreaConceptId = ZoneConcept::where('name', $WorkAreaConcept)->first()->id;


    $schedule = Schedule::with([
      'schedule_transition_parent.stock_transition', 'enlist_product.stock.ean14', 'enlist_product_filter.stock.ean14', 'enlist_product_filter.stock.zone_position', 'progress', 'schedule_enlist', 'schedule_transition_parent.enlist_product.ean_codes_14.ean14', 'schedule_transition_parent' => function ($q) {
        $q->whereHas('stock_transition', function ($q) {
          $q->where('code128_id', null);
        });
      }, 'enlist_product_filter.stock' => function ($query) use ($WorkAreaConceptId) {
        $query->whereHas('zone_position', function ($query) use ($WorkAreaConceptId) {
          $query->where('concept_id', $WorkAreaConceptId);
        });
      }, 'enlist_product.stock' => function ($query) use ($position) {
        $query->whereHas('zone_position.zone', function ($query) use ($position) {
          $query->where('name', $position);
        });
      }
    ])
      ->has('schedule_enlist')
      ->where('parent_schedule_id', $document)
      ->get();


    return $schedule->toArray();
  }

  public function getProductByIdSchedule($id, Request $request)
  {
    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    $position = $settingsObj->get('stock_zone');
    $config = EnlistProducts::with('product.stock.zone_position.zone.zone_type', 'product.stock.product_ean14s.pallet.ean128', 'product.stock.ean128', 'product.stock.ean13')->where('schedule_id', $id)->first();
    // return $config;

    $transition = ScheduleTransition::with('stock_transition')->where('schedule_id', $id)->get()->toArray();
    $arreglo_transition = [];
    foreach ($transition as $value) {
      $arreglo_transition[] = $value['stock_transition']['product_id'];
    }
    // return $position;
    if ($config) {
      $document = DocumentDetail::where('document_id', $config->document_id)->get();
      // return $document;
      $array = [];
      $datos_stock = [];
      foreach ($document as $value) {
        // $validacion = in_array($value['product_id'], $arreglo_transition);
        // return $validacion;
        $search_enlist = EnlistProducts::where('document_id', $value['document_id'])->where('product_id', $value['product_id'])->first();

        if ($search_enlist->picked_quanty < $value['quanty']) {
          $suggestion = Stock::with('document_detail', 'zone_position.zone.zone_type', 'product_ean14s.pallet.ean128', 'product', 'ean128')
            ->whereHas('document_detail', function ($query) use ($value) {
              $query->where('quanty', '>=', $value['quanty']);
            })
            ->whereHas('zone_position.zone', function ($query) use ($position) {
              $query->where('name', $position);
            })
            ->where('product_id', $value['product_id'])->first();
          // foreach ($suggestion as  $value_real) {

          // }
          // return $suggestion;
          // foreach ($transition as $value1) {
          if (!in_array($value['product_id'], $arreglo_transition, true)) {
            // return $value['product_id'];
            $datos_stock[] = [$value['product_id']];
            if ($suggestion) {
              $array[] = ["stock" => $suggestion];
            }
          }
        }
        // }
      }

      // return $datos_stock;

    }
    // return $array;
    return $array;
  }

  public function getProductByIdScheduleWare($id)
  {

    $config = EnlistProducts::with('product.stock.zone_position.zone.zone_type', 'without_conditioning_warehouse', 'product.stock.ean14.pallet.ean128', 'product.stock.ean128', 'product.stock.ean13')->where('schedule_id', $id)->where('without_conditioning', '!=', 0)->where('without_conditioning_warehouse', 0)->get();

    return $config->toArray();
  }

  public function getAllProducts(Request $request, $id)
  {
    $settingsObj = new Settings();
    $position = $settingsObj->get('transit_receive');
    $user = $request->input('session_user_id');

    $person = User::with('person')->where('id', $user)->first();


    if ($id === 'condition') {

      $config = Stock::with('product.enlist_product', 'ean14.code14_packing.document', 'ean13', 'zone_position.zone')
        ->whereHas('zone_position.zone', function ($query) use ($person, $position) {
          $query->where('warehouse_id', $person->person->warehouse_id)->where('name', $position);
        })
        ->whereHas('product.enlist_product', function ($query) {
          $query->where('condition', '>', 0);
        })
        ->whereHas('ean14', function ($query) {
          $query->where('status', 'conditioned');
        })
        // ->where('code128_id',null)
        ->get();

      // $config = EnlistProducts::with('product.stock.zone_position.zone.zone_type','without_conditioning_warehouse','product.stock.ean14.pallet.ean128','product.stock.ean128','product.stock.ean13')->where('condition','>', 0)->get();
    } elseif ($id === 'semi_condition') {
      $config = Stock::with('product.enlist_product', 'ean14.code14_packing.document', 'ean13', 'zone_position.zone')
        ->whereHas('zone_position.zone', function ($query) use ($person, $position) {
          $query->where('warehouse_id', $person->person->warehouse_id)->where('name', $position);
        })
        ->whereHas('product.enlist_product', function ($query) {
          $query->where('semi_condition', '>', 0);
        })
        // ->where('code128_id',null)
        ->get();
    } elseif ($id === 'without_conditioning') {
      $config = Stock::with('product.enlist_product', 'ean14.code14_packing.document', 'ean13', 'zone_position.zone')
        ->whereHas('zone_position.zone', function ($query) use ($person, $position) {
          $query->where('warehouse_id', $person->person->warehouse_id)->where('name', $position);
        })
        ->whereHas('product.enlist_product', function ($query) {
          $query->where('without_conditioning', '>', 0);
        })
        ->whereHas('ean14', function ($query) {
          $query->where('status', 'not_conditioned');
        })
        // ->where('code128_id',null)
        ->get();
    } elseif ($id === 'dispatch') {
      $config = StockTransition::with('product', 'ean128', 'ean14', 'zone_position.zone')->where('concept', 'dispatch')->get();
    }


    return $config->toArray();
  }

  public function createEnlist(Request $request)
  {
    $data = $request->all();

    ScheduleEnlist::create($data);
  }

  public function getAllDetails($id)
  {

    $settingsObj = new Settings();
    $position = $settingsObj->get('transit_receive');

    $transitorio = Stock::with('product', 'ean14', 'ean13')->with('zone_position.zone')
      ->whereHas('zone_position.zone', function ($query) use ($id, $position) {
        $query->where('warehouse_id', $id)->where('name', $position);
      })
      ->where('code128_id', null)
      ->get();

    return $transitorio->toArray();
  }

  public function getEnlistProductById($id)
  {

    $detalle = DocumentDetail::with('product', 'client.city', 'document')->where('document_id', $id)->get();

    return $detalle->toArray();
  }

  public function getEnlistProductByParentId($id)
  {

    $detalle = EnlistProducts::where('parent_product_id', $id)->get();

    return $detalle->toArray();
  }

  public function getEnlistProductsByParentScheduleId($id)
  {
    $search = Schedule::where('id', $id)->first();
    if ($search) {
      $parent_schedule_id = $search->parent_schedule_id;
      // $detalle = EnlistProducts::where('schedule_id',$parent_schedule_id)->get();
      $detalle = Product::with('enlist_product.warehouse', 'enlist_product.condition_warehouse', 'enlist_product.semi_condition_warehouse', 'enlist_product.without_conditioning_warehouse', 'enlist_product.document')
        ->whereHas('enlist_product', function ($q) use ($parent_schedule_id) {
          $q->where('schedule_id', $parent_schedule_id);
        })
        ->get();
    }


    return $detalle->toArray();
  }

  public function dropEan128(Request $request)
  {
    // $data= $request->all();
    $data = $request->all();
    $datos = $data['data'];
    // $warehouse_id = $data['warehouse_id'];
    $schedule_id = $data['schedule_id'];
    $companyId = $data['company_id'];

    $username = User::with('person')->where('id', $data['session_user_id'])->where('company_id', $data['company_id'])->first();
    // return $username;
    $warehouse_id = $username->person->warehouse_id;

    $tarea = Schedule::where('id', $schedule_id)->first();

    $parent_schedule_id = $tarea->parent_schedule_id;

    $settingsObj = new Settings($companyId);
    $position = $settingsObj->get('dispatch_zone');
    $real_quanty = 0;
    foreach ($datos as $value) {

      if ($value['stock_transition']['code_ean14']) {
        StockTransition::where('code_ean14', $value['stock_transition']['code_ean14'])->update(['code128_id' => null]);
      }

      // foreach ($value['stock_transition'] as  $value2) {
      $real_quanty += $value['stock_transition']['quanty'];


      $validar = Eancodes14Packing::where('code_ean14', $value['stock_transition']['code_ean14'])->first();
      if (!$validar) {

        $enlist = EnlistProducts::where('product_id', $value['stock_transition']['product_id'])->first();

        if ($enlist) {

          $obj = [
            "document_id" => $enlist['document_id'],
            "code_ean14" => $value['stock_transition']['code_ean14'],
            "code128_id" => $value['stock_transition']['code128_id']
          ];

          Eancodes14Packing::create($obj);
        }
      }
    }

    $validate = ProgressTask::where('schedule_id', $schedule_id)->first();
    if (!$validate) {

      $progress = [
        'schedule_id' => $schedule_id,
        'real_quanty' => $real_quanty

      ];
      ProgressTask::create($progress);
    }

    $transition = StockTransition::where('code128_id', null)->get();
    //
    // $insert = ZonePosition::with('zone')
    // ->whereHas('zone', function ($q) use ($warehouse_id,$position)
    // {
    //   $q->where('warehouse_id', $warehouse_id)->where('name',$position);
    // })->first();
    // return $this->response->error('gva si entro '.$insert->id, 404);
    // $objeto=[];
    // foreach ($transition as $value) {
    //
    //
    //   $validate = Stock::where('product_id',$value['product_id'])->where('zone_position_id',$insert->id)->first();
    //   if ($validate) {
    //     continue;
    //   }else {
    //     $objeto  = [
    //       'product_id'=> $value['product_id'],
    //       'zone_position_id'=> $insert->id,
    //       'code128_id'=> null,
    //       'code_ean14'=> $value['stock_transition']['code_ean14'],
    //       'quanty'=> $value['quanty'],
    //
    //     ];
    //     $create = Stock::create($objeto);
    //
    //     // $delete = StockTransition::where('product_id',$value['product_id'])->where('zone_position_id',$value['id'])->delete();
    //   }
    //
    // }
    return $transition->toArray();
  }

  public function saveSchedule(Request $request)
  {
    $data = $request->all();
    $bodegas = array_key_exists('bodegas', $data) ? $data['bodegas'] : NULL;
    $warehouse = array_key_exists('warehouse', $data) ? $data['warehouse'] : NULL;
    $parent = array_key_exists('parent', $data) ? $data['parent'] : NULL;
    $documents = array_key_exists('documents', $data) ? $data['documents'] : NULL;
    $companyId = $request->input('company_id');

    $settingsObj = new Settings($companyId);
    $chargeUserName = $settingsObj->get('stock_group');
    //
    // $username = User::with('person')->where('id',$data['session_user_id'])->first();
    //
    // $warehouseId = $username->warehouse_id;

    // $user = User::whereHas('person.group', function ($q) use ($chargeUserName) {
    //     $q->where('name', $chargeUserName);
    // })->whereHas('person', function ($q) use ($warehouse) {
    //     $q->where('warehouse_id', $warehouse);
    // })->first();

    // if (empty($user)) {
    //     return $this->response->error('user_not_found', 404);
    // }

    $taskSchedules = [
      'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'name' => 'Reubicar pallet sobrante:' . ' ' . $documents,
      'schedule_type' => ScheduleType::Restock,
      'schedule_action' => ScheduleAction::Transform,
      'status' => ScheduleStatus::Process,
      'company_id' => $companyId,
      'user_id' => $data['session_user_id']
    ];

    $schedule = Schedule::create($taskSchedules);

    $tran = ScheduleTransition::where('schedule_id', $parent)->get();

    if (!empty($tran)) {
      foreach ($tran as $value) {
        ScheduleTransition::where('schedule_id', $parent)->update(['schedule_id' => $schedule->id]);
      }
    }

    $aux = 0;
    foreach ($bodegas as $value) {

      if (!$aux || $aux !== $value) {

        $settingsObj = new Settings();
        $chargeUserName = $settingsObj->get('leader_charge');

        $user = User::whereHas('person.group', function ($q) use ($chargeUserName) {
          $q->where('name', $chargeUserName);
        })->whereHas('person', function ($q) use ($value) {
          $q->where('warehouse_id', $value);
        })->first();

        if (empty($user)) {
          return $this->response->error('user_not_found', 404);
        }

        $taskSchedules = [
          'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
          'name' => 'Gestionar planeación:' . ' ' . $documents,
          'schedule_type' => ScheduleType::EnlistPlan,
          'schedule_action' => ScheduleAction::Enlist,
          'status' => ScheduleStatus::Process,
          'user_id' => $user->id,
          'parent_schedule_id' => $parent
        ];

        $schedule = Schedule::create($taskSchedules);
        //
        //     $chargeUserName = $settingsObj->get('stock_group');
        //
        //     $user = User::whereHas('person.group', function ($q) use ($chargeUserName)
        //     {
        //       $q->where('name', $chargeUserName);
        //     })->whereHas('person', function ($q) use ($value)
        //     {
        //       $q->where('warehouse_id', $value);
        //     })->first();
        //
        //     if(empty($user)) {
        //       return $this->response->error('user_not_found', 404);
        //     }
        //
        //     $taskSchedulesW = [
        //            'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0].' '.explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
        //            'name' => 'Generar pallet :',
        //            'schedule_type' => ScheduleType::Pallet,
        //            'schedule_action' => ScheduleAction::Generate,
        //            'status' => ScheduleStatus::Process,
        //            'user_id' => $user->id
        //          ];
        //          $scheduleW = Schedule::create($taskSchedulesW);
        //
        $aux = $value;
      }
    }
  }

  public function saveScheduleWare(Request $request)
  {
    $data = $request->all();
    // $bodegas = array_key_exists('bodegas', $data) ? $data['bodegas'] : NULL;
    $warehouse = array_key_exists('warehouse', $data) ? $data['warehouse'] : NULL;
    $parent = array_key_exists('parent', $data) ? $data['parent'] : NULL;
    $companyId = $request->input('company_id');
    // $documents = array_key_exists('documents', $data) ? $data['documents'] : NULL;

    $settingsObj = new Settings();
    $chargeUserName = $settingsObj->get('stock_group');
    //
    // $username = User::with('person')->where('id',$data['session_user_id'])->first();
    //
    // $warehouseId = $username->warehouse_id;

    $user = User::whereHas('person.group', function ($q) use ($chargeUserName) {
      $q->where('name', $chargeUserName);
    })->whereHas('person', function ($q) use ($warehouse) {
      $q->where('warehouse_id', $warehouse);
    })->first();

    if (empty($user)) {
      return $this->response->error('user_not_found', 404);
    }

    $taskSchedules = [
      'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'name' => 'Reubicar pallet sobrante:',
      'schedule_type' => ScheduleType::Restock,
      'schedule_action' => ScheduleAction::Transform,
      'status' => ScheduleStatus::Process,
      'company_id' => $companyId,
      'user_id' => $user->id
    ];

    $schedule = Schedule::create($taskSchedules);

    $tran = ScheduleTransition::where('schedule_id', $parent)->get();

    if (!empty($tran)) {
      foreach ($tran as $value) {
        ScheduleTransition::where('schedule_id', $parent)->update(['schedule_id' => $schedule->id]);
      }
    }
  }

  public function updateEnlist(Request $request)
  {
    $data = $request->all();

    $status = array_key_exists('status', $data) ? $data['status'] : NULL;
    $id = array_key_exists('id', $data) ? $data['id'] : NULL;

    $config = ScheduleEnlist::where('schedule_id', $id)->update(['status' => $status]);

    $search = ScheduleEnlist::where('schedule_id', $id)->first();
    if ($search) {
      $config = Schedule::where('id', $search->parent_schedule_id)->update(['status' => 'closed']);
    }

    $update = EnlistProducts::where('schedule_id', $search->parent_schedule_id)->first();
    if ($update) {
      if ($status === 'removed') {
        $update_document = Document::where('id', $update->document_id)->update(['status' => 'Retirado']);
      } elseif ($status === 'relocated') {
        $update_document = Document::where('id', $update->document_id)->update(['status' => 'Reubicado']);
      }
    }
  }

  public function validateTask(Request $request)
  {
    $data = $request->all();
    $parent_schedule_id = array_key_exists('parent_schedule_id', $data) ? $data['parent_schedule_id'] : NULL;
    $flag = array_key_exists('flag', $data) ? $data['flag'] : NULL;
    $search = Schedule::where('id', $parent_schedule_id)->first();
    $settingsObj = new Settings();
    // $session_user_id = array_key_exists('session_user_id', $data) ? $data['session_user_id'] : NULL;
    // $dato = array_key_exists('dato', $data) ? $data['dato'] : NULL;


    $chargeUserName = $settingsObj->get('cedi_charge');

    $user = User::whereHas('person.group', function ($q) use ($chargeUserName) {
      $q->where('name', $chargeUserName);
    })->first();

    if (empty($user)) {
      return $this->response->error('user_not_found', 404);
    }
    if (!$flag) {
      $taskSchedules = [
        'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
        'name' => 'Validar faltante empaque :',
        'schedule_type' => ScheduleType::Validate,
        'schedule_action' => ScheduleAction::Packing,
        'status' => ScheduleStatus::Process,
        'user_id' => $user->id,
        'parent_schedule_id' => $parent_schedule_id
      ];
      $schedule = Schedule::create($taskSchedules);
      // $taskSchedules['schedule_id'] = $schedule->id;
      // ScheduleEnlist::create($taskSchedules);
    } else {
      $taskSchedules = [
        'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
        'name' => 'Validar faltante empaque zonas picking :',
        'schedule_type' => ScheduleType::Validate,
        'schedule_action' => ScheduleAction::PackingZones,
        'status' => ScheduleStatus::Process,
        'user_id' => $user->id,
        'parent_schedule_id' => $search->parent_schedule_id
      ];
      $schedule = Schedule::create($taskSchedules);
      $taskSchedules['schedule_id'] = $schedule->id;
      ScheduleEnlist::create($taskSchedules);
    }
  }

  public function reasonCodesave(Request $request)
  {
    $data = $request->all();

    $reason_code_id = array_key_exists('reason_code_id', $data) ? $data['reason_code_id'] : NULL;
    $schedule_id = array_key_exists('schedule_id', $data) ? $data['schedule_id'] : NULL;
    $bandera = array_key_exists('bandera', $data) ? $data['bandera'] : NULL;
    $zones_position_id_packing = array_key_exists('zones_position_id_packing', $data) ? $data['zones_position_id_packing'] : NULL;

    if ($bandera === false) {
      $config = SchedulePositionPacking::where('schedule_id', $schedule_id)->update(['reason_code_id' => $reason_code_id]);
    } else {
      $config = SchedulePositionPacking::where('schedule_id', $schedule_id)->update(['reason_code_id' => $reason_code_id]);
      foreach ($zones_position_id_packing as $value) {

        $validate = PackingZones::where('zone_position_id', $value)->first();
        if ($validate) {
          // $obj = [
          //   "zone_position_id"=>$value,
          //   "schedule_id"=>$schedule_id
          // ];
          $create = PackingZones::where('zone_position_id', $value)->update(['schedule_id' => $schedule_id, 'reason_code_id' => $reason_code_id]);
        }
      }
    }

    // return $config;

  }

  public function getStockByPosition($id)
  {

    $config = SchedulePositionPacking::with('ean14.stock', 'product', 'zone_position', 'schedule', 'reason_code')
      ->where('zone_position_id', $id)
      ->has('ean14.stock')
      ->get();

    return $config->toArray();
  }

  public function getStockById($id)
  {

    $config = PackingZones::with('zone_position.stocks.product', 'schedule', 'reason_code')
      ->where('schedule_id', $id)
      ->has('zone_position.stocks')
      ->get();

    return $config->toArray();
  }

  public function blockDocuments(Request $request)
  {
    $data = $request->all();
    $id = array_key_exists('ids', $data) ? $data['ids'] : NULL;
    $config = EnlistProducts::whereIn('document_id', $id)
      ->get();

    return $config->toArray();
  }

  public function saveServiceData(Request $request)
  {
    $data = $request->all();
    $information = array_key_exists('information', $data) ? $data['information'] : NULL;
    $truck = array_key_exists('truck', $data) ? $data['truck'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;

    $comments = "";
    foreach ($truck['schedule_comments'] as $comentario) {
      $comments .= $comentario['comment'] . ',';
    }


    // return $truck['schedule_comments'];
    $mov = [];
    $array = [];
    foreach ($information as $value2) {
      // foreach ($value1 as  $value2) {

      if (isset($value2['flag_menor'])) {
        // return $value2['flag_menor'];
        $array = $value2['arreglo_menor'];
      } else if (isset($value2['flag_igual'])) {
        // return $value2['flag_igual'];
        $array = $value2['arreglo_igual'];
      } else if (isset($value2['flag_mayor'])) {
        // return $value2['flag_mayor'];
        $array = $value2['arreglo_mayor'];
      }
      foreach ($array as $value) {
        $cdOrigin = Warehouse::with('distribution_center')->where('id', $value['document']['warehouse_origin'])->first();
        $cdDestino = Warehouse::with('distribution_center')->where('id', $value['document']['warehouse_destination'])->first();
        $zone = Zone::where('id', $truck['schedule_receipt']['zone_id'])->first();
        $coopera = explode("-", $value['document']['number']);
        $sum = ($value['quanty_received'] + $value['is_additional'] + $value['quarantine'] + $value['damaged_cartons']) > $value['cartons'] ? $value['cartons'] : ($value['quanty_received'] + $value['is_additional'] + $value['quarantine'] + $value['damaged_cartons']);

        $igual = $value['quanty_received'] + $value['is_additional'] + $value['quarantine'] + $value['damaged_cartons'];

        if ($sum > 0) {

          $mov[] = [
            "copera" => $cdDestino['distribution_center']['code'], //id de centro de operaciones que recibe mercancia
            "bodsal_mov" => $cdOrigin['code'], //codigo bodega destino de mercancia
            "ubicasal_mov" => $value['ubicasal_mov'], // codigo de zona de recibo
            "lote_mov" => $value['batch'], // lote
            "copera_mov" => $coopera[0], //centro de distribucion que envia la mercancia
            "umedida_mov" => $value['product_ean14']['containers']['code'], //unidad de medida mercancia
            "ubicaent_mov" => $zone['code'],
            "cant_mov" => $sum, //cantidad recibida de la mercancia
            "barcode_mov" => $value['code_ean14'], //ean14
          ];
        }

        $objet = [
          "api_name" => "rstOly_sob_wms_sie_traslTransEnt",
          "transOrigen" => "tr4c3",
          "copera" => $cdDestino['distribution_center']['code'], //id de centro de operaciones que recibe mercancia
          "fecha_doc" => str_replace("-", "", $datetime = date("Y-m-d")), //fecha creacion documento
          "notas" => $comments, //observaciones
          "bodsal" => $cdOrigin['code'], //codigo bodega origen de mercancia
          "bodent" => $cdDestino['code'], //codigo bodega destino de mercancia
          "numdoc_ref" => $value['document']['number'], //numero de documento
          "copera_origen" => $coopera[0], //centro de distribucion que envia la mercancia
          "tpdoc_origen" => $coopera[1], //tipo de documento
          "numdoc_origen" => $coopera[2], //numero de documento
          "placa_transp" => $value['document']['vehicle_plate'], //placa del vehiculo
          "nit_transp" => $value['document']['nit_transp'], //identificacion transportador
          "nomcond_transp" => $value['document']['driver_name'], //nombre conductor
          "idcond_transp" => $value['document']['driver_identification'], //cedula conductor
          "guia_transp" => $value['document']['guia_transp'], //guia transporte
          "peso_transp" => $value['document']['weight'], //peso de la mercancia
          "mov_" => $mov
        ];
      }

      // }
    }
    $retorno = [
      new \stdClass(), $objet, new \stdClass()
    ];
    if (count($mov) > 0) {
      // return $retorno;
      return SoberanaServices::PostReceive($retorno);
    }
    // return $config->toArray();

  }

  public function saveServiceDataPlus(Request $request)
  {
    $data = $request->all();
    $information = array_key_exists('information', $data) ? $data['information'] : NULL;
    $truck = array_key_exists('truck', $data) ? $data['truck'] : NULL;

    $comments = "";
    foreach ($truck['schedule_comments'] as $comentario) {
      $comments .= $comentario['comment'] . ',';
    }
    $mov = [];
    foreach ($information as $value2) {
      // foreach ($value1 as  $value2) {
      foreach ($value2['arreglo_mayor'] as $value) {
        $cdOrigin = Warehouse::with('distribution_center')->where('id', $value['document']['warehouse_origin'])->first();
        $cdDestino = Warehouse::with('distribution_center')->where('id', $value['document']['warehouse_destination'])->first();
        $zone = Zone::where('id', $truck['schedule_receipt']['zone_id'])->first();
        $coopera = explode("-", $value['document']['number']);
        $sum = ($value['quanty_received'] + $value['is_additional'] + $value['quarantine'] + $value['damaged_cartons']) - $value['cartons'];

        if ($sum > 0) {
          $mov[] = [
            "copera" => $cdDestino['distribution_center']['code'], //id de centro de operaciones que recibe mercancia
            "bodsal_mov" => $cdOrigin['code'], //codigo bodega destino de mercancia
            "ubicasal_mov" => $value['ubicasal_mov'], // codigo de zona de recibo
            "lote_mov" => $value['batch'], // lote
            "copera_mov" => $coopera[0], //centro de distribucion que envia la mercancia
            "umedida_mov" => $value['product_ean14']['containers']['code'], //unidad de medida mercancia
            "cant_mov" => $sum, //cantidad recibida de la mercancia
            "ubicaent_mov" => $zone['code'], //codigo bodega destino de mercancia
            "barcode_mov" => $value['code_ean14'], //ean14
            "cajas_venian" => $value['cartons']
          ];
        }

        $objet = [
          "api_name" => "rstOly_sob_wms_sie_traslTransAjuEnt",
          "transOrigen" => "tr4c3",
          "copera" => $cdDestino['distribution_center']['code'], //id de centro de operaciones que recibe mercancia
          "fecha_doc" => str_replace("-", "", $datetime = date("Y-m-d")), //fecha creacion documento
          "notas" => $comments, //observaciones
          "bodsal" => $cdOrigin['code'], //codigo bodega origen de mercancia
          "bodent" => $cdDestino['code'], //codigo bodega destino de mercancia
          "numdoc_ref" => $value['document']['number'], //numero de documento
          // "copera_origen"=>$cdOrigin['distribution_center']['id'],//centro de distribucion que envia la mercancia
          // "tpdoc_origen"=>$value['document']['receipt_type']['document_name'],//tipo de documento
          // "numdoc_origen"=>$value['document']['number'],//numero de documento
          "placa_transp" => $value['document']['vehicle_plate'], //placa del vehiculo
          "nit_transp" => $value['document']['nit_transp'], //identificacion transportador
          "nomcond_transp" => $value['document']['driver_name'], //nombre conductor
          "idcond_transp" => $value['document']['driver_identification'], //cedula conductor
          "guia_transp" => $value['document']['guia_transp'], //guia transporte
          "peso_transp" => $value['document']['weight'], //peso de la mercancia
          "mov_" => $mov
        ];
      }
      // }
    }
    $retorno = [
      new \stdClass(), $objet, new \stdClass()
    ];
    if (count($mov) > 0) {
      // return $retorno;
      return SoberanaServices::PostReceivePlus($retorno);
    }
    // return $config->toArray();

  }

  public function saveServiceDataBatch(Request $request)
  {
    $data = $request->all();
    $information = array_key_exists('information', $data) ? $data['information'] : NULL;
    $truck = array_key_exists('truck', $data) ? $data['truck'] : NULL;
    $companyId = $request->input('company_id');

    $comments = "";
    foreach ($truck['schedule_comments'] as $comentario) {
      $comments .= $comentario['comment'] . ',';
    }
    $mov = [];
    foreach ($information as $value2) {
      // foreach ($value1 as  $value2) {
      foreach ($value2['arreglo_batch'] as $value) {
        $cdOrigin = Warehouse::with('distribution_center')->where('id', $value['document']['warehouse_origin'])->first();
        $cdDestino = Warehouse::with('distribution_center')->where('id', $value['document']['warehouse_destination'])->first();
        $zone = Zone::where('id', $truck['schedule_receipt']['zone_id'])->first();
        $coopera = explode("-", $value['document']['number']);
        $sum = ($value['quanty_received'] + $value['is_additional'] + $value['quarantine'] + $value['damaged_cartons']);
        if ($sum > 0) {
          $mov[] = [
            "copera" => $cdDestino['distribution_center']['code'], //id de centro de operaciones que recibe mercancia
            "bodsal_mov" => $cdOrigin['code'], //codigo bodega destino de mercancia
            "ubicasal_mov" => $value['ubicasal_mov'], // codigo de zona de recibo
            "lote_mov" => $value['batch'], // lote
            "copera_mov" => $coopera[0], //centro de distribucion que envia la mercancia
            "umedida_mov" => $value['product_ean14']['containers']['code'], //unidad de medida mercancia
            "cant_mov" => $sum, //cantidad recibida de la mercancia
            "ubicaent_mov" => $zone['code'], //codigo bodega destino de mercancia
            "barcode_mov" => $value['code_ean14'], //ean14
            "cajas_venian" => $value['cartons']
          ];
        }

        $objet = [
          "api_name" => "rstOly_sob_wms_sie_traslTransDirecto",
          "transOrigen" => "tr4c3",
          "copera" => $cdDestino['distribution_center']['code'], //id de centro de operaciones que recibe mercancia
          "fecha_doc" => str_replace("-", "", $datetime = date("Y-m-d")), //fecha creacion documento
          "notas" => $comments, //observaciones
          "bodsal" => $cdOrigin['code'], //codigo bodega origen de mercancia
          "bodent" => $cdDestino['code'], //codigo bodega destino de mercancia
          "numdoc_ref" => $value['document']['number'], //numero de documento
          // "copera_origen"=>$cdOrigin['distribution_center']['id'],//centro de distribucion que envia la mercancia
          // "tpdoc_origen"=>$value['document']['receipt_type']['document_name'],//tipo de documento
          // "numdoc_origen"=>$value['document']['number'],//numero de documento
          "placa_transp" => $value['document']['vehicle_plate'], //placa del vehiculo
          "nit_transp" => $value['document']['nit_transp'], //identificacion transportador
          "nomcond_transp" => $value['document']['driver_name'], //nombre conductor
          "idcond_transp" => $value['document']['driver_identification'], //cedula conductor
          "guia_transp" => $value['document']['guia_transp'], //guia transporte
          "peso_transp" => $value['document']['weight'], //peso de la mercancia
          "mov_" => $mov
        ];
      }
      // }
    }
    $retorno = [
      new \stdClass(), $objet, new \stdClass()
    ];
    if (count($mov) > 0) {
      // return $retorno;
      return SoberanaServices::getToken($retorno, $companyId);
    }
    // return $config->toArray();

  }

  public function saveServiceDataReturn(Request $request)
  {
    $data = $request->all();
    $information = array_key_exists('information', $data) ? $data['information'] : NULL;
    $truck = array_key_exists('truck', $data) ? $data['truck'] : NULL;
    $mov = [];
    $comments = "";
    foreach ($truck['schedule_comments'] as $comentario) {
      $comments .= $comentario['comment'] . ',';
    }

    foreach ($information as $value2) {
      //  foreach ($value1 as  $value2) {
      foreach ($value2['arreglo_menor'] as $value) {
        $cdOrigin = Warehouse::with('distribution_center')->where('id', $value['document']['warehouse_origin'])->first();
        $cdDestino = Warehouse::with('distribution_center')->where('id', $value['document']['warehouse_destination'])->first();
        $zone = Zone::where('id', $truck['schedule_receipt']['zone_id'])->first();

        $coopera = explode("-", $value['document']['number']);

        $sum = $value['cartons'] - ($value['quanty_received'] + $value['is_additional'] + $value['quarantine'] + $value['damaged_cartons']);
        $menor = $value['quanty_received'] + $value['is_additional'] + $value['quarantine'] + $value['damaged_cartons'];
        if ($sum > 0 && $menor < $value['cartons']) {

          $mov[] = [
            "copera" => $coopera[0], //id de centro de operaciones que recibe mercancia
            "bodsal_mov" => $cdOrigin['code'], //codigo bodega destino de mercancia
            "ubica_mov" => $value['ubicasal_mov'], // codigo de zona de recibo
            "lote_mov" => $value['batch'], // lote
            "copera_mov" => $coopera[0], //centro de distribucion que envia la mercancia
            "umedida_mov" => $value['product_ean14']['containers']['code'], //unidad de medida mercancia
            "cant_mov" => $sum, //cantidad recibida de la mercancia
            // "ubicaent_mov"=>$value['document']['warehouse_destination'],//codigo bodega destino de mercancia
            "barcode_mov" => $value['code_ean14'], //ean14
          ];
        }


        $objet = [
          "api_name" => "rstOly_sob_wms_sie_traslTransDev",
          "transOrigen" => "tr4c3",
          "copera" => $coopera[0], //id de centro de operaciones que recibe mercancia
          "fecha_doc" => str_replace("-", "", $datetime = date("Y-m-d")), //fecha creacion documento
          "notas" => $comments, //observaciones
          "bodsal" => $cdOrigin['code'], //codigo bodega origen de mercancia
          "bodent" => $cdDestino['code'], //codigo bodega destino de mercancia
          "numdoc_ref" => $value['document']['number'], //numero de documento
          // "copera_origen"=>$cdOrigin['distribution_center']['id'],//centro de distribucion que envia la mercancia
          "tpdoc_origen" => $coopera[1], //tipo de documento
          "numdoc_origen" => $coopera[2], //numero de documento
          "placa_transp" => $value['document']['vehicle_plate'], //placa del vehiculo
          "nit_transp" => $value['document']['nit_transp'], //identificacion transportador
          "nomcond_transp" => $value['document']['driver_name'], //nombre conductor
          "idcond_transp" => $value['document']['driver_identification'], //cedula conductor
          "guia_transp" => $value['document']['guia_transp'], //guia transporte
          "peso_transp" => $value['document']['weight'], //peso de la mercancia
          "mov_" => $mov
        ];
      }
      // }
    }
    $retorno = [
      new \stdClass(), $objet, new \stdClass()
    ];
    if (count($mov) > 0) {
      // return $retorno;
      return SoberanaServices::PostReceiveReturn($retorno);
    }
    // return $config->toArray();

  }

  public function deleteAll(Request $request)
  {
    $data = $request->all();

    $stock = array_key_exists('stock', $data) ? $data['stock'] : NULL;
    $schedule_id = array_key_exists('schedule_id', $data) ? $data['schedule_id'] : NULL;
    $flag = array_key_exists('flag', $data) ? $data['flag'] : NULL;
    $parent_schedule_id = array_key_exists('parent_schedule_id', $data) ? $data['parent_schedule_id'] : NULL;
    $settingsObj = new Settings();

    if (!$flag) {
      foreach ($stock as $value) {
        $config = Stock::where('zone_position_id', $value['zone_position_id'])->where('product_id', $value['product_id'])->delete();

        $config = SchedulePositionPacking::where('code14_id', $value['code14_id'])->delete();
        $zone_position_id = $value['zone_position_id'];
      }
      $schedule = Schedule::where('id', $schedule_id)->update(['status' => 'closed']);
      // $schedule = Schedule::where('id', $parent_schedule_id)->update(['status' => 'closed']);

      $posi = ZonePosition::with('zone')->where('id', $zone_position_id)->first();
      $warehouse_id = $posi->zone->warehouse_id;
      $chargeUserName = $settingsObj->get('receipt_group');

      $user = User::whereHas('person.group', function ($q) use ($chargeUserName) {
        $q->where('name', $chargeUserName);
      })->whereHas('person', function ($q) use ($warehouse_id) {
        $q->where('warehouse_id', $warehouse_id);
      })->first();

      if (empty($user)) {
        return $this->response->error('user_not_found', 404);
      }


      $taskSchedules = [
        'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
        'name' => 'Consolidar inventario despacho :',
        'schedule_type' => ScheduleType::EnlistPlan,
        'schedule_action' => ScheduleAction::Dispatch,
        'status' => ScheduleStatus::Process,
        'user_id' => $user->id,
        // 'parent_schedule_id' => $parent_schedule_id
      ];
      $schedule = Schedule::create($taskSchedules);
    } else {
      foreach ($stock as $value) {
        $config = Stock::where('zone_position_id', $value['zone_position_id'])->where('product_id', $value['product_id'])->delete();
        $zone_position_id = $value['zone_position_id'];
        $config = SchedulePositionPacking::where('code14_id', $value['code14_id'])->delete();
      }
      $schedule = Schedule::where('id', $schedule_id)->update(['status' => 'closed']);
      $schedule = Schedule::where('id', $parent_schedule_id)->update(['status' => 'closed']);

      $posi = ZonePosition::with('zone')->where('id', $zone_position_id)->first();
      $warehouse_id = $posi->zone->warehouse_id;
      $chargeUserName = $settingsObj->get('receipt_group');

      $user = User::whereHas('person.group', function ($q) use ($chargeUserName) {
        $q->where('name', $chargeUserName);
      })->whereHas('person', function ($q) use ($warehouse_id) {
        $q->where('warehouse_id', $warehouse_id);
      })->first();

      if (empty($user)) {
        return $this->response->error('user_not_found', 404);
      }


      $taskSchedules = [
        'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
        'name' => 'Consolidar inventario despacho :',
        'schedule_type' => ScheduleType::EnlistPlan,
        'schedule_action' => ScheduleAction::Dispatch,
        'status' => ScheduleStatus::Process,
        'user_id' => $user->id,
        // 'parent_schedule_id' => $parent_schedule_id
      ];
      $schedule = Schedule::create($taskSchedules);
    }
  }

  public function getDataPicking($id)
  {
    // $data= $request->all();

    $settingsObj = new Settings();
    $PickingName = $settingsObj->get('picking_type');
    $WorkAreaConcept = $settingsObj->get('zone_concept_work_area');


    $picking = DocumentDetail::with('product.stock.ean14.code14_packing', 'product.enlist_product.product')
      // ->has('product.stock.ean14.code14_packing')
      ->has('enlist_product.document')
      ->get();
    // ->get();

    $productsId = $picking->pluck('product_id')->toArray();

    // return $picking->toArray();
    // $WorkAreaConceptId = ZoneConcept::where('name', $WorkAreaConcept)->first()->id;
    $areaTrabajo = Stock::with('zone_position.concept')->whereHas('zone_position.concept', function ($q) use ($WorkAreaConcept) {
      $q->where('name', $WorkAreaConcept);
    })
      ->whereIn('product_id', $productsId)->get();

    $zones = DB::table('wms_zone_positions')
      ->join('wms_stock', 'wms_zone_positions.id', '=', 'wms_stock.zone_position_id')
      ->join('wms_products', 'wms_stock.product_id', '=', 'wms_products.id')
      ->join('wms_enlist_products', 'wms_products.id', '=', 'wms_enlist_products.product_id')
      // ->join('wms_document_details', 'wms_enlist_products.product_id', '=', 'wms_document_details.product_id')
      ->join('wms_zones', 'wms_zone_positions.zone_id', '=', 'wms_zones.id')
      ->join('wms_zone_types', 'wms_zones.zone_type_id', '=', 'wms_zone_types.id')
      ->groupBy('wms_stock.product_id', 'wms_products.reference')
      ->whereIn('wms_stock.product_id', $productsId)
      ->where('wms_zone_types.name', '=', $PickingName)
      ->select('wms_zone_positions.code', 'wms_products.reference', 'wms_products.ean', 'wms_products.description', 'wms_stock.quanty', 'wms_enlist_products.order_quanty', 'wms_stock.product_id', 'wms_stock.zone_position_id', 'wms_enlist_products.document_id')
      ->get();

    $real_quanty_acum = 0;
    foreach ($zones as $value) {
      $real_quanty_acum += $value->quanty;
      $validate = PackingZones::where('zone_position_id', $value->zone_position_id)->first();
      if (!$validate) {
        $obj = [
          "zone_position_id" => $value->zone_position_id,
          "product_id" => $value->product_id,
          "real_quanty" => $value->quanty

        ];
        $create = PackingZones::create($obj);
      }
    }

    $val = ProgressTask::where('schedule_id', $id)->first();
    if (!$val) {
      $cargar = [
        "schedule_id" => $id,
        "real_quanty" => $real_quanty_acum
      ];
      ProgressTask::create($cargar);
    }

    $sugerencia = [];

    return ['planeado' => $picking, 'area_trabajo' => $areaTrabajo, 'picking' => $zones, 'mostrar' => $zones];
    // return $zones;

  }

  public function getInformationDocumentById($id)
  {
    $document = Document::where('id', $id)->first();

    return $document->toArray();
  }

  public function getScheduleByScheduleId($id)
  {
    // $data= $request->all();
    // // $wId = $request->input('warehouse_id');
    // $wId = $data['warehouse_id'];
    // $schedule_id = $data['schedule_id'];

    $schedules = Schedule::with('schedule_stock');

    //Get warehouses for stocks
    if (isset($wId)) {
      $schedules = $schedules->whereHas('schedule_stock', function ($query) use ($wId) {
        $query->where('schedule_id', '=', $id);
      });
    }
    $schedules = $schedules->where('id', $id)->get();

    // $schedules = Schedule::with('schedule_stock')->where('id',$id)->get();

    return $schedules->toArray();
  }

  public function getStockByScheduleCount($id)
  {

    $count = Stock::with('product', 'stock_counts', 'ean14', 'zone_position')
      ->whereHas('stock_counts', function ($query) use ($id) {
        $query->where('schedule_id', $id);
      })
      ->get();


    // $count = DB::table('wms_stock')
    //  >join('wms_stock_counts', 'wms_stock.id', '=', 'wms_stock_counts.stock_id')
    //  ->where('schedule_id', $id)
    //  ->select('name', 'email as user_email')->get();

    //  $count = DB::table('wms_stock')
    //  >join('contacts', 'users.id', '=', 'contacts.user_id')
    //  ->select('name', 'email as user_email')->get();

    return $count->toArray();
  }

  public function adjust(Request $request)
  {
    $data = $request->all();
    return $data;

    $valor = array_key_exists('valor', $data) ? $data['valor'] : NULL;
    $conteo = 0;
    if ($valor['isSelected'] === "count1") {
      $conteo = 1;
    } elseif ($valor['isSelected'] === "count2") {
      $conteo = 2;
    } elseif ($valor['isSelected'] === "count3") {
      $conteo = 3;
    } elseif ($valor['isSelected'] === "count4") {
      $conteo = 4;
    } elseif ($valor['isSelected'] === "count5") {
      $conteo = 5;
    }

    // $schedule = Schedule::where('id', $id)->first();
    //       $consult = StockCount::where('count',$conteo)
    //       ->get();
    // // return $consult;
    //       foreach ($consult as $value) {

    //         $config = Stock::where('product_id', $value['product_id'])
    //         ->where('zone_position_id', $value['zone_position_id'])->update(['quanty' => $value['found']]);

    //         StockCount::where('product_id', $value['product_id'])->where('zone_position_id', $value['zone_position_id'])->delete();
    //       }


    return $consult->toArray();
  }

  public function getProductAdjust(Request $request)
  {

    $data = $request->all();

    $ean14 = $data['product_id'];
    $schedule_id = $data['schedule_id'];
    $position = $data['position'];

    // $parent = Schedule::where('id', $schedule_id)->first();

    // return $parent->parent_schedule_id;

    // $schedule = Schedule::where('id', $id)->first();
    $consult = StockCount::with('product', 'position', 'stock')
      ->where('product_id', $ean14)->where('schedule_id', $schedule_id)->where('zone_position_id', $position)
      ->get();

    return $consult->toArray();
  }

  public function updateScheduleCount($id)
  {

    Schedule::where('parent_schedule_id', $id)->where('schedule_type', 'task')->where('schedule_action', 'count_inventary')->update(['status' => 'process']);

    Schedule::where('parent_schedule_id', $id)->where('schedule_type', 'stock')->where('schedule_action', 'validate')->update(['status' => 'closed']);
  }

  public function GenerateRelocateTask(Request $request)
  {

    // $schedule = Schedule::where('id',$id)->first();
    $data = $request->all();
    $datos = $data['data'];

    return $datos;
    $warehouse_id = $data['warehouse_id'];
    $session_user_id = array_key_exists('session_user_id', $data) ? $data['session_user_id'] : NULL;
    // $username = User::with('person')->where('id',$session_user_id)->first();


    $settingsObj = new Settings();
    $chargeUserName = $settingsObj->get('stock_group');

    $user = User::whereHas('person.group', function ($q) use ($chargeUserName) {
      $q->where('name', $chargeUserName);
    })->whereHas('person', function ($q) use ($datos) {
      $q->where('warehouse_id', $datos[0]['warehouse_id']);
    })->first();

    // return $datos;

    if (empty($user)) {
      return $this->response->error('user_not_found', 404);
    }

    $taskSchedulesW = [
      'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'name' => 'Reubicar inventario para despacho:' . $datos[0]['number'],
      'schedule_type' => ScheduleType::EnlistPlan,
      'schedule_action' => ScheduleAction::relocateDispatch,
      'status' => ScheduleStatus::Process,
      'user_id' => $user->id
      // 'parent_schedule_id'=> $parent->parent_schedule_id
    ];
    $scheduleW = Schedule::create($taskSchedulesW);

    // $taskSchedulesW['schedule_id'] = $scheduleW->id;
    // ScheduleTransition::create($taskSchedulesW);

    // $objeto = [];
    foreach ($datos as $value) {

      $objeto = [
        "product_id" => $value['product_id'],
        "zone_position_id" => $value['zone_position_id'],
        // "code128_id"=>$value['code128_id'],
        "code_ean14" => $value['code_ean14'],
        "quanty" => $value['quanty'],
        "action" => "income",
        "concept" => "relocate",
        "warehouse_id" => $value['warehouse_id'],
        "user_id" => $session_user_id,
        "document_detail_id" => $value['document_detail_id']
      ];
      // return $objeto;
      $transition = StockTransition::create($objeto);

      $scheduletran = [
        "schedule_id" => $scheduleW->id,
        "transition_id" => $transition->id,
        "warehouse_id" => $warehouse_id
      ];
      ScheduleTransition::create($scheduletran);

      $stock = Stock::where('code_ean14', $value['code_ean14'])->delete();
    }
  }

  public function GenerateAproveTask(Request $request)
  {
    $data = $request->all();

    $number = $data['number'];
    $company_id = $data['company_id'];
    $user_id = $data['session_user_id'];

    $user_ware = User::with('person.warehouse')->where('id', $user_id)->first();

    // return $number;

    $document = Document::with('detail.stock')->whereIn('id', $number)->first();

    // $cdDestino = Warehouse::with('distribution_center')->where('id',$document->warehouse_destination)->first();

    // $cdOrigin = Warehouse::with('distribution_center')->where('id',$document->warehouse_origin)->first();

    $validate = Schedule::where('schedule_action', 'dispatch_plan')->where('status', 'process')->first();

    if (!$validate) {
      $settingsObj = new Settings($company_id);
      $chargeUserName = $settingsObj->get('leader_charge');

      $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
        $q->where('name', $chargeUserName);
      })->whereHas('person', function ($q) use ($user_ware) {
        $q->where('warehouse_id', $user_ware->person->warehouse->id);
      })->first();

      if (empty($user)) {
        return $this->response->error('user_not_found', 404);
      }
      // return $chargeUserName;

      $taskSchedulesW = [
        'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
        'name' => 'Programar Cita de despacho:' . ' ' . $document->number,
        'schedule_type' => ScheduleType::EnlistPlan,
        'schedule_action' => ScheduleAction::Dispatch_plan,
        'status' => ScheduleStatus::Process,
        'user_id' => $user->id,
        'company_id' => $company_id
      ];

      $scheduleW = Schedule::create($taskSchedulesW);
      $update_document = Document::where('id', $number)->update(['status' => 'Cargando']);
    }
  }

  public function loadTruck(Request $request)
  {
    $data = $request->all();
    $ean14 = array_key_exists('ean14', $data) ? $data['ean14'] : NULL;
    $plate = array_key_exists('plate', $data) ? $data['plate'] : NULL;
    $order_number = array_key_exists('order_number', $data) ? $data['order_number'] : NULL;
    $settingsObj = new Settings($data['company_id']);
    $dispatch = $settingsObj->get('position_dispatch');

    DB::beginTransaction();

    try {

      $despacho = Document::join('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
        ->join('wms_stock', function ($query) {
          $query->on('wms_document_details.id', '=',  'wms_stock.document_detail_id');
          $query->on('wms_document_details.product_id', '=', 'wms_stock.product_id');
        })
        ->join('wms_zone_positions', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
        ->join('wms_zones', 'wms_zone_positions.zone_id', '=', 'wms_zones.id')
        ->join("wms_ean_codes14 as e14", "wms_stock.code_ean14", "=", "e14.id")
        ->selectRaw("
                    GROUP_CONCAT(wms_stock.product_id) as productoId,
                    e14.code14 as ean14,
                    e14.id as ean14Id,
                    SUM(wms_stock.quanty) as unidades
                ")
        ->where('wms_zones.name', $dispatch)
        ->where('wms_documents.id', $order_number)
        ->where('e14.code14', $ean14)
        ->groupBy("e14.id")
        ->first();

      if (empty($despacho)) {
        $despacho = Document::join('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
          ->join('wms_stock', function ($query) {
            $query->on('wms_document_details.id', '=',  'wms_stock.document_detail_id');
            $query->on('wms_document_details.product_id', '=', 'wms_stock.product_id');
          })
          ->join('wms_zone_positions', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
          ->join('wms_zones', 'wms_zone_positions.zone_id', '=', 'wms_zones.id')
          ->join("wms_ean_codes14 as e14", "wms_stock.code_ean14", "=", "e14.id")
          ->selectRaw("
                        GROUP_CONCAT(wms_stock.product_id) as productoId,
                        e14.code14 as ean14,
                        e14.id as ean14Id,
                        SUM(wms_stock.quanty) as unidades
                        ")
          ->where('wms_zones.name', $dispatch)
          ->where('wms_documents.id', $order_number)
          ->where('e14.master', $ean14)
          ->groupBy("e14.id")
          ->first();
      }

      if (!$despacho) {
        throw new RuntimeException('No se encontró información');
      }

      $objeto = [
        "plate" => $plate,
        "code14_id" => $despacho->ean14Id,
        "order_number" => $order_number,
        "quanty" => $despacho->unidades
      ];
      BoxDriver::create($objeto);

      $packing14 = DB::select(
        "SELECT
                    id
                FROM
                    `wms_eancodes14_packing`
                WHERE
                    `product_id` IN ( $despacho->productoId)
                    AND `document_id` = $order_number"
      );

      foreach ($packing14 as $packing) {
        Eancodes14Packing::where('id', $packing->id)->delete();
      }

      $despacho = Document::join('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
        ->join('wms_stock', function ($query) {
          $query->on('wms_document_details.id', '=',  'wms_stock.document_detail_id');
          $query->on('wms_document_details.product_id', '=', 'wms_stock.product_id');
        })
        ->join('wms_zone_positions', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
        ->join('wms_zones', 'wms_zone_positions.zone_id', '=', 'wms_zones.id')
        ->join("wms_ean_codes14 as e14", "wms_stock.code_ean14", "=", "e14.id")
        ->selectRaw("group_concat(wms_stock.id) as stockId")
        ->where('wms_zones.name', $dispatch)
        ->where('wms_documents.id', $order_number)
        ->where('e14.code14', $ean14)
        ->groupBy("e14.id")
        ->first();

      if (empty($despacho)) {
        $despacho = Document::join('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
          ->join('wms_stock', function ($query) {
            $query->on('wms_document_details.id', '=',  'wms_stock.document_detail_id');
            $query->on('wms_document_details.product_id', '=', 'wms_stock.product_id');
          })
          ->join('wms_zone_positions', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
          ->join('wms_zones', 'wms_zone_positions.zone_id', '=', 'wms_zones.id')
          ->join("wms_ean_codes14 as e14", "wms_stock.code_ean14", "=", "e14.id")
          ->selectRaw("group_concat(wms_stock.id) as stockId")
          ->where('wms_zones.name', $dispatch)
          ->where('wms_documents.id', $order_number)
          ->where('e14.master', $ean14)
          ->groupBy("e14.id")
          ->first();
      }

      DB::delete("DELETE FROM wms_stock where id IN ($despacho->stockId)");

      DB::commit();
      return response([], 201);
    } catch (Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response(["message" => $e->getMessage()], 500);
    }
  }

  public function updateTask(Request $request)
  {
    $data = $request->all();

    $status = array_key_exists('status', $data) ? $data['status'] : NULL;
    $schedule = array_key_exists('schedule', $data) ? $data['schedule'] : NULL;
    $documentos = array_key_exists('documentos', $data) ? $data['documentos'] : NULL;

    $config = Schedule::where('parent_schedule_id', $schedule)->update(['status' => $status]);

    foreach ($documentos as $value) {
      Document::where('id', $value['document_id'])->update(['status' => 'dispatch']);
    }
  }

  public function getAll14ByPlate(Request $request)
  {
    $data = $request->all();

    $plate = array_key_exists('plate', $data) ? $data['plate'] : NULL;
    $order_number = array_key_exists('order_number', $data) ? $data['order_number'] : NULL;
    $consult = BoxDriver::with('ean14')->where('plate', $plate)->whereRaw("order_number in ($order_number)")->get();
    return $consult->toArray();
  }

  public function getPackingListBySchedule_id($id)
  {
    $document = DocumentSchedule::with('document.code_packing.stock', 'document.detail', 'document.ean14', 'enlist_products', 'box_driver', 'document.clientdocument')->where('schedule_id', $id)->get();
    return $document->toArray();
  }

  public function getAll14ByOrder($id, Request $request)
  {
    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    $chargeUserName = $settingsObj->get('dispatch_zone');
    $dispatch = $settingsObj->get('position_dispatch');

    $consult = Document::join('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
      ->join('wms_stock', function ($query) {
        $query->on('wms_document_details.id', '=',  'wms_stock.document_detail_id');
        $query->on('wms_document_details.product_id', '=', 'wms_stock.product_id');
      })
      ->join('wms_zone_positions', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
      ->join('wms_zones', 'wms_zone_positions.zone_id', '=', 'wms_zones.id')
      ->join("wms_ean_codes14 as e14", "wms_stock.code_ean14", "=", "e14.id")
      ->selectRaw("
            e14.code14 as code_ean14,
            e14.master,
            SUM(wms_stock.quanty) as quanty_14
            ")
      ->whereIn('wms_zones.name', [$dispatch, 'ZonaCajaMaster'])
      ->where('wms_documents.id', $id)
      ->groupBy("e14.id")
      ->get();

    return $consult->toArray();
  }

  public function saveServiceDataSal(Request $request)
  {
    $data = $request->all();

    $number = $data['number'];
    $company_id = $data['company_id'];
    $user_id = $data['session_user_id'];

    // $timezone = date_default_timezone_set('America/Bogota');
    // $now = Carbon::now();
    // $nowInLondonTz = Carbon::now(new DateTimeZone('America/Bogota'));

    // return $nowInLondonTz;
    // $zona->setTimezone($timezone);

    $user_ware = User::with('person.warehouse')->where('id', $user_id)->first();

    $document = Document::with(['box_driver.ean14.containers', 'detail', 'scheduleDocument.schedule_dispatch', 'detail.enlist' => function ($q) use ($number) {
      $q->whereIn('document_id', $number);
    }])->whereIn('id', $number)->get()->toArray();
    // $document = DB::table('wms_documents')
    // ->join('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
    // ->join('wms_product_ean14', 'wms_document_details.product_id', '=', 'wms_product_ean14.product_id')
    // ->join('wms_containers', 'wms_product_ean14.container_id', '=', 'wms_containers.id')
    // ->select('name', 'email as user_email')->get();
    // return $document;
    // return $document;

    // $cdDestino = Warehouse::with('distribution_center')->where('id',$document->warehouse_destination)->first();

    // $cdOrigin = Warehouse::with('distribution_center')->where('id',$document->warehouse_origin)->first();

    // $zone = Zone::where('id',$truck['schedule_receipt']['zone_id'])->first();


    $array = [];
    $settingsObj = new Settings($company_id);
    $chargeUserName = $settingsObj->get('dispatch_zone');
    $docs = [];
    foreach ($document as $value1) {
      // return $value1;
      // return $value1['schedule_document'][0]['schedule_dispatch'];
      $coopera = explode("-", $value1['number']);
      $mov = [];

      foreach ($value1['box_driver'] as $value) {
        $search_detail = DocumentDetail::where('document_id', $value['order_number'])->where('code_ean14', $value['code14_id'])->first();
        $mov[] = [
          "copera" => $coopera[0], //id de centro de operaciones que recibe mercancia
          "bodsal_mov" => $user_ware->person->warehouse->code, //codigo bodega destino de mercancia
          "lote_mov" => $search_detail['batch'], // lote
          "umedida_mov" => $value['ean14']['containers']['code'], //unidad de medida mercancia
          "cant_mov" => $value['quanty'], //cantidad recibida de la mercancia
          "barcode_mov" => $value['code14_id'], //ean14
          "additional_information" => $search_detail['additional_information']
        ];
      }

      $docs[] = [
        "numdoc_ref" => $value1['number'], //numero de documento,
        "fecha_doc" => str_replace("-", "", $value1['date']),
        "route" => $value1['route'],
        "additional_information" => $value1['additional_information'],
        "mov_" => $mov
      ];
      //     foreach ($value1['detail'] as  $value) {
      //       // return $value;
      //       // $sum = ($value['box_driver']['quanty']);

      //       $stock = Stock::with('zone_position.zone','document_detail')
      //       ->whereHas('zone_position.zone', function ($q) use ($chargeUserName)
      //       {
      //       $q->where('name', $chargeUserName);
      //       })
      //       ->where('code_ean14',$value['code_ean14'])->first();

      //       // foreach ($value['enlist'] as $enlist) {
      //         // if($enlist['document_id'] == $value['document_id']) {

      //           break;
      //         // }

      //       // }


      // }America/Bogota
      $objet = [
        "api_name" => "rstOly_sob_wms_sie_despacho",
        "copera" => $coopera[0], //id de centro de operaciones que recibe mercancia
        "fecha_despacho" => str_replace("-", "", $datetime = Carbon::now(new DateTimeZone('America/Bogota'))), //fecha creacion documento
        "bodsal" => $user_ware->person->warehouse->code, //codigo bodega origen de mercancia
        "placa_transp" => $value1['schedule_document'][0]['schedule_dispatch']['vehicle_plate'], //placa del vehiculo
        "nit_transp" => $value1['nit_transp'], //identificacion transportador
        "nomcond_transp" => $value1['schedule_document'][0]['schedule_dispatch']['driver_name'], //nombre conductor
        "idcond_transp" => $value1['schedule_document'][0]['schedule_dispatch']['driver_identification'], //cedula conductor
        "guia_transp" => $value1['guia_transp'], //guia transporte
        "peso_transp" => $value1['weight'], //peso de la mercancia,
        "docs_" => $docs
      ];
    }


    $retorno = [$objet];
    // return $retorno;
    $update_document = Document::where('id', $number)->update(['status' => 'pending_dispatch']);
    return SoberanaServices::PostReceive($retorno);
    // $respuestas = [];
    // foreach ($objet as  $value) {
    //   $retorno = [$value];
    //   // $peticion = return SoberanaServices::PostReceive($retorno);
    //    SoberanaServices::PostReceive($retorno);
    // }
    // return 'correcto';
    // if (count($mov) > 0) {


  }

  public function saveServiceDataNew(Request $request)
  {
    $data = $request->all();
    $information = array_key_exists('information', $data) ? $data['information'] : NULL;
    $truck = array_key_exists('truck', $data) ? $data['truck'] : NULL;

    $comments = "";
    foreach ($truck['schedule_comments'] as $comentario) {
      $comments .= $comentario['comment'] . ',';
    }


    // return $truck['schedule_comments'];
    $mov = [];
    $array = [];
    foreach ($information as $value2) {

      foreach ($value2['details'] as $value) {
        $cdOrigin = Warehouse::with('distribution_center')->where('id', $value['document']['warehouse_origin'])->first();
        $cdDestino = Warehouse::with('distribution_center')->where('id', $value['document']['warehouse_destination'])->first();
        $zone = Zone::where('id', $truck['schedule_receipt']['zone_id'])->first();
        $coopera = explode("-", $value['document']['number']);
        $sum = ($value['quanty_received'] + $value['is_additional'] + $value['quarantine'] + $value['damaged_cartons']);

        // $igual = $value['quanty_received'] + $value['is_additional'] + $value['quarantine'] + $value['damaged_cartons'];

        // if ($sum > 0 ) {

        $mov[] = [
          "barcode_mov" => $value['code_ean14'], //codigo bodega destino de mercancia
          "umedida_mov" => $value['product_ean14']['containers']['code'], //unidad de medida mercancia
          "lote_mov" => $value['batch'], // lote
          "cantDocumento_mov" => $value['different_batch'] ? 0 : $value['cartons'], // codigo de zona de recibo
          "cantRecibida_mov" => $sum, //centro de distribucion que envia la mercancia
          "additional_information" => $value['additional_information'], //id de centro de operaciones que recibe mercancia
        ];
        // }

        $objet = [
          "api_name" => "rstOly_sob_wms_sie_recibo",
          "transOrigen" => "tr4c3",
          "fecha_doc" => str_replace("-", "", $datetime = date("Y-m-d")), //fecha creacion documento
          "copera" => $cdDestino['distribution_center']['code'], //id de centro de operaciones que recibe mercancia
          "bodent" => $cdDestino['code'], //codigo bodega destino de mercancia
          "bodsal" => $cdOrigin['code'], //codigo bodega origen de mercancia
          "copera_origen" => $coopera[0], //centro de distribucion que envia la mercancia
          "tpdoc_origen" => $coopera[1], //tipo de documento
          "numdoc_origen" => $coopera[2], //numero de documento
          "route" => $value['document']['route'], //observaciones
          "placa_transp" => $value['document']['vehicle_plate'], //placa del vehiculo
          "nit_transp" => $value['document']['nit_transp'], //identificacion transportador
          "nomcond_transp" => $value['document']['driver_name'], //nombre conductor
          "idcond_transp" => $value['document']['driver_identification'], //cedula conductor
          "guia_transp" => $value['document']['guia_transp'], //guia transporte
          "peso_transp" => $value['document']['weight'], //peso de la mercancia
          "additional_information" => $value['document']['additional_information'],
          "mov_" => $mov
        ];
      }
    }
    $retorno = [
      new \stdClass(), $objet, new \stdClass()
    ];

    // return $retorno;
    return SoberanaServices::PostReceive($retorno);

    // return $config->toArray();

  }

  public function createStock(Request $request)
  {
    $data = $request->all();
    $stock = array_key_exists('stock', $data) ? $data['stock'] : NULL;
    $companyId = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;

    $position = $stock['position'];
    $ean13 = $stock['ean13'];
    $quanty = $stock['quanty'];

    $zonePosition = ZonePosition::where('code', $position)->first();
    if (!$zonePosition) {
      throw new InvalidArgumentException("No se encontró una posición para el código ingresado");
    }

    $product = Product::where('ean', $ean13)->first();
    if (!$product) {
      throw new InvalidArgumentException("No se encontró un producto para el ean ingresado");
    }

    $stock = Stock::where('product_id', $product->id)->where('zone_position_id', $zonePosition->id)->where('quanty', '>', 0)->first();

    DB::beginTransaction();
    try {
      if ($stock) {
        $stock->quanty = $stock->quanty + $quanty;
        $stock->save();
      } else {
        Stock::create([
          'product_id' => $product->id,
          'zone_position_id' => $zonePosition->id,
          'quanty' => $quanty
        ]);
      }
      DB::commit();
      return response('Producto ingresado con éxito', Response::HTTP_CREATED);
    } catch (Exception $e) {
      DB::rollBack();
      return response($e->getMessage(), Response::HTTP_CONFLICT);
    }
  }

  public function saveServiceDataInventary(Request $request)
  {
    $data = $request->all();
    $stock = Stock::where('zone_position_id', $data['valor']['zone_position_id'])->where('product_id', $data['valor']['product_id'])->first();
    if ($stock) {
      Stock::where('zone_position_id', $data['valor']['zone_position_id'])->where('product_id', $data['valor']['product_id'])->update(['quanty' => intval($data['valor']['isSelected'])]);
      $stockP = Stock::where('zone_position_id', $data['valor']['zone_position_id'])->where('product_id', $data['valor']['product_id'])->first();

      if ($stockP) {
        if ($stockP->quanty == 0) {
          $stockP->delete();
        }
      }
    }
    return $stock;


    $consul = ScheduleStock::where('schedule_id', $data['valor']['schedule_id'])->first();
    $user = User::whereHas('person', function ($q) use ($consul) {
      $q->where('id', $consul->persona_id);
    })
      ->first();

    $consult_container = ProductEan14::with('containers')->where('code_ean14', $data['valor']['code_ean14'])->first();

    $stock = Stock::with('document_detail')->where('zone_position_id', $data['valor']['zone_position_id'])->first();
    // return $stock->document_detail->batch;

    $zone = ZonePosition::with('zone.warehouse.distribution_center')->where('id', $data['valor']['zone_position_id'])->first();
    // $mov = [];
    // $coopera = $zone->zone->warehouse->distribution_center->code;
    // // $sum = ($value['quanty_received'] + $value['is_additional'] + $value['quarantine'] + $value['damaged_cartons']);

    // // $igual = $value['quanty_received'] + $value['is_additional'] + $value['quarantine'] + $value['damaged_cartons'];

    // // if ($sum > 0 ) {

    // $mov[] = [
    //     "barcode_mov" => $data['valor']['code_ean14'],//codigo bodega destino de mercancia
    //     "umedida_mov" => $consult_container->containers->code,//unidad de medida mercancia
    //     "lote_mov" => $stock->document_detail->batch,// lote
    //     "cant_teorica" => $data['valor']['quanty'],// codigo de zona de recibo
    //     "cant_real" => $data['valor']['found'],//centro de distribucion que envia la mercancia
    // ];
    // // }

    // $objet = [
    //     "api_name" => "rstOly_sob_wms_sie_inventario",
    //     "transOrigen" => "tr4c3",
    //     "copera" => $coopera, //id de centro de operaciones que recibe mercancia
    //     "bodega_inv" => $zone->zone->warehouse->code,
    //     "fecha_inv" => str_replace("-", "", $datetime = date("Y-m-d")),//fecha creacion documento
    //     "usuario" => $user->username,//codigo bodega destino de mercancia
    //     "mov_" => $mov
    // ];
    // // }

    // // }
    // $retorno = [
    //     new \stdClass(), $objet, new \stdClass()
    // ];

    // // return $retorno;
    // return SoberanaServices::PostReceive($retorno);

    // return $config->toArray();

  }

  public function inventary_stock(Request $request)
  {
    $productType = $request->input('product_type_id');
    $warehouse = $request->input('warehouse_id');
    $reference = $request->input('reference');
    $ean128 = $request->input('ean128');
    $ean14 = $request->input('ean14');
    $ean13 = $request->input('ean13');
    $client = $request->input('client_id');
    $position = $request->input('zone_position_id');
    $schedule = $request->input('schedule_id');
    $companyId = $request->input('company_id');

    // return 85;


    $stock = DB::table('wms_stock')
      ->join('wms_products', 'wms_stock.product_id', '=', 'wms_products.id')
      ->join('wms_zone_positions', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
      ->join('wms_zones', 'wms_zone_positions.zone_id', '=', 'wms_zones.id')
      ->leftjoin('wms_ean_codes128', 'wms_stock.code128_id', '=', 'wms_ean_codes128.id')
      ->leftjoin('wms_stock_counts', 'wms_stock.id', '=', 'wms_stock_counts.stock_id')
      ->where('wms_zones.name', '!=', 'Zona Despacho')
      ->where('wms_zones.name', '!=', 'Zona de Empaque')
      ->select('wms_products.description', 'wms_zone_positions.code', 'wms_zones.name', 'wms_ean_codes128.code128', 'wms_stock.code_ean14', 'wms_products.ean', 'wms_products.reference', DB::raw('SUM(quanty_14) as quanty_14'), 'wms_products.id as product_id', 'wms_zone_positions.id as zone_position_id', 'wms_stock.id as stock_id', 'wms_ean_codes128.id as code128_id', 'wms_zones.warehouse_id');


    // $stock;

    if (isset($warehouse)) {

      $stock->where('wms_zones.warehouse_id', $warehouse);
    }

    if (isset($reference)) {

      $stock->where('wms_products.reference', $reference);
    }


    // $stock = $stock->get();
    $arrStock = $stock->groupBy('wms_stock.product_id')->groupBy('wms_stock.zone_position_id')->get();

    //Check if the counts belongs to the same schedule
    // if(isset($schedule)) {
    //   foreach ($arrStock as &$stocksini) {
    //     if(array_key_exists('stock_count', $stocksini) && $stocksini['stock_count']['schedule_id'] != $schedule) {
    //       $stocksini['stock_count'] = null;
    //     }
    //   }
    // }

    return $arrStock;
  }

  public function print_pallets(Request $request)
  {

    $pallet = EanCode128::with('pallet', 'stock.zone_position')
      ->has('stock')
      ->get();
    $objeto = [];
    $sum = 0;
    foreach ($pallet as $value) {

      foreach ($value['pallet'] as $value1) {
        $sum = $value1['quanty'];
      }
      // foreach ($value['stock'] as $value2) {
      $position = $value['stock']['zone_position']['code'];
      // }
      $objeto[] = [
        "code128" => $value['code128'],
        "quanty_box" => $sum,
        "position" => $position
      ];
    }

    return $objeto;
  }

  public function getProductsAll(Request $request)
  {

    $pallet = Product::get();

    return $pallet->toArray();
  }

  public function createEan14(Request $request)
  {
    $data = $request->all();

    // return $data;
    $stock = array_key_exists('stock', $data) ? $data['stock'] : NULL;
    $companyId = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $packaging_type = 'empaque';

    $consult_quanty = ProductEan14::with('product')->where('product_id', $stock['product'])->first()->toArray();
    //  return $consult_quanty;

    // $consult_position = ZonePosition::where('code',$stock['position'])->first();

    $settingsObj = new Settings($companyId);

    //Consultamos la estrucutra para el tipo de embalaje
    // $structurecodes = Codes::GetStructureCode($packaging_type);
    // // return $structurecodes;
    $new14 = '';
    // $ia_code= '';
    // foreach ($structurecodes as  $value) {
    //   $ia_code .= $value['ia_code']['code_ia'].',';
    // }
    // $ia_code = explode(",",$ia_code);
    $new14 .= $consult_quanty['code_ean14'];

    $barcode = [];
    for ($i = 0; $i < $stock['quanty']; $i++) {
      $barcode[] = [
        "barcode" => preg_replace('/\(|\)/', '', $new14),
        "ean14" => $consult_quanty['code_ean14'],
        "description" => $consult_quanty['product']['description']
      ];
    }


    // return $document_detail['id'];

    $newstructurecode14 = str_random(7);


    return $barcode;
  }

  public function validate_close_task($id)
  {
    $schedule = Schedule::where('id', $id)->first();
    if ($schedule) {
      $schedule_parent = Schedule::where('parent_schedule_id', $schedule->parent_schedule_id)->where('schedule_action', 'remove')->first();
    }

    return $schedule_parent->toArray();
  }

  public function saveProducts(Request $request)
  {
    $data = $request->all();
    $document = array_key_exists('documento', $data) ? $data['documento'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    // $users = User::where('id',)->first();
    $productos = $document['products'];
    $number = " ";

    $validate_number = Document::orderBy('id', 'desc')->first();
    if (!$validate_number) {
      $number = $document['client_id']['name'] . "-" . "1";
    } else {
      $number = $document['client_id']['name'] . "-" . $validate_number->id;
    }

    if ($document['receipt_type_id']['name'] === "Sales order") {
      $documentObjet = [
        "number" => $number,
        "active" => 1,
        // "min_date"=>$document['fecha_minima'],
        "max_date" => $document['fecha_maxima'],
        "client" => $document['client_id']['name'],
        "receipt_type_id" => $document['receipt_type_id']['id'],
        "document_type" => "departure",
        "observation" => isset($document['observation']) ? $document['observation'] : NULL,
        "address" => $document['address'],
        "company_id" => $company_id
      ];
    } else {
      $documentObjet = [
        "number" => $number,
        "active" => 1,
        // "min_date"=>$document['min_date'],
        "max_date" => $document['fecha_maxima'],
        "client" => $document['client_id']['name'],
        "receipt_type_id" => $document['receipt_type_id']['id'],
        "observation" => isset($document['observation']) ? $document['observation'] : NULL,
        "company_id" => $company_id
      ];
    }


    $insert_document = Document::create($documentObjet);

    foreach ($productos as $value) {
      $ean14 = ProductEan14::where('product_id', $value['id'])->first();
      if ($document['receipt_type_id']['name'] === "Pedido") {
        $productObjet = [
          "document_id" => $insert_document->id,
          "quanty" => $value['unit'],
          "product_id" => $value['id'],
          "client_id" => $document['client_id']['id'],
          "client" => $document['client_id']['name'],
          "unit" => $value['unit'],
          "code_ean14" => $ean14['code_ean14'],
          "cartons" => $value['unit']
        ];

        $insert_products = DocumentDetail::create($productObjet);
      } else {
        $ean14 = ProductEan14::where('product_id', $value['id'])->first();
        // if (isset($value['presentation'])) {
        $productObjet = [
          "document_id" => $insert_document->id,
          "quanty" => $value['unit'],
          "product_id" => $value['id'],
          "client_id" => $document['client_id']['id'],
          "client" => $document['client_id']['name'],
          "unit" => $value['unit'],
          "code_ean14" => $ean14['code_ean14'],
          "cartons" => $value['unit']
        ];

        $insert_products = DocumentDetail::create($productObjet);
        // }else {
        //   return $this->response->error('alert_ean14_product', 404);
        // }
      }
    }

    return $insert_document->toArray();
  }

  public function getClientsByOrder(Request $request)
  {
    $data = $request->all();

    $order_type = $data['order'];

    $order_type = array_key_exists('order', $data) ? $data['order'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    // $schedule = array_key_exists('schedule', $data) ? $data['schedule'] : NULL;

    if ($order_type === 'Recibo De Mercancía') {
      $client = Brand::where('company_id', $company_id)->get();
    } else {
      $client = Client::where('company_id', $company_id)->get();
    }
    return $client->toArray();
  }

  public function getProductsByType(Request $request, $type)
  {
    $products = Product::with('product_sub_type', 'product_ean14s.product', 'stock')->whereHas('product_sub_type', function ($q) use ($type) {
      $q->where('product_type_id', $type);
    })->get();

    return $products->toArray();
  }

  public function deleteDocument($id)
  {
    $products = Document::where('id', $id)->delete();

    DocumentDetail::where('document_id', $id)->delete();
  }

  public function getZonesCategory(Request $request)
  {
    $zones = Zone::get();
    return $zones->toArray();
  }

  public function getThirdByType($type)
  {
    return Client::where('third', $type)->get()->toArray();
  }

  public function getCountries()
  {
    return Country::get()->toArray();
  }

  public function getCities(Request $request)
  {
    $data = $request->all();
    $country = array_key_exists('country', $data) ? $data['country'] : null;
    $district = array_key_exists('district', $data) ? $data['district'] : null;
    if ($country) {
      return City::where('country_code', $country)->get()->toArray();
    } else {
      return City::where('district', $district)->get()->toArray();
    }
  }

  public function getThirdsBySearch(Request $request)
  {
    $data = $request->all();
    if ($data['type'] == 'sales') {
      return DB::table('wms_clients as c')
        ->join('countries as customer_country', 'customer_country.id', '=', 'c.customer_country_id')
        ->join('countries as shipping_country', 'shipping_country.id', '=', 'c.shipping_country_id')
        ->join('cities as customer_city', 'customer_city.id', '=', 'c.customer_city_id')
        ->join('cities as shipping_city', 'shipping_city.id', '=', 'c.shipping_city_id')
        ->select(
          'c.id',
          'c.customer_names as names',
          'c.customer_last_names as last_names',
          'c.customer_street as street',
          'c.customer_street_2 as street_2',
          'c.shipping_names',
          'c.shipping_last_names',
          'c.shipping_street',
          'c.shipping_street_2',
          'customer_country.name as country',
          'shipping_country.name as shipping_country',
          'customer_city.name as city',
          'shipping_city.name as shipping_city',
          'c.customer_zip_code as zip_code',
          'c.shipping_zip_code as shipping_zip_code',
          'c.type'
        )
        ->where('customer_names', 'LIKE', '%' . $data['search'] . '%')
        ->where('third_type', 'client')
        ->get();
    } else {
      return DB::table('wms_clients as c')
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
        ->where('company_name', 'LIKE', '%' . $data['search'] . '%')
        ->where('third_type', 'vendor')
        ->get();
    }
  }

  public function saveRequisition(Request $request)
  {
    $data = $request->all();
    $details = $data['details'];
    $rData = $data['requisition'];
    $dData = [];
    $ddData = [];
    $totalCost = 0;
    $totalAverageCost = 0;
    $totalCost = 0;
    foreach ($details as $detail) {
      $dData[] = [
        'product_id' => $detail['id'],
        'quantity' => $detail['quantity'],
        'total_price' => $detail['total']
      ];
      $totalCost += $detail['total'];
      $totalAverageCost += \Illuminate\Support\Facades\DB::table('wms_product_features')
        ->select('value')
        ->where('product_id', $detail['id'])
        ->where('feature_id', 62)
        ->first()->value;
      $ean14 = \Illuminate\Support\Facades\DB::table('wms_product_ean14')
        ->select('code_ean14')
        ->where('product_id', $detail['id'])->first();
      $ddData[] = [
        'product_id' => $detail['id'],
        'client_id' => $rData['third']['id'],
        'quanty' => $detail['quantity'],
        'cartons' => $detail['quantity'],
        'code_ean14' => $ean14->code_ean14,
        'unit' => $detail['quantity']
      ];
    }
    $rData['client_id'] = $rData['third']['id'];
    $rData['total_price'] = $rData['total'];
    $rData['status'] = 'pending';
    $requisition = Requisition::create($rData);
    $requisition->details()->createMany($dData);

    $totalBenefit = $totalCost - $totalAverageCost;
    $documentData = [
      'client' => $rData['third']['shipping_names'],
      'number' => $rData['third']['shipping_names'] . '-' . $requisition->id,
      'document_type' => 'departure',
      'status' => 'pending',
      'address' => $rData['third']['shipping_street'] . ' ' . $rData['third']['shipping_street_2'],
      'max_date' => date("Y/m/d"),
      'active' => 1,
      'type' => $rData['type'],
      'company_id' => 21,
      'total_cost' => $totalCost,
      'total_benefit' => $totalBenefit
    ];
    $document = Document::create($documentData);
    $document->detail()->createMany($ddData);
  }

  public function getRequisitions()
  {
    return Requisition::with('details', 'client', 'client.customerCity')->get()->toArray();
  }

  public function picking(Request $request)
  {
    $data = $request->all();

    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    $dispatch = $settingsObj->get('dispatch_zone');
    $zone_position = DB::table('wms_zones')
      ->join('wms_zone_positions', 'wms_zones.id', '=', 'wms_zone_positions.zone_id')
      ->where('wms_zones.name', $dispatch)
      ->select('wms_zone_positions.id')->first();
    // return $zone_position->id;

    $datos = array_key_exists('data', $data) ? $data['data'] : NULL;
    $schedule_id = array_key_exists('schedule_id', $data) ? $data['schedule_id'] : NULL;
    $parent = Schedule::where('id', $schedule_id)->first();
    // return $parent;
    $search_position = ZonePosition::where('code', $datos['position'])->first()->toArray();
    if ($search_position) {
      $search_stock = Stock::where('zone_position_id', $search_position['id'])->first();

      $search_enlist = EnlistProducts::where('product_id', $search_stock['product_id'])->where('schedule_id', $parent->parent_schedule_id)->first();
      // return $search_stock;
      if ($search_stock) {
        if ($search_stock->quanty < $datos['amount']) {
          return $this->response->error('less_amount', 404);
        } else if ($datos['amount'] > $search_enlist->quanty) {
          return $this->response->error('more_amount', 404);
        } else {
          $search_stock->decrement('quanty', $datos['amount']);
          $search_stock->decrement('quanty_14', $datos['amount']);
          $search_enlist->increment('picked_quanty', $datos['amount']);
          $objeto = [
            "product_id" => $search_stock->product_id,
            "zone_position_id" => $zone_position->id,
            "code128_id" => $search_stock->code128_id,
            "quanty" => $datos['amount'],
            "active" => 1,
            "code_ean14" => $search_stock->code_ean14,
            "document_detail_id" => $search_stock->document_detail_id,
            "quanty_14" => $datos['amount'],
          ];
          Stock::create($objeto);
        }
      }
    }
    return $data;
  }

  public function createService(Request $request)
  {
    $data = $request->all();
    $data['vendor_id'] = $data['vendor']['id'];
    Services::create($data);
  }

  public function getServices()
  {
    return Services::get();
  }

  public function getServicesBySearch(Request $request)
  {
    $data = $request->all();
    return Services::where('name', 'LIKE', '%' . $data['search'] . '%')
      ->orWhere('item_number', $data['search'])
      ->get()->toArray();
  }

  public function updateService(Request $request)
  {
    $data = $request->all();
    $data['vendor_id'] = $data['vendor']['id'];
    unset($data['vendor']);
    unset($data['session_user_id']);
    unset($data['company_id']);

    Services::where('id', $data['id'])
      ->update($data);
  }

  public function getServiceById($id)
  {
    $service = Services::findOrFail($id);
    $service->vendor = DB::table('wms_clients as c')
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
      ->where('c.id', $service->vendor_id)
      ->first();

    return $service->toArray();
  }

  public function getStates()
  {
    $district = DB::table('cities')
      ->distinct('district')
      ->select('district')
      ->where('country_code', 'USA')
      ->get();

    //        $district = array_map(function ($d){
    //            return $d->district;
    //        }, $district);

    return $district;
  }

  public function enterSerial(Request $request)
  {
    $data = $request->all();
    $params = array_key_exists('params', $data) ? $data['params'] : NULL;
    $serial = array_key_exists('serial', $data) ? $data['serial'] : NULL;

    $objeto = [
      "serial" => $serial,
      "product_id" => $params['product']['id'],
      "document_id" => $params['document_id']
    ];
    $validate = EanCode14Serial::where('product_id', $params['product']['id'])->where('document_id', $params['document_id'])->where('serial', $serial)->first();
    if (!$validate) {
      EanCode14Serial::create($objeto);
    } else {
      return $this->response->error('The serial number already exist', 404);
    }
    return $params;
  }

  public function saveTulas(Request $request)
  {
    $data = $request->all();
    $params = array_key_exists('params', $data) ? $data['params'] : NULL;
    // return $params;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    // $objeto = [];
    // foreach ($params as  $value) {
    $objeto = [
      "document_id" => $params[0]['document_id'],
      "code14" => $params[0]['seal'],
      "facturation_number" => isset($params[0]['facturation_number']) ? $params[0]['facturation_number'] : '',
      "observation_auditor" => isset($params[0]['observation_auditor']) ? $params[0]['observation_auditor'] : '',
      "company_id" => $company_id
    ];
    // }


    $code14 = EanCode14::create($objeto);
    // return $code14;

    // $encabe14 = EanCode14::where('document_detail_id',$params['id'])->first();
    // $detail14 = EanCode14Detail::where('ean_code14_id',$encabe14->id)->first();

    // return $detail14->good;


    foreach ($params as  $value) {
      $sum = $value['quanty_received'];
      DocumentDetail::where('id', $value['id'])->update(['quanty_received_pallet' => $sum]);
      // $encabe14 = EanCode14::where('document_detail_id',$value['id'])->first();
      $detail14 = EanCode14Detail::where('document_detail_id', $value['id'])->first();
      if ($detail14) {
        $objeto_d = [
          "ean_code14_id" => $code14->id,
          "product_id" => $value['product_id'],
          "quanty" => $value['quanty_received'] - $detail14->quanty,
          "good" => $value['good'] - $detail14->good,
          "seconds" => $value['seconds'] - $detail14->seconds,
          "sin_conf" => $value['sin_conf'] - $detail14->sin_conf,
          "document_detail_id" => $value['id']
        ];

        EanCode14Detail::create($objeto_d);
      } else {
        $objeto_d = [
          "ean_code14_id" => $code14->id,
          "product_id" => $value['product_id'],
          "quanty" => $value['quanty_received'],
          "good" => $value['good'],
          "seconds" => $value['seconds'],
          "sin_conf" => $value['sin_conf'],
          "document_detail_id" => $value['id']
        ];

        EanCode14Detail::create($objeto_d);
      }
    }

    $detalle = Document::with('detail.product')->where('id', $params[0]['document_id'])->get()->toArray();
    return $detalle;
  }

  public function getDocumentsMaaji(Request $request)
  {
    $document = Document::with('detail.product', 'detail.ean14.detail', 'clientdocument')->get();
    return $document->toArray();
  }

  public function getDocumentsProcessMaaji(Request $request)
  {
    $document = Document::with('ean14', 'ean14.detail', 'ean14.document', 'clientdocument')
      ->where("status", "process")
      ->where('document_type', 'receipt')
      ->get();
    return $document->toArray();
  }

  public function updateReceiptTulas(Request $request)
  {
    $data = $request->all();
    $params = array_key_exists('params', $data) ? $data['params'] : NULL;
    $sum = $params['good'] + $params['seconds'] + $params['sin_conf'];
    DocumentDetail::where('id', $params['id'])->update(['quanty_received' => $sum, 'good' => $params['good'], 'seconds' => $params['seconds'], 'sin_conf' => $params['sin_conf']]);
    return $params;
  }

  public function getpersonalMaaji(Request $request)
  {
    $document = Person::with('user')->has('user')->get();
    return $document->toArray();
  }

  /**
   *
   *  Creación de recogida tulas 1.3 HU
   * @author Julian Osorio
   */
  public function CreateTaskTulas(Request $request)
  {
    $data = $request->all();
    $params = array_key_exists('params', $data) ? $data['params'] : NULL;
    $user = array_key_exists('params', $data) ? $data['user'] : NULL;
    $documentArray = $params['documentsArray'];
    DB::beginTransaction();
    try {
      foreach ($documentArray as $value) {
        $documentModel = Document::with('clientdocument')->find($value['id']);
        $taskSchedulesW = [];
        $taskSchedulesW = [
          'start_date' => $params['start_date'],
          'name' => 'Recoger O.P:' . $documentModel->clientdocument->name . ' ' . $documentModel->number,
          'schedule_type' => ScheduleType::Receipt,
          'schedule_action' => ScheduleAction::CollectTulas,
          'status' => ScheduleStatus::Process,
          'user_id' => $user["id"],
          'parent_schedule_id' => $documentModel->id,
          'company_id' => $user["company_id"]
        ];
        Schedule::create($taskSchedulesW);
      }
      DB::commit();
      return response('Tarea creada con exito', Response::HTTP_OK);
    } catch (Exception $e) {
      DB::rollBack();
      return response($e->getMessage(), Response::HTTP_CONFLICT);
    }
  }

  public function getTulasMaaji($id)
  {
    $tulas = EanCode14::with('detail.product', 'document')
      ->where('document_id', $id)
      // ->where('status', '1')
      ->get();
    return $tulas->toArray();
  }

  public function getTulasMaajiCollect($id)
  {
    $tulas = EanCode14::with('detail.product', 'document')
      ->where('document_id', $id)
      // ->where('status', 2)
      ->get();
    return $tulas->toArray();
  }

  public function getTulasMaajiReceived($documentId)
  {
    $tulas = EanCode14::with('detail.product', 'document')
      ->where('wms_ean_codes14.document_id', $documentId)
      ->get();
    return $tulas->toArray();
  }

  public function getTulasMaajiFinish($id)
  {
    $tulas = EanCode14::with('detail.product', 'document')
      ->where('document_id', $id)
      ->where('status', '11')
      ->get();
    return $tulas->toArray();
  }

  public function ActiveDocument(Request $request)
  {
    $data = $request->all();
    $documents = array_key_exists('documents', $data) ? $data['documents'] : NULL;
    DB::beginTransaction();
    try {
      foreach ($documents as $value) {
        Document::where('id', $value["id"])->update(['active' => 1, "income_sizfra" => $value["consecutivo"]]);
        $codigoEan = EanCode14::where('document_id', $value["id"])->get();
        foreach ($codigoEan as $ean) {
          if ($ean->consecutive == '' || $ean->consecutive == null) {
            $code = EanCode14::find($ean->id);
            $code->consecutive = $value["consecutivo"];
            if ($code->status == 1) {
              $code->status = 2;
            }
            $code->save();
          }
        }
      }
      DB::commit();
      return response(["message" => 'Se ha creado la tarea para validar el transporte'], Response::HTTP_OK);
    } catch (\Exception $e) {
      DB::rollBack();
      return $this->response->error($e->getMessage(), 404);
    }
  }

  public function chengeTulas(Request $request)
  {
    $data = $request->all();
    $params = array_key_exists('params', $data) ? $data['params'] : NULL;
    $documentId = $params[0]['document']['id'];
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $consecutive = array_key_exists('consecutive', $data) ? $data['consecutive'] : NULL;

    $numberDocument = $params ? $params[0]['document']['number'] : '';
    $client = Client::where('id', $params[0]['document']['client'])->first();

    $settingsObj = new Settings($company_id);
    $chargeUserName = $settingsObj->get('validate_charge');

    $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
      $q->where('name', $chargeUserName);
    })->first();

    if (empty($user)) {
      return $this->response->error('user_not_found', 404);
    }



    $taskSchedulesW = [
      // 'start_date' => $params['start_date'],
      // 'end_date' => $params['end_date'],
      'name' => 'Crear cita de recibo: ' . $client->name . ' ' . $numberDocument,
      'schedule_type' => ScheduleType::Receipt,
      'schedule_action' => ScheduleAction::ReceiptSchedule,
      'status' => ScheduleStatus::Process,
      'user_id' => $user->id,
      'parent_schedule_id' => $documentId,
      'company_id' => $company_id
    ];
    $scheduleW = Schedule::create($taskSchedulesW);

    $chargeUserNameT = $settingsObj->get('intermediario');

    $userT = User::whereHas('person.charge', function ($q) use ($chargeUserNameT) {
      $q->where('name', $chargeUserNameT);
    })->first();

    if (empty($userT)) {
      return $this->response->error('user_not_found_t', 404);
    }

    $taskSchedulesWT = [
      // 'start_date' => $params['start_date'],
      // 'end_date' => $params['end_date'],
      'name' => 'Asignar conductor recogida: ' . $client->name . ' ' . $numberDocument,
      'schedule_type' => ScheduleType::Receipt,
      'schedule_action' => ScheduleAction::Recogida,
      'status' => ScheduleStatus::Process,
      'user_id' => $userT->id,
      'parent_schedule_id' => $documentId,
      'company_id' => $company_id
    ];
    $scheduleWT = Schedule::create($taskSchedulesWT);

    foreach ($params as $value) {
      EanCode14::where('id', $value['id'])->update(['status' => 2, 'schedule_id' => $scheduleW->id, 'consecutive' => $consecutive]);
    }
    return  $params;
  }

  public function get14DetailById(Request $request)
  {

    $data = $request->all();
    $document_id = array_key_exists('document_id', $data) ? $data['document_id'] : NULL;
    $schedule_id = array_key_exists('schedule_id', $data) ? $data['schedule_id'] : NULL;

    $array = EanCode14::with('detail_m.product')->where('document_id', $document_id)->where('schedule_id', $schedule_id)->where('status', '11')->get()->toArray();

    // $array = EanCode14::from('wms_ean_codes14 as e14')
    // ->join('wms_ean_codes14_detail as e14d', 'e14.id', '=', 'ean_code14_id')
    // ->join('wms_products as p', 'e14d.id', '=', 'product_id')
    // ->where('document_id',$document_id)
    // ->where('schedule_id',$schedule_id)
    // ->where('status', '11')->get()->toArray();

    // $documents = EanCode14Detail::findOrFail($params['detail']['id']);
    // $documento = DocumentDetail::with('ean14.detail.product')->where('document_id',$document_id)->get()->toArray();
    // $array = [];
    // foreach ($documento as $value) {
    //     foreach ($value['ean14'] as  $value1) {
    //         $value1['good_receive'] =$value1['detail']['good_receive'];
    //         $value1['seconds_receive'] =$value1['detail']['seconds_receive'];
    //         $value1['sin_conf_receive'] =$value1['detail']['sin_conf_receive'];
    //         $value1['quanty_receive'] =$value1['detail']['quanty_receive'];
    //         if ($value1['status']===2 && $value1['schedule_id'] == $schedule_id) {
    //             $array []= $value1;
    //         }
    //     }
    // }
    return $array;
  }

  public function update14DetailQuantyReceived(Request $request)
  {

    DB::beginTransaction();
    try {
      $data = $request->all();
      $params = array_key_exists('params', $data) ? $data['params'] : NULL;
      $documents = EanCode14Detail::findOrFail($params['ean_code14_detail_id']);
      $documents->good_receive = array_key_exists('good_receive', $params) ? $params['good_receive'] : $documents->good_receive;
      $documents->seconds_receive = array_key_exists('seconds_receive', $params) ? $params['seconds_receive'] : $documents->seconds_receive;
      $documents->sin_conf_receive = array_key_exists('sin_conf_receive', $params) ? $params['sin_conf_receive'] : $documents->sin_conf_receive;
      $documents->quanty_receive = array_key_exists('quanty_receive', $params) ? $params['quanty_receive'] : $documents->quanty_receive;
      $documents->save();
      $detail = DocumentDetail::findOrFail($params['document_detail_id']);
      $detail->quanty_received = array_key_exists('quanty_receive', $params) ? $params['quanty_receive'] : $documents->quanty_receive;
      $detail->save();
      DB::commit();
      // return $this->response->noContent();
      return response('Producto ingresado con éxito', Response::HTTP_CREATED);
    } catch (Exception $e) {
      DB::rollBack();
      return response($e->getMessage(), Response::HTTP_CONFLICT);
    }
  }

  public function CreateTaskTulasOp(Request $request)
  {
    $data = $request->all();
    $params = array_key_exists('params', $data) ? $data['params'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    //    return $params;
    foreach ($params['schedule_documents'] as  $value) {
      $taskSchedulesW = [
        'start_date' => $params['start_date'],
        'end_date' => $params['end_date'],
        'name' => 'Entregar OPs:' . $value['client'] . ' ' . $value['number'],
        'schedule_type' => ScheduleType::Receipt,
        'schedule_action' => ScheduleAction::ReceiptScheduleOp,
        'status' => ScheduleStatus::Process,
        'user_id' => $params['users']['user']['id'],
        'parent_schedule_id' => $value['id'],
        'company_id' => $company_id
      ];
      $scheduleW = Schedule::create($taskSchedulesW);
    }
    return $params;
  }

  public function getDetailDocuments($id)
  {
    $documento = DocumentDetail::with('product', 'document')->where('document_id', $id)->get()->toArray();

    return $documento;
  }

  public function chengeTulasConsecutive(Request $request)
  {
    $data = $request->all();
    $id = array_key_exists('id', $data) ? $data['id'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $schedule_id = array_key_exists('schedule_id', $data) ? $data['schedule_id'] : NULL;

    $ean14Document = EanCode14::where('schedule_id', $schedule_id)->first();

    $settingsObj = new Settings($company_id);
    // $validate_charge = $settingsObj->get('validate_charge');
    $documento = Document::where('id', $ean14Document->document_id)->first();

    $ean14 = EanCode14::where('document_id', $ean14Document->document_id)->where('schedule_id', $schedule_id)->where('status', 2)->get();

    foreach ($ean14 as $value) {
      ScheduleEAN14::create([
        'schedule_id' => $schedule_id,
        'ean14_id' => $value['id']
      ]);
    }

    // $this->recogerOpsTemporary($ean14Document->document_id, $company_id);

    $chargeUserName = $settingsObj->get('assistant_cedi');

    $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
      $q->where('name', $chargeUserName);
    })->first();

    // return $user;

    if (empty($user)) {
      return $this->response->error('user_not_found', 404);
    }

    $client = Client::where('id', $documento['client'])->first();

    // return $user;
    $taskSchedulesW = [
      // 'start_date' => $params['start_date'],
      // 'end_date' => $params['end_date'],
      'name' => 'Validar tulas:' . $client->name . ' ' . $documento['number'],
      'schedule_type' => ScheduleType::Receipt,
      'schedule_action' => ScheduleAction::ReceiptValidateTulas,
      'status' => ScheduleStatus::Process,
      'user_id' => $user->id,
      'parent_schedule_id' => $schedule_id,
      'company_id' => $company_id
    ];
    // return $taskSchedulesW;
    $scheduleW = Schedule::create($taskSchedulesW);
    EanCode14::where('schedule_id', $schedule_id)->update(['schedule_id' => $scheduleW->id]);
    ScheduleEAN14::where('schedule_id', $schedule_id)->update(['schedule_id' => $scheduleW->id]);
    return $schedule_id;

    //    return $params;
  }

  public function recogerOpsTemporary(Request $request)
  {
    $data = $request->all();
    $documentId = array_key_exists('document_id', $data) ? $data['document_id'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $tulas = EanCode14::where('document_id', $documentId)->where('status', 1)->selectRaw('id, observation_auditor')->get();
    // $settingsObj = new Settings($company_id);
    $documento = Document::where('id', $documentId)->first();

    // $chargeUserName = $settingsObj->get('conductor');

    // $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
    //     $q->where('name', $chargeUserName);
    // })->first();

    // if (empty($user)) {
    //     return $this->response->error('user_not_found', 404);
    // }

    $client = Client::where('id', $documento['client'])->first();

    $taskSchedulesW = [
      'name' => 'Recoger OPs:' . $client->name . ' ' . $documento['number'],
      'schedule_type' => ScheduleType::Receipt,
      'schedule_action' => ScheduleAction::ReceiptTulas,
      'status' => ScheduleStatus::Process,
      'user_id' => $data['user_id'],
      'parent_schedule_id' => $documentId,
      'company_id' => $company_id
    ];

    $scheduleW = Schedule::create($taskSchedulesW);

    foreach ($tulas as $value) {
      ScheduleEAN14::create([
        'schedule_id' => $scheduleW->id,
        'ean14_id' => $value['id']
      ]);
    }
  }

  public function getTulasById($id)
  {
    $array = EanCode14::with('detail_m.product')->where('id', $id)->first()->toArray();
    return $array;
  }

  public function finishOp($id)
  {
    Document::where('id', $id)->update(['active' => 2, 'status' => 'closed', 'additional_information' => date('Y-m-d')]);
    return $id;
  }

  /**
   * @author Julian Osorio y Romario
   */
  public function createValidateTask(Request $request)
  {
    $data = $request->all();
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $id = array_key_exists('id', $data) ? $data['id'] : NULL;
    $tulas = array_key_exists('tulas', $data) ? $data['tulas'] : NULL;
    $scheduleId = array_key_exists('scheduleId', $data) ? $data['scheduleId'] : NULL;
    $settingsObj = new Settings($company_id);

    DB::beginTransaction();
    try {
      //Se actualiza las tulas con las observaciones y el estado
      foreach ($tulas as $value) {
        if (isset($value['receipt']) && $value['receipt'] == 1) {
          EanCode14::where('id', $value['id'])->update(['status' => 10, 'observation_driver' => $value['observation_driver']]);
        }
      }
      $chargeUserName = $settingsObj->get('intermediario');
      $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
        $q->where('name', $chargeUserName);
      })->first();
      if (empty($user)) {
        return $this->response->error('user_not_found', 404);
      }
      $documento = Document::where('id', $id)->first();
      $client = Client::where('id', $documento['client'])->first();
      $taskSchedulesW = [
        'name' => 'Validar recibo tulas:' . $client->name . ' ' . $documento['number'],
        'schedule_type' => ScheduleType::Receipt,
        'schedule_action' => ScheduleAction::ReceiptValidateTulas,
        'status' => ScheduleStatus::Process,
        'user_id' => $user->id,
        'parent_schedule_id' => $id,
        'company_id' => $company_id
      ];
      Schedule::create($taskSchedulesW);
      DB::commit();
      return response('Producto ingresado con éxito', Response::HTTP_CREATED);
    } catch (Exception $e) {
      DB::rollBack();
      return response($e->getMessage(), Response::HTTP_CONFLICT);
    }
  }

  public function validateOpTemporary(Request $request)
  {
    $data = $request->all();
    $documentId = $data['documentId'];
    Document::where('id', $documentId)->update(['active' => 3]);

    $tulas = EanCode14::where('document_id', $documentId)->where('status', 0)->selectRaw('id, weight, observation_auditor, observation_driver')->get();

    if (count($tulas) == 0) {
      return 'No se encontraron tulas para facturar';
    }

    foreach ($tulas as $value) {
      EanCode14::where('id', $value['id'])->update(['weight' => $value['weight'], 'observation_auditor' => $value['observation_auditor'], 'observation_driver' => $value['observation_driver'], 'status' => 11]);
    }

    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $settingsObj = new Settings($company_id);
    $chargeUserName = $settingsObj->get('production_analyst');
    $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
      $q->where('name', $chargeUserName);
    })->first();

    $documento = Document::where('id', $documentId)->first();
    $client = Client::where('id', $documento['client'])->first();

    $taskSchedulesW = [
      // 'start_date' => $params['start_date'],
      // 'end_date' => $params['end_date'],
      'name' => 'Validar facturación: ' . $client->name . ' ' . $documento['number'],
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::ValidateFacturation,
      'status' => ScheduleStatus::Process,
      'user_id' => $user->id,
      'parent_schedule_id' => $documentId,
      'company_id' => $company_id
    ];

    Schedule::create($taskSchedulesW);
    return 'Tarea de validación de facturas generada correctamente.';
  }

  public function print_list($id)
  {
    $document = Document::with('ean14', 'detail.client.city', 'clientdocument')->where('id', $id)->first();
    return $document->toArray();
  }
  public function getplatesMaaji($id)
  {
    $plates = Vehicle::get()->toArray();
    return $plates;
  }
  public function getdriversMaaji($id)
  {
    $plates = Driver::get()->toArray();
    return $plates;
  }

  public function saveOp(Request $request)
  {
    $data = $request->all();
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $document = array_key_exists('document', $data) ? $data['document'] : NULL;
    $detail = array_key_exists('detail', $data) ? $data['detail'] : NULL;

    // return $detail;

    $document_object = [
      "number" => $document['number'],
      "date" => date('Y-m-d'),
      "status" => "process",
      "receipt_type_id" => 3,
      "document_type" => "receipt",
      "company_id" => $company_id,
      "city" => isset($document['city']) ? $document['city'] : "",
      "client" => $document['client'],
    ];

    $documentos = Document::create($document_object);

    foreach ($detail as $value) {
      $detail_object = [
        "document_id" => $documentos->id,
        "unit" => $value['quantity'],
        "quanty" => $value['quantity'],
        "cartons" => $value['quantity'],
        "count_status" => 1,
        "product_id" => $value['id']
      ];

      DocumentDetail::create($detail_object);
    }

    return $document_object;
  }

  public function saveOpPlan(Request $request)
  {
    $data = $request->all();
    // return $data;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $document = array_key_exists('document', $data) ? $data['document'] : NULL;
    $detail = array_key_exists('detail', $data) ? $data['detail'] : NULL;

    $client = Client::where('id', $document['client'])->first();

    $document_object = [
      "number" => $client->name . '-' . rand(1, 1000),
      "status" => "process",
      "date" => date("Y-m-d"),
      // "receipt_type_id"=>3,
      "document_type" => "departure",
      "company_id" => $company_id,
      "city" => $document['city'],
      "client" => $document['client'],
      "warehouse_origin" => $document['warehouse_origin']
    ];
    // return $document_object;

    $documentos = Document::create($document_object);

    foreach ($detail as $value) {
      $detail_object = [
        "document_id" => $documentos->id,
        "unit" => $value['quantity'],
        "quanty" => $value['quantity'],
        "cartons" => $value['quantity'],
        "count_status" => 1,
        "product_id" => $value['id'],
        "good" => $value['calidad'] == 'primeras' ? $value['quantity'] : 0,
        "seconds" => $value['calidad'] == 'segundas' ? $value['quantity'] : 0
      ];

      DocumentDetail::create($detail_object);
    }

    return $document_object;
  }

  public function searchPrecinto(Request $request)
  {
    $data = $request->all();

    $precinto = EanCode14::where('code14', $data['precinto'])->first();

    return $precinto;
  }

  public function updateObservation(Request $request)
  {
    $data = $request->all();

    $obs = EanCode14::where('id', $data['id'])->update(['observation_driver' => $data['observation']]);

    return $obs;
  }

  /*
    * @author Julian Osorio
    * @params $id es el id de la tarea que estoy ejecutando
    */
  public function getTulasMaajiValidate($id)
  {
    $tulas = Document::join("wms_schedule_documents as sd", "sd.document_id", "wms_documents.id")
      ->join("wms_clients as c", "c.id", "wms_documents.client")
      ->where('sd.schedule_id', $id)
      ->where('document_type', "receipt")
      ->where('status', "process")
      ->select('wms_documents.id', 'c.name', 'wms_documents.number', 'wms_documents.facturation_number')
      ->get();
    return $tulas->toArray();
  }

  public function updateFacturationNumber(Request $request)
  {
    $data = $request->all();

    $facturation = EanCode14::where('id', $data['id'])->update(['facturation_number' => $data['facturation_number']]);

    return $facturation;
  }

  public function CreatePicking(Request $request)
  {
    $data = $request->all();
    // return $data;
    $params = array_key_exists('params', $data) ? $data['params'] : NULL;
    $documents = array_key_exists('documents', $data) ? $data['documents'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $client = Client::where('id', $params['clientId'])->first();

    $validateTask = Schedule::where('parent_schedule_id', $params['documentId'])->where('schedule_action', 'picking_action')->first();
    if ($validateTask) {
      return response(["message" => 'Ya hay una tarea generada para este documento'], Response::HTTP_CONFLICT);
    }

    foreach ($params['userId'] as $value) {
      $taskSchedulesW = [
        'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
        'end_date' => $params['end_date'],
        'name' => 'Realizar picking de: ' . $client->name . ' para el despacho ' . $params['documentNumber'],
        'schedule_type' => ScheduleType::Task,
        'schedule_action' => ScheduleAction::PickingAction,
        'status' => ScheduleStatus::Process,
        'user_id' => $value['user']['id'],
        'parent_schedule_id' => $params['documentId'],
        'company_id' => $company_id
      ];
      $scheduleW = Schedule::create($taskSchedulesW);
    }

    foreach ($documents as $value) {
      $obj = [
        "product_id" => $value['product_id'],
        "quanty" => $value['pedido'],
        "good" => $value['pedido'],
        "seconds" => 0,
        "unit" => $value['pedido'],
        "document_id" => $params['documentId'],
        "schedule_id" => $scheduleW['id']
      ];

      EnlistProducts::create($obj);
    }
    Document::where('id', $params['documentId'])->update(['status' => 'picking']);
    return response(Response::HTTP_OK);
  }

  public function getSuggestions($taskId)
  {
    $settings = new Settings(22);
    $seconds = $settings->Get('seconds');
    $good = $settings->Get('good');
    $gunsmithZone = $settings->Get('dispatch_zone');
    $enlistProducts = EnlistProducts::with('product.stock.zone_position.concept', 'product.stock', 'product.stock.zone_position.zone')
      ->where('schedule_id', $taskId)->get();
    // return $enlistProducts;
    $suggestions = [];
    foreach ($enlistProducts as $enlistProduct) {
      if ($enlistProduct->picked_quanty != $enlistProduct->quanty) {
        foreach ($enlistProduct->product->stock as $key => $stock) {
          // return $stock->zone_position->concept->name;
          if ($stock->zone_position->concept->name == $seconds && $enlistProduct->seconds > 0 && $stock->zone_position->zone->name != $gunsmithZone) {
            $suggestions[] = [
              'zone_position' => $stock->zone_position->code,
              'row' => $stock->zone_position->row,
              'level' => $stock->zone_position->level,
              'module' => $stock->zone_position->module,
              'zone' => $stock->zone_position->zone->name,
              'serial' => $stock->serial ? $stock->serial->serial : '',
              'reference' => $enlistProduct->product->reference,
              'description' => $enlistProduct->product->short_description,
              'needed_quantity' => $enlistProduct->quanty - $enlistProduct->picked_quanty,
              'product_id' => $enlistProduct->product_id
            ];
          }

          if ($stock->zone_position->concept->name == $good && $enlistProduct->good > 0 && $stock->zone_position->zone->name != $gunsmithZone) {
            $suggestions[] = [
              'zone_position' => $stock->zone_position->code,
              'row' => $stock->zone_position->row,
              'level' => $stock->zone_position->level,
              'module' => $stock->zone_position->module,
              'zone' => $stock->zone_position->zone->name,
              'serial' => $stock->serial ? $stock->serial->serial : '',
              'reference' => $enlistProduct->product->reference,
              'description' => $enlistProduct->product->short_description,
              'needed_quantity' => $enlistProduct->quanty - $enlistProduct->picked_quanty,
              'product_id' => $enlistProduct->product_id
            ];
          }
        }
      }
    }
    return $suggestions;
  }

  public function pickSuggestion(Request $request)
  {
    $settings = new Settings(22);
    $data = $request->all();
    // return $data;
    $taskId = $data['task_id'];
    // $task = Schedule::find($taskId);
    // $taskId = $task->parent_schedule_id;
    $gunsmithZone = $settings->Get('dispatch_zone');
    // return $data;

    DB::beginTransaction();

    try {

      $zone = Zone::where('name', $gunsmithZone)->first();
      $newZonePosition = ZonePosition::where('zone_id', $zone->id)->first();
      $consult_serial_prod = Product::where('ean', $data['ean'])->first();
      // return $consult_serial_prod;
      // if (!$consult_serial_prod->serial) {
      // return 1;
      $enlistProduct = EnlistProducts::where('document_id', $taskId)->where('product_id', $consult_serial_prod->id)->first();

      if ($enlistProduct->picked_quanty == $enlistProduct->unit) {
        return $this->response->error('No es posible mercar El producto ,ya fue terminado', 404);
      }
      //    return $enlistProduct;
      if ($enlistProduct) {
        $position = ZonePosition::where('code', $data['zone_position'])->first();
        $stock_search = Stock::where('zone_position_id', $position->id)->where('product_id', $consult_serial_prod->id)->first();

        $packing_val =  Eancodes14Packing::where('document_id', $enlistProduct->document_id)->where('product_id', $consult_serial_prod->id)->first();
        if (!$packing_val) {
          // return 5;

          $stock_search_new = Stock::where('zone_position_id', $newZonePosition->id)->where('product_id', $consult_serial_prod->id)->first();

          // return $packing_val;
          // if (!$stock_search_new ) {
          $stock_search->decrement('quanty', 1);

          $enlistProduct->increment('picked_quanty', 1);
          $objeto = [
            'product_id' => $consult_serial_prod->id,
            'zone_position_id' => $newZonePosition->id,
            'quanty' => 1,
            'code128_id' => $stock_search->code128_id,
            'code_ean14' => $stock_search->code_ean14,
            'document_detail_id' => $stock_search->document_detail_id,
            'quanty_14' => 1,
            'good' => $enlistProduct->good > 0 ? 1 : 0,
            'seconds' => $enlistProduct->seconds > 0 ? 1 : 0
          ];
          $stockNew = Stock::create($objeto);

          $obj = [
            "document_id" => $enlistProduct->document_id,
            'code_ean14' => $stock_search->code_ean14,
            'code128_id' => $stock_search->code128_id,
            'quanty_14' => 1,
            'stock_id' => $stockNew->id,
            'good' => $enlistProduct->good,
            'seconds' => $enlistProduct->seconds,
            'product_id' => $enlistProduct->product_id
          ];
          Eancodes14Packing::create($obj);
          if ($stock_search->quanty === 0) {
            $stock_search = Stock::where('zone_position_id', $position->id)->where('product_id', $consult_serial_prod->id)->delete();
          }
          // }
        } else {
          // return 6;
          $packing = Eancodes14Packing::where('document_id', $enlistProduct->document_id)->where('product_id', $consult_serial_prod->id)->first();
          $stock_search_new = Stock::where('zone_position_id', $newZonePosition->id)->where('product_id', $consult_serial_prod->id)->where('id', $packing->stock_id)->first();
          $stock_search = Stock::where('zone_position_id', $position->id)->where('product_id', $consult_serial_prod->id)->first();
          $stock_search->decrement('quanty', 1);

          $enlistProduct->increment('picked_quanty', 1);
          $stock_search_new->increment('quanty', 1);
          $stock_search_new->increment('quanty_14', 1);


          $packing->increment('quanty_14', 1);
          $stock_searche = Stock::where('zone_position_id', $position->id)->where('product_id', $consult_serial_prod->id)->first();

          if ($stock_searche->quanty === 0) {
            Stock::where('zone_position_id', $position->id)->where('product_id', $consult_serial_prod->id)->delete();
          }
        }

        // return $stock_search;
        //    $enlistProduct->picked_quanty = $enlistProduct->quanty;

      }
      DB::commit();

      $this->res['respuesta'] = "Documentos guardado con éxito ";
      $this->res['exito']     = 1;
      $this->res['mensaje']   = "Documento guardado con éxito";
    } catch (\Exception $e) {
      DB::rollBack();

      $this->res['respuesta'] = "No se puede guardar el documento";
      $this->res['exito']     = 0;
      $this->res['mensaje']   = $e->getMessage();
    }
    // }
  }

  /**
   * Servicio para enviar el detalle recibido de las OP a saya.
   */
  public function remService($id)
  {
    $companyId = $request->input('company_id');
    $ean14 = EanCode14::from("wms_ean_codes14 as cd")
      ->leftjoin("wms_ean_codes14_detail as wdd", "wdd.ean_code14_id", "=", "cd.id")
      ->leftjoin("wms_products as p", "wdd.product_id", "=", "p.id")
      ->leftjoin("wms_documents as d", "cd.document_id", "=", "d.id")
      ->where('cd.id', '=', $id)
      ->select(
        'd.number',
        'cd.facturation_number',
        'wdd.quanty_receive',
        'p.reference'
      )
      ->get();

    // $pallet =  EanCode128::with('pallet.document_detail.product', 'document.ean14')->where('code128', $id)->first();
    // $detalles = [];
    // foreach ($pallet['pallet'] as $value) {
    //     $detalles[] = [
    //         "quantity" => $value['quanty'],
    //         "quality" => 1,
    //         "code" => $value['document_detail']['product']['reference']
    //     ];
    // }
    $detalles = [];
    foreach ($ean14 as $value) {
      $detalles[] = [
        "quantity" => $value->quanty_receive,
        "quality" => 1,
        "code" => $value->reference
      ];
    }
    $obje = [
      "number" => $ean14[0]['number'],
      "external_document" => $ean14[0]['facturation_number'],
      "status" => "PARCIAL",
      "detail" => $detalles
    ];


    $objeto = [
      'user' => 'ptolomeo',
      'pass' => 'PTML*_2250'
    ];

    // return $obje;
    $res = SoberanaServices::getToken($objeto, $objeto);
    $porciones = explode(":", $res);
    $porciones1 = explode('"', $porciones[1]);
    $token = explode('"', $porciones1[1]);

    //    return [$porciones2[0]];
    //       return $tales['Token'];
    //       if ($res) {
    //        return $res;
    $respuesta = SoberanaServices::PostReceivePlus($obje,  $token[0]);

    //     // return $respuesta;
    $pedazos = explode(":", $respuesta);
    //     // $tales = json_decode($respuesta, true);
    $pedazos1 = explode('"', $pedazos[0]);
    //     return $pedazos1[1];
    // return $respuesta = "";
    $pedazosvali = explode('"', $pedazos[0]);
    $pedazos2 = explode('"', $pedazos1[1]);
    $pedazosvali1 = explode('"', $pedazosvali[1]);
    // $tales = json_decode($res, true) ;
    return ['error' => $pedazosvali1[0], 'respuesta' => $pedazos2[0]];

    //    }

    // }
    //  Document::where('id',$id)->update(['type'=>'enviado']);
  }

  public function patsService($id, Request $request)
  {
    $companyId = $request->input('company_id');
    $document = Document::with('detail.product', 'enlistplan.product')->where('id', $id)->first()->toArray();
    $detalles = [];
    if (count($document['enlistplan']) > 0) {
      foreach ($document['enlistplan'] as $value) {
        if ($value['picked_quanty'] > 0) {
          $detalles[] = [
            "quantity" => $value['picked_quanty'],
            "code" => $value['product']['reference']
          ];
        }
      }
    } else {
      $documents = DB::table('wms_document_details')
        ->Join('wms_documents', 'wms_document_details.document_id', '=', 'wms_documents.id')
        ->Join('wms_products', 'wms_document_details.product_id', '=', 'wms_products.id')
        ->leftJoin('wms_waves as ww', function ($join) {
          $join->where(DB::raw("FIND_IN_SET(wms_documents.id, ww.documents)"), "<>", "0");
        })
        ->leftJoin('wms_enlist_products_waves as wepw', function ($query) {
          $query->on('ww.id', '=', 'wepw.wave_id')
            ->on('wms_document_details.product_id', '=', 'wepw.product_id');
        })
        ->where('wms_documents.id', $id)
        ->select('wepw.picked_quanty', 'wms_products.reference')
        ->get();

      foreach ($documents as $value) {
        if ($value->picked_quanty > 0) {
          $detalles[] = [
            "quantity" => $value->picked_quanty,
            "code" => $value->reference
          ];
        }
      }
    }

    $obje = [
      "number" => $document['number'],
      "id_number" => $document['id'],
      "detail" => $detalles
    ];
    // return $obje;
    $objeto = [
      'user' => env("API_SAYA_USERNAME", "ptolomeo"),
      'pass' => env("API_SAYA_PASSWORD", "PTML*_2250")
    ];

    $res = SoberanaServices::getToken($objeto, $companyId);
    $porciones = explode(":", $res);
    $porciones1 = explode('"', $porciones[1]);
    $token = explode('"', $porciones1[1]);

    $respuesta = SoberanaServices::saveOrder($obje, $token[0], $companyId);

    $vuelta = explode(":", $respuesta);
    $vuelta1 = explode('"', $vuelta[0]);

    if ($vuelta1[1] != 'error') {
      Document::where('id', $id)->update(['status' => 'Por facturar SAYA', 'send_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1]]);
    }

    $client = Client::where('id', $document['client'])->first();
    $settingsObj = new Settings($companyId);
    $chargeUserName = $settingsObj->get('receipt_group');
    $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
      $q->where('name', $chargeUserName);
    })->first();

    if (empty($user)) {
      return $this->response->error('No se encontró un usuario para asignar la tarea', 404);
    }

    $taskSchedules = [
      'name' => "Ingresar Documentos: $client->name para el pedido " . $document['number'],
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::Documentos,
      'status' => ScheduleStatus::Process,
      'user_id' => $user->id,
      'parent_schedule_id' => $id,
      'company_id' => $companyId
    ];
    $pedazos = explode(":", $respuesta);
    $pedazos1 = explode('"', $pedazos[1]);

    return $respuesta;
  }

  public function suspendDocument($id, Request $request)
  {
    DB::beginTransaction();

    try {
      $document = Document::where('id', $id)->first();

      if ($document->status == 'packing') {
        $statusPacking = Schedule::where('parent_schedule_id', $id)->where('name', 'like', '%Realizar packing%')->where('status', 'closed')->first();
        if (empty($statusPacking)) {
          throw new RuntimeException('El packing de este pedido no ha finalizado');
        }
      }

      if ($document->status == 'picking') {
        $statusPicking = Schedule::where('parent_schedule_id', $id)->where('name', 'like', '%Realizar picking%')->where('status', 'closed')->first();
        if (empty($statusPicking)) {
          throw new RuntimeException('El picking de este pedido no ha finalizado');
        }
      }

      Document::where('id', $id)->update(['status' => 'pending_suspend']);

      $companyId = $request->input('company_id');
      $settingsObj = new Settings($companyId);
      $chargeUserName = $settingsObj->get('subdirector_logistics');
      $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
        $q->where('name', $chargeUserName);
      })->first();

      if (empty($user)) {
        throw new RuntimeException('No se encontró un usuario para asignar la tarea');
      }

      $taskSchedules = [
        'name' => 'Suspender pedido: ' . $document->number,
        'schedule_type' => ScheduleType::Task,
        'schedule_action' => ScheduleAction::SuspendDocumento,
        'status' => ScheduleStatus::Process,
        'user_id' => $user->id,
        'parent_schedule_id' => $document->id,
        'company_id' => $companyId
      ];
      Schedule::create($taskSchedules);
      DB::commit();
      return response(["message" => 'Tarea de suspensión creada correctamente'], 201);
    } catch (Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response(["message" => $e->getMessage()], 500);
    }
  }

  public function cancelDocument($id, Request $request)
  {
    DB::beginTransaction();

    try {
      $document = Document::where('id', $id)->first();

      Document::where('id', $id)->update(['status' => 'pending_cancel']);

      $companyId = $request->input('company_id');
      $settingsObj = new Settings($companyId);
      $chargeUserName = $settingsObj->get('cedi_charge');
      $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
        $q->where('name', $chargeUserName);
      })->first()->toArray();

      // return $user['id'];



      if (empty($user)) {
        throw new RuntimeException('No se encontró un usuario para asignar la tarea');
      }

      $taskSchedules = [
        'name' => 'Cancelar pedido: ' . $document->number,
        'schedule_type' => ScheduleType::Task,
        'schedule_action' => ScheduleAction::CancelDocument,
        'status' => ScheduleStatus::Process,
        'user_id' => $user['id'],
        'parent_schedule_id' => $document->id,
        'company_id' => $companyId
      ];
      Schedule::create($taskSchedules);
      DB::commit();
      return response(["message" => 'Tarea de cancelación creada correctamente'], 201);
    } catch (Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response(["message" => $e->getMessage()], 500);
    }
  }

  public function CreatePickingEspecial(Request $request)
  {
    $data = $request->all();
    $params = array_key_exists('params', $data) ? $data['params'] : NULL;
    $products = array_key_exists('products', $data) ? $data['products'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $client = Client::where('id', $params['clientId'])->first();
    $taskSchedulesW = [
      'start_date' => $params['start_date'],
      'end_date' => $params['end_date'],
      'name' => 'Realizar picking especial de: ' . $client->name . ' para el despacho ' . $params['documentNumber'],
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::PickingAction,
      'status' => ScheduleStatus::Process,
      'user_id' => $params['userId'],
      'parent_schedule_id' => $params['documentId'],
      'company_id' => $company_id
    ];
    $scheduleW = Schedule::create($taskSchedulesW);

    Document::where('id', $params['documentId'])->update(['status' => 'picking']);

    foreach ($products as $value) {
      $obj = [
        "product_id" => $value['product_id'],
        "quanty" => $value['quanty'],
        "good" => $value['quanty'],
        "seconds" => 0,
        "unit" => $value['quanty'],
        "is_material" => 1,
        "document_id" => $params['documentId'],
        "schedule_id" => $scheduleW['id']
      ];

      EnlistProducts::create($obj);
    }
  }

  public function getDocumentId($id)
  {
    $document =  Document::with('code_packing.product.stock.zone_position.concept', 'code_packing.stock.zone_position.concept', 'clientdocument.city', 'ean14.detail', 'ean14.detail_m')->where('id', $id)->first();
    // $documents = [];
    // foreach ($document->enlistplan as  $value) {
    //     if ($value->returned < $value->picked_quanty) {
    //         $documents []= $value;
    //     }
    // }
    return $document;
  }

  public function getDocumentById($id)
  {
    // $document = Document::with('detail.product', 'clientdocument.city', 'enlistplan.product', 'enlistplan.stock.zone_position.concept', 'ean14.detail', 'ean14.detail_m')->where('id', $id)->first();
    $document = DB::table('wms_enlist_products')
      ->leftJoin('wms_products', 'wms_enlist_products.product_id', '=', 'wms_products.id')
      ->leftJoin('wms_documents', 'wms_enlist_products.document_id', '=', 'wms_documents.id')
      ->groupBy('wms_enlist_products.product_id')
      ->where('wms_enlist_products.document_id', $id)
      ->select('wms_products.ean', 'wms_products.reference', 'wms_products.description', 'wms_enlist_products.picked_quanty', 'wms_products.id as product_id', 'wms_documents.number')
      ->get();



    foreach ($document as &$value) {
      $subida = DB::table('wms_ean_codes14_detail')
        ->Join('wms_ean_codes14', 'wms_ean_codes14.id', '=', 'wms_ean_codes14_detail.ean_code14_id')
        ->groupBy('wms_ean_codes14_detail.product_id')
        ->where('wms_ean_codes14.document_id', $id)
        ->where('wms_ean_codes14_detail.product_id', $value->product_id)
        ->select(DB::raw('SUM(wms_ean_codes14_detail.quanty) as quanty'))
        ->first();
      // return [$subida];
      if ($subida) {
        if (isset($value->empacado_sin)) {
          $value->empacado_sin += $subida->quanty;
        } else {
          $value->empacado_sin = $subida->quanty;
        }
      } else {
        $value->empacado_sin = 0;
      }

      // return [$subida];
    }
    $documentos = [];
    foreach ($document as $value2) {
      if (($value2->picked_quanty - $value2->empacado_sin) !== 0) {
        $documentos[] = $value2;
      }
    }



    $pedido = DB::table('wms_enlist_products')
      ->groupBy('wms_enlist_products.document_id')
      ->where('wms_enlist_products.document_id', $id)
      ->select(DB::raw('SUM(wms_enlist_products.picked_quanty) as pedido'))
      ->get();

    $cajas = DB::table('wms_ean_codes14')
      ->groupBy('wms_ean_codes14.id')
      ->where('wms_ean_codes14.document_id', $id)
      ->select('wms_ean_codes14.id')
      ->get();
    $ids = [];
    foreach ($cajas as $value1) {
      $ids[] = $value1->id;
    }
    // return $document;
    $empacado = DB::table('wms_ean_codes14_detail')
      ->whereIn('wms_ean_codes14_detail.ean_code14_id', $ids)
      ->select(DB::raw('SUM(wms_ean_codes14_detail.quanty) as empacado'))
      ->get();

    // return [$empacado[0]->empacado];
    if (count($pedido) > 0) {
      $documentos[0]->pedido = $pedido[0]->pedido;
    }

    if (count($empacado) > 0) {
      $documentos[0]->empacado = $empacado[0]->empacado;
    }



    return $documentos;
  }

  public function confirmSuspendDocument($id)
  {
    $document = Document::where('id', $id)->update(['status' => 'suspend']);
    return $document;
  }

  public function cancelSuspendDocument($id)
  {
    $dataDocument = Document::find($id);
    $dataClient = Client::find($dataDocument->client);
    $isPicking = Schedule::where('parent_schedule_id', $id)->where('name', 'like', '%"Realizar picking de: ' . $dataClient->name . '"%')->where('status', 'process')->first();
    if (empty($isPicking)) {
      Document::where('id', $id)->update(['status' => 'process']);
    } else {
      Document::where('id', $id)->update(['status' => 'picking']);
    }

    return $dataDocument;
  }

  public function confirmCancelDocument($id, Request $request)
  {
    $companyId = $request->input('company_id');

    $objeto = [
      'user' => 'ptolomeo',
      'pass' => 'PTML*_2250'
    ];
    $res = SoberanaServices::getToken($objeto, $companyId);
    $porciones = explode(":", $res);
    $porciones1 = explode('"', $porciones[1]);
    $token = explode('"', $porciones1[1]);
    $documents = Document::where('id', $id)->first();


    $obje = [
      'number' => $documents->number
    ];

    $respuesta = SoberanaServices::cancelDocument($obje, $token[0], $companyId);
    Document::where('id', $id)->update(['status' => 'cancel']);

    $document = Document::where('id', $id)->first();
    $tareas = Schedule::where('parent_schedule_id', $id)->update(['status' => 'closed']);

    $companyId = $request->input('company_id');

    $taskUser = Schedule::where('parent_schedule_id', '=', $id)
      ->where(function ($query) {
        $query->where('schedule_action', '=', ScheduleAction::PackingAction)
          ->orWhere('schedule_action', '=', ScheduleAction::PickingAction);
      })->first();

    if (!empty($taskUser)) {
      $taskSchedules = [
        'name' => 'Devolución del pedido: ' . $document->number,
        'schedule_type' => ScheduleType::Task,
        'schedule_action' => ScheduleAction::OrderReturn,
        'status' => ScheduleStatus::Process,
        'user_id' => $taskUser->user_id,
        'parent_schedule_id' => $document->id,
        'company_id' => $companyId
      ];
      if ($taskUser) {
        Schedule::create($taskSchedules);
      }
    }


    $pedazos = explode(":", $respuesta);
    $pedazos1 = explode('"', $pedazos[0]);
    return $pedazos1[1];

    return $respuesta;
  }

  public function cancelCancelDocument($id)
  {
    $dataDocument = Document::find($id);
    $dataClient = Client::find($dataDocument->client);
    $isPicking = Schedule::where('parent_schedule_id', $id)->where('name', 'like', '%"Realizar picking de: ' . $dataClient->name . '"%')->where('status', 'process')->first();
    if (empty($isPicking)) {
      Document::where('id', $id)->update(['status' => 'process']);
    } else {
      Document::where('id', $id)->update(['status' => 'picking']);
    }

    return $dataDocument;
  }

  public function orderReturn(Request $request)
  {
    $data = $request->all();
    $documentId = array_key_exists('document', $data) ? $data['document'] : NULL;
    $ean = array_key_exists('ean', $data) ? $data['ean'] : NULL;
    $position_origin = array_key_exists('position_origin', $data) ? $data['position_origin'] : NULL;
    $position_destination = array_key_exists('position_destination', $data) ? $data['position_destination'] : NULL;
    $quantity = array_key_exists('quantity', $data) ? $data['quantity'] : NULL;
    $completeReturn = array_key_exists('completeReturn', $data) ? $data['completeReturn'] : false;
    // return $quantity;

    // DB::beginTransaction();

    // try{

    if ($position_origin !== 'Transition') {
      $dataPositionOrigin = ZonePosition::where('code', $position_origin)->first();
      if (empty($dataPositionOrigin)) {
        throw new RuntimeException('No se encontró la posicion de origen');
      }
    }
    // return $position_origin;

    $dataPositionDestination = ZonePosition::where('code', $position_destination)->first();
    if (empty($dataPositionDestination)) {
      throw new RuntimeException('No se encontró la posicion de destino');
    }

    $dataProduct = Product::where('ean', $ean)
      ->join('wms_document_details', 'wms_products.id', '=', 'wms_document_details.product_id')
      ->where('wms_document_details.document_id', $documentId)
      ->selectRaw('wms_products.id')
      ->first();
    if (empty($dataProduct)) {
      throw new RuntimeException('Este EAN no está asociado en este pedido');
    }

    $eanpacking = Eancodes14Packing::where('product_id', $dataProduct->id)->where('document_id', $documentId)->first();

    // if ($position_origin !== 'Transition') {

    // }


    if ($position_origin !== 'Transition') {
      $consultStock = Stock::where('product_id', $dataProduct->id)->where('zone_position_id', $dataPositionOrigin->id)->first();
      $consultStock->decrement('quanty', $quantity);
      $consultStockD = Stock::where('product_id', $dataProduct->id)->where('zone_position_id', $dataPositionDestination->id)->first();
      if ($consultStockD) {
        $consultStockD->increment('quanty', $quantity);
      } else {
        Stock::create(['product_id' => $dataProduct->id, 'zone_position_id' => $dataPositionDestination->id, 'quanty' => $quantity]);
      }
      $consultEnlist = EnlistProducts::where('product_id', $dataProduct->id)->where('document_id', $documentId)->first();
      $consultEnlist->increment('returned', $quantity);
      $consultP = Eancodes14Packing::where('product_id', $dataProduct->id)->where('document_id', $documentId)->first();
      $consultP->increment('seconds', $quantity);
      $consultStockc = Stock::where('product_id', $dataProduct->id)->where('zone_position_id', $dataPositionOrigin->id)->first();
      if ($consultStock->quanty == 0) {
        $consultStock->delete();
      }
      // Stock::where('product_id',$dataProduct->id)->where('zone_position_id',$dataPositionOrigin->id)->update(['zone_position_id'=>$dataPositionDestination->id]);
    } else {

      $detail = DocumentDetail::where('product_id', $dataProduct->id)->where('document_id', $documentId)->first();
      $ean14D = EanCode14Detail::where('product_id', $dataProduct->id)->where('document_detail_id', $detail->id)->first();
      $ean14 = EanCode14::where('id', $ean14D->ean_code14_id)->first();

      $transition =  StockTransition::where('code_ean14', $ean14D->ean_code14_id)->first();
      // return $transition;

      if ($transition) {
        //    return $transition;
        $transition->decrement('quanty', $quantity);
        Stock::create(['product_id' => $dataProduct->id, 'zone_position_id' => $dataPositionDestination->id, 'quanty' => $quantity]);
        $consultEnlist = EnlistProducts::where('product_id', $dataProduct->id)->where('document_id', $documentId)->first();
        $consultEnlist->increment('returned', $quantity);
        $consultP = Eancodes14Packing::where('product_id', $dataProduct->id)->where('document_id', $documentId)->first();
        $consultP->increment('seconds', $quantity);
        $transitionc =  StockTransition::where('code_ean14', $ean14D->ean_code14_id)->first();
        if ($transitionc->quanty == 0) {
          $transitionc->delete();
        }
      }
    }

    //     DB::commit();
    //     return response([], 201);
    // }catch(Exception $e){
    //     DB::rollBack();
    //     if($e instanceof RuntimeException){
    //         return response(["message" => $e->getMessage()], 409);
    //     }
    //     return response(["message" => $e->getMessage()], 500);
    // }
  }

  public function createPacking($id, Request $request)
  {
    $data = $request->all();
    $document = Document::where('id', $id)->first();
    if ($document->status == 'pending_cancel' || $document->status == 'cancel') {
      return response(["message" => "No se puede crear la tarea de packing porque el pedido está en proceso de cancelación"], 409);
    }

    $validateTask = Schedule::where('parent_schedule_id', $document->id)->where('schedule_action', 'PackingAction')->first();
    if ($validateTask) {
      return ('Ya hay una tarea generada para este documento');
    }

    Document::where('id', $id)->update(['status' => 'packing']);

    $validateTask = Schedule::where('parent_schedule_id', $document->id)->where('schedule_action', 'picking_action')->update(['status' => 'closed']);

    $client = Client::where('id', $document->client)->first();

    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    $chargeUserName = $settingsObj->get('assistant_cedi');
    // $user = User::whereHas('person.charge', function ($q) use ($chargeUserName)
    // {
    //     $q->where('name', $chargeUserName);
    // })->first();

    // if(empty($user)) {
    //     return('No se encontró un usuario para asignar la tarea');
    // }


    $taskSchedules = [
      'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'name' => "Realizar packing de: $client->name para el pedido $document->number",
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::PackingAction,
      'status' => ScheduleStatus::Process,
      'user_id' => $data['session_user_id'],
      'parent_schedule_id' => $document->id,
      'company_id' => $companyId
    ];
    Schedule::create($taskSchedules);
  }

  public function savePacking(Request $request)
  {
    $data = $request->all();
    $documentId = array_key_exists('document', $data) ? $data['document'] : NULL;
    $ean14 = array_key_exists('ean14', $data) ? $data['ean14'] : NULL;
    $ean13 = array_key_exists('ean13', $data) ? $data['ean13'] : NULL;

    DB::beginTransaction();

    try {

      $dataEan14 = EanCode14::where('code14', $ean14)->where('document_id', $documentId)->first();

      if (!$dataEan14) {
        throw new RuntimeException('El EAN 14 no existe o no está asociado a este pedido');
      }

      $dataEan14Stored = EanCode14::where('code14', $ean14)->where('document_id', $documentId)->where('stored', 0)->where('id', $dataEan14->id)->first();
      if (!$dataEan14Stored) {
        throw new RuntimeException('El EAN 14 ya se encuentra cerrado');
      }

      $dataProduct = Product::where('ean', $ean13)
        ->join('wms_document_details', 'wms_products.id', '=', 'wms_document_details.product_id')
        ->where('wms_document_details.document_id', $documentId)
        ->selectRaw('wms_document_details.id as idDocumento,
            wms_products.id as idProducto')
        ->first();


      if (empty($dataProduct)) {
        throw new RuntimeException('No se encontró un producto asociado al ean ingresado');
      }

      $dataEmpaque = Document::join('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
        ->join('wms_eancodes14_packing', function ($query) {
          $query->on('wms_document_details.document_id', '=',  'wms_eancodes14_packing.document_id');
          $query->on('wms_document_details.product_id', '=', 'wms_eancodes14_packing.product_id');
        })
        ->join('wms_stock', 'wms_eancodes14_packing.stock_id', '=', 'wms_stock.id')
        ->join('wms_zone_positions', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
        ->selectRaw(
          "wms_stock.id as idStock,
                wms_stock.quanty"
        )
        ->where('zone_id', 512)
        ->where('wms_document_details.product_id', $dataProduct->idProducto)
        ->where('wms_documents.id', $documentId)
        ->first();
      // return $dataEmpaque;

      if (!empty($dataEmpaque)) {
        if ($dataEmpaque->quanty == 1) {
          DB::delete("DELETE FROM wms_stock where id = $dataEmpaque->idStock");
        } else {
          DB::update(
            "UPDATE
                            wms_stock
                        SET
                            quanty = quanty - 1,
                            quanty_14 = quanty_14 - 1
                        WHERE id = $dataEmpaque->idStock"
          );
        }
      } else {
        $actualiza = EanCode14::where('code14', $ean14)->where('document_id', $documentId)->update(['stored' => 0, 'status' => 20]);
        // return $actualiza;
        if ($actualiza) {
        } else {
          throw new RuntimeException('El producto no se encuentra en la zona de empaque');
        }
      }

      $validacion = EanCode14Detail::join('wms_ean_codes14', 'wms_ean_codes14_detail.ean_code14_id', 'wms_ean_codes14.id')
        ->where('wms_ean_codes14.document_id', $documentId)
        ->where('product_id', $dataProduct->idProducto)
        ->selectRaw('SUM(wms_ean_codes14_detail.quanty) as quanty')
        ->first();

      $validacionE = EnlistProducts::where('document_id', $documentId)
        ->where('product_id', $dataProduct->idProducto)
        ->selectRaw('SUM(wms_enlist_products.picked_quanty) as picked_quanty')
        ->first();

      if ($validacion) {
        if ($validacionE->picked_quanty == $validacion->quanty) {
          throw new RuntimeException('El producto ya fue empacado por completo');
        }
      }

      $dataTransition = StockTransition::where('product_id', $dataProduct->idProducto)->where('document_detail_id', $dataProduct->idDocumento)->where('code_ean14', $dataEan14->id)->first();
      if ($dataTransition) {
        DB::update(
          "UPDATE
                        wms_stock_transition
                    SET
                        quanty = quanty + 1,
                        quanty_14 = quanty_14 + 1
                    WHERE product_id = $dataProduct->idProducto
                    AND code_ean14 = $dataEan14->id
                    AND document_detail_id = $dataProduct->idDocumento"
        );
      } else {
        StockTransition::create([
          "product_id" => $dataProduct->idProducto,
          "quanty" => 1,
          "action" => "output",
          "document_detail_id" => $dataProduct->idDocumento,
          "code_ean14" => $dataEan14->id,
          "quanty_14" => 1
        ]);
      }

      $dataEan14Detail = EanCode14Detail::where('ean_code14_id', $dataEan14->id)->where('product_id', $dataProduct->idProducto)->first();
      if ($dataEan14Detail) {
        DB::update(
          "UPDATE
                        wms_ean_codes14_detail
                    SET
                        quanty = quanty + 1,
                        good = good + 1,
                        quanty_receive = quanty_receive + 1
                    WHERE product_id = $dataProduct->idProducto
                    AND ean_code14_id = $dataEan14->id"
        );
      } else {
        EanCode14Detail::create([
          "ean_code14_id" => $dataEan14->id,
          "product_id" => $dataProduct->idProducto,
          "quanty" => 1,
          "good" => 1,
          "quanty_receive" => 1,
          "document_detail_id" => $dataProduct->idDocumento
        ]);
      }

      DB::commit();
      return response([], 201);
    } catch (Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response(["message" => $e->getMessage()], 500);
    }
  }

  public function generateEan14Packing(Request $request)
  {
    $data = $request->all();
    $documentId = array_key_exists('document', $data) ? $data['document'] : NULL;
    $container = array_key_exists('container', $data) ? $data['container'] : NULL;
    $companyId = $request->input('company_id');

    $data14 = EanCode14::where('status', 20)->orderBy('id', 'desc')->first();
    $code = $data14 ? $data14->code14 + 1 : '10000000000000';

    DB::beginTransaction();

    try {

      $code14 = EanCode14::where('stored', 0)->where('document_id', $documentId)->first();
      if ($code14) {
        return $this->response->error('Debe cerrar esta caja antes de poder generar una nueva', 404);
      }
      $code14 = EanCode14::create([
        'code14' => $code,
        'container_id' => $container,
        'document_id' => $documentId,
        'company_id' => $companyId,
        'status' => 20
      ]);
      DB::commit();
      return response(["message" => $code14->code14], 201);
    } catch (Exception $e) {
      DB::rollBack();
      return response(["message" => $e->getMessage()], 500);
    }
  }


  public function closeEan14(Request $request)
  {
    $data = $request->all();
    $documentId = array_key_exists('document', $data) ? $data['document'] : NULL;
    $ean14 = array_key_exists('ean14', $data) ? $data['ean14'] : NULL;
    $peso = array_key_exists('peso', $data) ? $data['peso'] : NULL;

    $data14 = EanCode14::where('document_id', $documentId)->where('code14', $ean14)->where('status', 20)->first();

    $detail = EanCode14Detail::where('ean_code14_id', $data14->id)->first();

    if (!$detail) {
      return response(["message" => "El EAN 14 no puede cerrarse porque no contiene unidades"]);
    }

    if (empty($data14)) {
      return response(["message" => "El EAN 14 no existe o no está asociado a este pedido"]);
    }

    EanCode14::where('document_id', $documentId)->where('code14', $ean14)->where('status', 20)->update(['stored' => 1, 'weight' => $peso]);

    return response(["message" => "EAN cerrado correctamente"]);
  }

  public function validateEanClosed(Request $request)
  {
    $data = $request->all();
    $documentId = array_key_exists('document', $data) ? $data['document'] : NULL;

    $data14 = EanCode14::where('document_id', $documentId)->where('status', 20)->where('stored', 0)->first();

    if (!empty($data14)) {
      return response(["message" => "No se ha cerrado la caja $data14->code14", 409]);
    }

    return response(["message" => "La tarea se va a cerrar"], 201);
  }

  public function createReubicarPacking(Request $request)
  {
    $data = $request->all();
    $documentId = array_key_exists('document', $data) ? $data['document'] : NULL;

    $document = Document::where('id', $documentId)->first();
    $validateTask = Schedule::where('parent_schedule_id', $document->id)->where('schedule_action', 'ReubicarPackingAction')->first();
    if ($validateTask) {
      return ('Ya hay una tarea generada para este documento');
    }

    if ($document->status == 'pending_cancel' || $document->status == 'cancel') {
      return response(["message" => "No se puede crear la tarea de ubicar porque el pedido está en proceso de cancelación"], 409);
    }

    Document::where('id', $documentId)->update(['status' => 'transsition']);
    $client = Client::where('id', $document->client)->first();

    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    $chargeUserName = $settingsObj->get('assistant_cedi');
    // $user = User::whereHas('person.charge', function ($q) use ($chargeUserName)
    // {
    //     $q->where('name', $chargeUserName);
    // })->first();

    // if(empty($user)) {
    //     return('No se encontró un usuario para asignar la tarea');
    // }

    $taskSchedules = [
      'name' => "Ubicar pedido de: $client->name para el pedido $document->number",
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::ReubicarPackingAction,
      'status' => ScheduleStatus::Process,
      'user_id' => $data['session_user_id'],
      'parent_schedule_id' => $document->id,
      'company_id' => $companyId
    ];
    Schedule::create($taskSchedules);

    $this->createIngresarDocumentoPedido($documentId, $companyId);
  }

  public function createIngresarDocumentoPedido($documentId, $companyId)
  {
    $document = Document::where('id', $documentId)->first();
    $client = Client::where('id', $document->client)->first();

    $settingsObj = new Settings($companyId);
    $chargeUserName = $settingsObj->get('transport');
    $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
      $q->where('name', $chargeUserName);
    })->first();

    if (empty($user)) {
      return ('No se encontró un usuario para asignar la tarea');
    }

    $taskSchedules = [
      'name' => "Ingresar documentos de: $client->name para el pedido $document->number",
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::Documentos,
      'status' => ScheduleStatus::Process,
      'user_id' => $user->id,
      'parent_schedule_id' => $document->id,
      'company_id' => $companyId
    ];
    // Schedule::create($taskSchedules);
  }

  public function createTrasTask(Request $request)
  {
    $data = $request->all();
    $params = array_key_exists('params', $data) ? $data['params'] : NULL;
    $documents = array_key_exists('documents', $data) ? $data['documents'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $client = Client::where('id', $params['clientId'])->first();
    $taskSchedulesW = [
      'start_date' => $params['start_date'],
      'end_date' => $params['end_date'],
      'name' => 'Realizar traslado de: ' . $client->name . '-' . $params['documentNumber'],
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::Traslados,
      'status' => ScheduleStatus::Process,
      'user_id' => $params['userId'],
      'parent_schedule_id' => $params['documentId'],
      'company_id' => $company_id
    ];
    // Document::where('id', $params['documentId'])->update(['status' => 'picking']);
    $scheduleW = Schedule::create($taskSchedulesW);
  }

  public function ConsultTrans($id)
  {
    return Document::with('detail.product')->where('id', $id)->first()->toArray();
  }

  public function relocated(Request $request)
  {

    $data = $request->all();
    $origin = array_key_exists('origin', $data) ? $data['origin'] : NULL;
    $destination = array_key_exists('destination', $data) ? $data['destination'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $ean13 = array_key_exists('ean13', $data) ? $data['ean13'] : NULL;
    $document_id = array_key_exists('document_id', $data) ? $data['document_id'] : NULL;

    $origin = ZonePosition::where('code', $origin)->first();
    $destino = ZonePosition::where('code', $destination)->first();
    $consulProduct = Product::where('ean', $ean13)->first();

    $sacar = Stock::where('zone_position_id', $origin->id)->where('product_id', $consulProduct->id)->first();
    $detalle = DocumentDetail::where('document_id', $document_id)->where('product_id', $consulProduct->id)->first();
    if ($detalle) {
      $detalle->decrement('relocated_quanty', 1);
    }

    if ($sacar) {
      $sacar->decrement('quanty', 1);
      $sacar->decrement('quanty_14', 1);
    }

    $ingresar = Stock::where('zone_position_id', $destino->id)->first();
    if ($ingresar) {
      $ingresar->increment('quanty', 1);
      $ingresar->increment('quanty_14', 1);
    }
  }

  public function createStockFull(Request $request)
  {
    $data = $request->all();
    $stock = array_key_exists('stock', $data) ? $data['stock'] : NULL;
    $companyId = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    // return $stock;
    DB::beginTransaction();
    try {
      foreach ($stock as $value) {
        $position = $value['position'];
        $ean13 = $value['ean13'];
        $quanty = $value['cantidad'];

        $zonePosition = ZonePosition::where('code', $position)->first();
        if (!$zonePosition) {
          throw new InvalidArgumentException("No se encontró una posición para el código ingresado");
        }

        $product = Product::where('ean', $ean13)->first();
        if (!$product) {
          throw new InvalidArgumentException("No se encontró un producto para el ean ingresado");
        }


        $stock = Stock::where('product_id', $product->id)->where('zone_position_id', $zonePosition->id)->where('quanty', '>', 0)->first();

        if ($stock) {
          $stock->quanty = $stock->quanty + $quanty;
          $stock->save();
        } else {
          Stock::create([
            'product_id' => $product->id,
            'zone_position_id' => $zonePosition->id,
            'quanty' => $quanty
          ]);
        }
      }

      DB::commit();
      return response('Productos ingresados con éxito', Response::HTTP_CREATED);
    } catch (Exception $e) {
      DB::rollBack();
      return response($e->getMessage(), Response::HTTP_CONFLICT);
    }
  }

  public function consulProductByean($id)
  {
    $product = Product::where('ean', $id)->first();
    if ($product) {
      return $product->toArray();
    };
    return [];
  }

  public function getCategory($id)
  {
    $product = ProductCategory::get()->toArray();
    return $product;
  }

  public function relocateMercancy(Request $request)
  {
    $data = $request->all();


    if (isset($data['position_out'])) {
      $codigo14 = Product::where('ean', $data['ean_out'])->first();
      $destino = ZonePosition::where('code', $data['position_out'])->first();

      if ($destino->concept_id === 122) {
        return $this->response->error('No es posible retirar mercancia de las posiciones de empaque', 404);
      }

      $origen = Stock::where('product_id', $codigo14->id)->where('zone_position_id', $destino->id)->first();

      if ($origen) {
        $origen->decrement('quanty', 1);
        $origenv = Stock::where('product_id', $codigo14->id)->where('zone_position_id', $destino->id)->first();
        if ($origenv->quanty == 0) {
          $origenv->delete();
        }
        $transition = StockTransition::where('product_id', $codigo14->id)->where('code_ean14', null)->first();
        if ($transition) {
          $transition->increment('quanty', 1);
        } else {
          //    $origen->decrement('quanty',1);
          StockTransition::create(['product_id' => $codigo14->id, 'zone_position_id' => $origen->zone_position_id, 'quanty' => 1, 'concept' => 'relocate']);
          //    $origenv = Stock::where('product_id',$codigo14->id)->where('zone_position_id',$destino->id)->first();
          //     if (!$origenv||$origenv->quanty == 0) {
          //         $origenv->delete();
          //     }
        }
      }

      // $destino = ZonePosition::where('code',$data['destination'])->first();


      // $cambios = Stock::where('code14_id',$codigo14->id)->update(['zone_position_id'=>$destino->id]);

      // if (!$cambios) {
      //   return $this->response->error('La operación fallo', 404);
      // }
    } else {

      $codigo14 = Product::where('ean', $data['ean_in'])->first();
      $destino = ZonePosition::where('code', $data['position_in'])->first();
      if ($destino->concept_id === 122) {
        return $this->response->error('No es posible ingresar mercancia de las posiciones de empaque', 404);
      }


      $origen = StockTransition::where('product_id', $codigo14->id)->where('code_ean14', null)->first();

      if ($origen) {
        $origen->decrement('quanty', 1);
        $transition = Stock::where('product_id', $codigo14->id)->where('zone_position_id', $destino->id)->where('code_ean14', null)->first();

        if ($transition) {
          $transition->increment('quanty', 1);
          $origenv = StockTransition::where('product_id', $codigo14->id)->where('code_ean14', null)->first();
          if ($origenv->quanty == 0) {
            $origenv->delete();
          }
        } else {
          // return 'entro';
          // $origen->decrement('quanty',1);
          Stock::create(['product_id' => $codigo14->id, 'zone_position_id' => $destino->id, 'quanty' => 1, 'active' => 1]);
          $origenv = StockTransition::where('product_id', $codigo14->id)->where('code_ean14', null)->first();
          if ($origenv->quanty == 0) {
            $origenv->delete();
          }
        }
      }
    }




    return $data;
  }

  public function getTransitionData()
  {
    return StockTransition::with('product')->where('code_ean14', null)->get()->toArray();
  }

  public function sendTaskCome(Request $request)
  {

    $data = $request->all();
    $document_id = array_key_exists('document_id', $data) ? $data['document_id'] : NULL;
    $companyId = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $user_id = array_key_exists('user_id', $data) ? $data['user_id'] : NULL;
    $dataForm = array_key_exists('datos', $data) ? $data['datos'] : NULL;
    $usercita_id = array_key_exists('usercita_id', $data) ? $data['usercita_id'] : NULL;

    $documentos = ScheduleDocument::with('document', 'document.clientdocument')->where('schedule_id', $document_id)->get();

    if ($dataForm && isset($dataForm['mercancy_number_description'])) {
      foreach ($documentos as $dataDocument) {
        Document::where("id", $dataDocument->document->id)->update([
          "sizfra_merchandise_form" => $dataForm['mercancy_number_description'],
          "fmm_authorization" => $dataForm['mercancy_number_description'],
          // "status" => 'pending_dispatch'
        ]);
      }
    }


    $pedidos = "";
    $clientes = "";
    $groups = "";
    $grupo = "";
    foreach ($documentos as $value) {
      $pedidos .=   $value['document']['clientdocument']['name'] . ' - ' . $value['document']['facturation_number'] . ';  ';
      $groups .=   $value['document']['group'] == $grupo ? '' : $value['document']['group'] . ', ';
      $grupo = $value['document']['group'];
    }

    $taskSchedules = [
      'name' => "Gestionar Documentación COMEX: " . $groups . '; ' . $pedidos,
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::Comex,
      'status' => ScheduleStatus::Process,
      'user_id' => $user_id,
      'parent_schedule_id' => $document_id,
      'company_id' => $companyId
    ];
    $schedule = Schedule::create($taskSchedules);

    foreach ($documentos as $value) {
      ScheduleDocument::create(['schedule_id' => $schedule->id, 'document_id' => $value['document']['id']]);
    }

    $settingsObj = new Settings($companyId);
    $chargeUserName = $settingsObj->get('receipt_group');
    $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
      $q->where('name', 'Coordinador Despachos');
    })->where('active', 1)->first();

    if (empty($user)) {
      return ('No se encontró un usuario para asignar la tarea');
    }

    $taskSchedules = [
      'name' => "Crear cita de despacho para el grupo: " . $groups . " y para el formulario: " . $dataForm['mercancy_number_description'],
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::CitaDespacho,
      'status' => ScheduleStatus::Process,
      'user_id' => $usercita_id,
      'parent_schedule_id' => $document_id,
      'company_id' => $companyId
    ];
    $schedule = Schedule::create($taskSchedules);

    foreach ($documentos as $value) {
      ScheduleDocument::create(['schedule_id' => $schedule->id, 'document_id' => $value['document']['id']]);
    }
  }

  public function sendTaskDespa(Request $request)
  {
    $data = $request->all();

    $document_id = array_key_exists('document_id', $data) ? $data['document_id'] : NULL;
    $task_id = array_key_exists('task_id', $data) ? $data['task_id'] : NULL;
    $fileName = array_key_exists('file_name', $data) ? $data['file_name'] : NULL;
    $dataForm = array_key_exists('datos', $data) ? $data['datos'] : NULL;
    $documentos = ScheduleDocument::with('document')->where('schedule_id', $task_id)->get();
    DB::beginTransaction();
    try {
      if ($fileName != "") {
        if (count($documentos) > 0) {
          self::updateBLComExWithDocument($fileName, $documentos);
        } else {
          self::updateBLComExWithOutDocument($fileName, $documentos);
        }
      }
      if ($documentos->count() == 1) {
        foreach ($documentos as $dataDocument) {
          if ($document_id && isset($dataForm['guide_master']) && isset($dataForm['guide_description'])) {
            Document::where("id", $dataDocument->document->id)->update([
              "transportation_company" => isset($dataForm['transport_description']) ? $dataForm['transport_description'] : $dataDocument->document->transport_description,
              "guia_transp" => isset($dataForm['guide_description']) ? $dataForm['guide_description'] : $dataDocument->document->guide_description,
              "master_guide" => isset($dataForm['guide_master']) ? $dataForm['guide_master'] : $dataDocument->document->guide_master,
              "status" => "pending_dispatch"
            ]);
          }
        }
      }
      DB::commit();
      return response(Response::HTTP_OK);
    } catch (Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], Response::HTTP_CONFLICT);
      }
      return response(["message" => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getConcept($id)
  {
    $product = ZoneConcept::get()->toArray();
    return $product;
  }

  public function getZones($id)
  {
    $product = Zone::where('warehouse_id', $id)
      ->where('code', 'not like', '%M1%')
      ->where('code', 'not like', '%MD%')
      ->where('code', 'not like', '%ZA%')
      ->where('code', 'not like', '%AS%')
      ->get()
      ->toArray();
    return $product;
  }

  public function consultDocument($id)
  {
    $product = Document::with('clientdocument')->where('id', $id)->first()->toArray();
    return $product;
  }

  public function getDocumentsReprint()
  {
    $product = Document::with('clientdocument')->where('document_type', 'receipt')->where('status', 'process')->get()->toArray();
    return $product;
  }

  public function generateEan14PackingReprint(Request $request)
  {
    $data = $request->all();
    $documentId = array_key_exists('document', $data) ? $data['document'] : NULL;
    $companyId = $request->input('company_id');
    return $documentId;

    $data14 = EanCode14::where('status', 20)->orderBy('id', 'desc')->first();
    $code = $data14 ? $data14->code14 + 1 : '10000000000000';

    DB::beginTransaction();

    try {
      $code14 = EanCode14::create([
        'code14' => $code,
        'container_id' => $container,
        'document_id' => $documentId,
        'company_id' => $companyId,
        'status' => 20
      ]);
      DB::commit();
      return response(["message" => $code14->code14], 201);
    } catch (Exception $e) {
      DB::rollBack();
      return response(["message" => $e->getMessage()], 500);
    }
  }

  public function getCode14Stored($id)
  {
    $data14 = EanCode14::where('stored', 0)->where('document_id', $id)->first()->toArray();
    return $data14;
  }

  public function getDocumentBySearch($id)
  {
    $data14 = Document::with('clientdocument')->where('number', 'LIKE', '%' . $id . '%')
      ->where(function ($query) {
        $query->where('status', 'Facturado SAYA')
          ->orWhere('status', 'Por facturar SAYA')
          ->orWhere('status', 'En documentación');
      })
      ->get()
      ->toArray();
    return $data14;
  }

  public function getDocumentBySearchNew($id)
  {
    $data14 = Document::with('clientdocument')->where('number', 'LIKE', '%' . $id . '%')->get()->toArray();
    return $data14;
  }

  public function getDocumentByNumber($number)
  {
    return Document::with('clientdocument')->where('number', '=', $number)->get();
  }



  public function getAllDepartureDocumentReceiptT(Request $request)
  {
    $data = $request->all();
    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    $departure = DocType::Departure;

    $username = User::where('id', $data['session_user_id'])->first();

    $document = DB::table('wms_documents')
      // ->leftJoin('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
      ->leftJoin('wms_clients', 'wms_documents.client', '=', 'wms_clients.id')
      ->leftJoin('cities', 'wms_clients.city_id', '=', 'cities.id')
      // ->leftJoin('wms_ean_codes14', 'wms_documents.id', '=', 'wms_ean_codes14.document_id')
      ->groupBy('wms_documents.id')
      ->where('wms_documents.document_type', $departure)
      ->where('wms_documents.company_id', $companyId)
      ->where('wms_documents.status', '!=', 'process')
      ->where('wms_documents.status', '!=', 'picking')
      ->where('wms_documents.status', '!=', 'packing')
      ->select(
        'wms_documents.number',
        'wms_documents.external_number',
        'wms_documents.observation',
        'wms_documents.total_cost',
        'total_benefit',
        'wms_documents.client',
        'wms_documents.status',
        'wms_documents.id',
        'wms_documents.min_date',
        'wms_documents.city as name',
        'wms_documents.lead_time as max_date',
        'cities.dispatch_time',
        'wms_clients.name as clientName',
        'wms_documents.warehouse_origin',
        'wms_documents.warehouse_destination',
        DB::raw("'$username->name' as responsible")
      )
      ->get();

    return $document;
  }

  public function getAllDepartureDocumentReceipt(Request $request)
  {
    $data = $request->all();
    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    $departure = DocType::Departure;

    $username = User::where('id', $data['session_user_id'])->first();

    $document = DB::table('wms_documents')
      ->leftJoin('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
      ->leftJoin('wms_clients', 'wms_documents.client', '=', 'wms_clients.id')
      ->leftJoin('cities', 'wms_clients.city_id', '=', 'cities.id')
      // ->leftJoin('wms_ean_codes14', 'wms_documents.id', '=', 'wms_ean_codes14.document_id')
      ->groupBy('wms_documents.id')
      ->where('wms_documents.document_type', $departure)
      ->where('wms_documents.company_id', $companyId)
      ->where('wms_documents.status', 'process')
      ->select(
        'wms_documents.number',
        'wms_documents.external_number',
        'wms_documents.date',
        DB::raw('SUM(wms_document_details.quanty) as quanty'),
        'wms_documents.total_cost',
        'total_benefit',
        'wms_documents.client',
        'wms_documents.status',
        'wms_documents.observation',
        'wms_documents.id',
        'wms_documents.min_date',
        'wms_documents.city as name',
        'wms_documents.lead_time as max_date',
        'wms_documents.transportation_company',
        'cities.dispatch_time',
        'wms_clients.name as clientName',
        'wms_documents.warehouse_origin',
        'wms_documents.warehouse_destination',
        DB::raw("'$username->name' as responsible")
      )
      ->groupBy('wms_documents.id')
      ->get();

    return $document;
  }

  public function getDispatchByFilterByFilter(Request $request)
  {
    $data = $request->all();
    $companyId = $request->input('company_id');
    $departure = DocType::Departure;

    $client = array_key_exists('client', $data) ? $data['client'] : NULL;
    // return $data;
    $number_document = array_key_exists('number_document', $data) ? $data['number_document'] : NULL;
    $status = array_key_exists('status', $data) ? $data['status'] : NULL;
    $date_start = array_key_exists('date_start', $data) ? $data['date_start'] : NULL;
    $date_end = array_key_exists('date_end', $data) ? $data['date_end'] : NULL;
    $type_document = array_key_exists('type_document', $data) ? $data['type_document'] : NULL;
    $external_number = array_key_exists('external_number', $data) ? $data['external_number'] : NULL;
    $facturation_number = array_key_exists('facturation_number', $data) ? $data['facturation_number'] : NULL;

    $document = DB::table('wms_documents')
      ->join('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
      ->leftJoin(DB::raw("(
            SELECT
                SUM(totalDispatch) totalDispatch,
                SUM( totalCajas ) AS totalCajas,
                SUM( weight ) AS totalPeso,
                document_id,
                container_id,
                master
            FROM
                (
                SELECT
                    SUM( e14d.quanty ) AS totalDispatch,
                    COUNT( DISTINCT e14.id ) AS totalCajas,
                    IFNULL( e14.weight, 0 ) AS weight,
                    document_id,
                    container_id,
                    master
                FROM
                    `wms_documents`
                    INNER JOIN `wms_ean_codes14` AS `e14` ON `wms_documents`.`id` = `e14`.`document_id`
                    LEFT JOIN `wms_ean_codes14_detail` AS `e14d` ON `e14`.`id` = `e14d`.`ean_code14_id`
                    LEFT JOIN `wms_products` ON `e14d`.`product_id` = `wms_products`.`id`
                GROUP BY
                    `e14`.`id`
                ) AS e14d
            GROUP BY
                e14d.document_id
            ) as e14"), function ($join) {
        $join->on('wms_documents.id', '=', 'e14.document_id');
      })

      ->leftJoin(DB::raw("(
                SELECT
                    SUM(picked_quanty) totalPicked,
                    document_id
                FROM
                    wms_enlist_products
                GROUP BY
                    wms_enlist_products.document_id
                ) as picked"), function ($join) {
        $join->on('wms_documents.id', '=', 'picked.document_id');
      })

      ->leftJoin('wms_clients', 'wms_documents.client', '=', 'wms_clients.id')
      ->leftJoin('cities', 'wms_clients.city_id', '=', 'cities.id')
      ->leftJoin('wms_containers', 'e14.container_id', '=', 'wms_containers.id')
      ->leftJoin('wms_schedule_documents', 'wms_schedule_documents.document_id', '=', 'wms_documents.id')
      ->leftJoin('wms_schedules', 'wms_schedules.id', '=', 'wms_schedule_documents.schedule_id')
      ->orderBy("wms_documents.date", 'asc')
      ->groupBy('wms_documents.id')
      ->where('wms_documents.document_type', $departure)
      ->where('wms_documents.company_id', $companyId)
      ->selectRaw("
            wms_documents.id,
            wms_schedules.updated_at,
            wms_documents.date,
            wms_documents.number,
            wms_documents.external_number,
            wms_documents.send_date,
            wms_documents.facturation_date,
            wms_documents.city as name,
            wms_clients.name as clientName,
            wms_documents.facturation_number,
            wms_documents.guia_transp,
            wms_documents.consecutive_dispatch,
            wms_documents.fmm_authorization,
            wms_documents.transportation_company,
            picked.totalPicked,
            SUM( wms_document_details.quanty) as totalUnit,
            IFNULL(totalDispatch, 0) AS totalDispatch,
            IFNULL(totalCajas, 0) AS totalCajas,
            IFNULL(totalPeso, 0) AS totalPeso,
            CASE
                WHEN wms_documents.status = 'picking' THEN 'Picking'
                WHEN wms_documents.status = 'packing' THEN 'Empaque'
                WHEN wms_documents.status = 'transsition' THEN 'Por facturar'
                WHEN wms_documents.status = 'process' THEN 'Por asignar'
                WHEN wms_documents.status = 'cancel' THEN 'Cancelado'
                WHEN wms_documents.status = 'pending_cancel' THEN 'Por cancelar'
                WHEN wms_documents.status = 'suspend' THEN 'Suspendido'
                WHEN wms_documents.status = 'pending_suspend' THEN 'Por suspender'
                WHEN wms_documents.status = 'pending_dispatch' THEN 'Por despachar'
                WHEN wms_documents.status = 'dispatch' THEN 'Despachado'
                WHEN wms_documents.status = 'Por facturar SAYA' THEN 'Por facturar SAYA'
                WHEN wms_documents.status = 'Facturado SAYA' THEN 'Facturado por saya'
                ELSE ''
            END as status,
            group_concat(DISTINCT wms_containers.name SEPARATOR '  -  ' ) as nameContainer,
            master

        ")
      ->groupBy('wms_documents.id')
      ->orderBy("wms_documents.date", 'asc');
    // return $data;

    if ($client) {
      // return 5;
      $document = $document->where('wms_clients.id', '=', $client);
    }

    if ($number_document) {
      $document = $document->where('wms_documents.number', '=', $number_document);
    }

    if ($status) {
      $document = $document->where('wms_documents.status', '=', $status);
    }

    if ($date_start) {
      $document = $document->where('wms_documents.date', '>=', $date_start);
    }

    if ($date_end) {
      $document = $document->where('wms_documents.date', '<=', $date_end);
    }

    if ($type_document) {
      $document = $document->whereRaw("wms_documents.number like '$type_document%'");
    }

    if ($external_number) {
      $document = $document->where('wms_documents.external_number', '=', $external_number);
    }

    if ($facturation_number) {
      $document = $document->where('wms_documents.facturation_number', '=', $facturation_number);
    }

    return $document->get();
  }

  public function get14code(Request $request)
  {
    $data = $request->all();

    $position = array_key_exists('position', $data) ? $data['position'] : NULL;
    $ean = array_key_exists('ean', $data) ? $data['ean'] : NULL;

    $position = ZonePosition::where('code', $position)->first();
    $producto = Product::where('ean', $ean)->first();

    return Stock::with('ean14', 'product')->where('zone_position_id', $position->id)->where('product_id', $producto->id)->first()->toArray();
  }

  public function updateInventoryByEan14Code(Request $request)
  {
    $data = $request->all();
    $position = array_key_exists('position', $data) ? $data['position'] : NULL;
    $quantity_into = array_key_exists('quantity_into', $data) ? $data['quantity_into'] : NULL;
    $ean = array_key_exists('ean', $data) ? $data['ean'] : NULL;

    DB::beginTransaction();

    try {

      //   $consult14 = EanCode14::where('code14',$ean14)->first();
      $position = ZonePosition::where('code', $position)->first();
      $producto = Product::where('ean', $ean)->first();

      if ($quantity_into == 0) {
        Stock::where('zone_position_id', $position->id)->where('product_id', $producto->id)->delete();
      } else {

        Stock::where('zone_position_id', $position->id)->where('product_id', $producto->id)
          ->update(['quanty' => $quantity_into]);
      }


      //   EanCode14::where('code14',$ean14)->update(['expiration_date' => $expiration_date, 'batch' => $batch]);
      // Stock::join("wms_ean_codes14_detail", function($query){
      //   $query->on("wms_stock.code14_id", "wms_ean_codes14_detail.id")
      //   ->where("wms_stock.product_id", "wms_ean_codes14_detail.product_id");
      // })
      // ->join('wms_ean_codes14', 'wms_ean_codes14_detail.ean_code14_id', 'wms_ean_codes14.id')
      // ->join('wms_document_details', 'wms_ean_codes14.document_detail_id', 'wms_document_details.id')
      // ->where('wms_stock.code_ean14',$ean14)
      // ->update(['expiration_date' => substr($expiration_date, 0, 10), 'batch' => $batch]);

      // DB::update(
      //   "UPDATE wms_stock
      //   INNER JOIN wms_ean_codes14_detail ON wms_stock.code14_id = wms_ean_codes14_detail.id
      //   AND wms_stock.product_id = wms_ean_codes14_detail.product_id
      //   INNER JOIN wms_ean_codes14 ON wms_ean_codes14_detail.ean_code14_id = wms_ean_codes14.id
      //   INNER JOIN wms_document_details ON wms_ean_codes14.document_detail_id = wms_document_details.id
      //   AND wms_ean_codes14_detail.product_id = wms_document_details.product_id
      //   SET expiration_date = '".substr($expiration_date, 0, 10)."',
      //   batch = '$batch'
      //   WHERE
      //     wms_stock.code_ean14 = '$ean14'");

      DB::commit();
      return response(["message" => 'Inventario ajustado correctamente'], Response::HTTP_CREATED);
    } catch (Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], Response::HTTP_CONFLICT);
      }
      return response(["message" => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getDetailDispatch(Request $request)
  {
    $data = $request->all();

    $document = DB::table('wms_documents')
      // ->leftJoin('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
      // // ->leftJoin('wms_enlist_products', function($query){
      // //     $query->on('wms_documents.id', '=',  'wms_enlist_products.document_id');
      // //     $query->on('wms_document_details.product_id', '=', 'wms_enlist_products.product_id');
      // // })
      ->join("wms_ean_codes14 as e14", "wms_documents.id", "=", "e14.document_id")
      ->join("wms_ean_codes14_detail as e14d", "e14.id", "=", "e14d.ean_code14_id")
      ->join('wms_products', 'e14d.product_id', '=', 'wms_products.id')
      ->selectRaw("
            e14.code14 as code_ean14,
            SUM(  e14d.quanty) as quanty,
            wms_products.reference,
            wms_products.ean as ean13,
            wms_products.description,
            SUM(  e14d.quanty) as unit,
            wms_products.id as product_id,
            wms_documents.id as document_id,
            wms_documents.count_status,
            IFNULL(e14.weight, 0) as weight,
            e14.master
        ")
      ->whereIn('wms_documents.id', $data['number'])
      ->groupBy("e14.id", "wms_products.id")
      ->orderBy("e14.id", "e14.weight")
      ->get();


    return $document;
  }



  public function getTaskByFilter(Request $request)
  {
    $data = $request->all();

    $user = array_key_exists('user', $data) ? $data['user'] : NULL;
    $number_document = array_key_exists('number_document', $data) ? $data['number_document'] : NULL;
    $statusDocument = array_key_exists('status_document', $data) ? $data['status_document'] : NULL;
    $statusTask = array_key_exists('status_task', $data) ? $data['status_task'] : NULL;
    $date_start = array_key_exists('date_start', $data) ? $data['date_start'] : NULL;
    $date_end = array_key_exists('date_end', $data) ? $data['date_end'] : NULL;
    $grouped_task = array_key_exists('grouped_task', $data) ? $data['grouped_task'] : NULL;

    $task = DB::table('wms_schedules')
      ->leftJoin('wms_documents', 'wms_schedules.parent_schedule_id', '=', 'wms_documents.id')
      ->leftJoin(DB::raw("(
            SELECT
                SUM( e14d.quanty ) AS totalDispatch,
                COUNT( DISTINCT e14.id ) AS totalCajas,
                SUM( DISTINCT e14.weight ) AS totalPeso,
                document_id,
                container_id
            FROM
                wms_ean_codes14 AS e14
                JOIN `wms_ean_codes14_detail` AS `e14d` ON `e14`.`id` = `e14d`.`ean_code14_id`
                JOIN wms_products ON e14d.product_id = wms_products.id
            GROUP BY
                e14.document_id
            ) as e14"), function ($join) {
        $join->on('wms_documents.id', '=', 'e14.document_id');
      })
      ->leftJoin('admin_users', 'wms_schedules.user_id', '=', 'admin_users.id')
      ->whereRaw("wms_schedules.name not like '%Ubicar%'");

    if ($user && $user != '') {
      $task = $task->where('wms_schedules.user_id', '=', $user);
    }

    if ($number_document) {
      $task = $task->whereRaw("wms_schedules.name like '%$number_document%'");
    }

    if ($statusDocument && $statusDocument != '') {
      $task = $task->whereRaw("wms_schedules.name like '%$statusDocument%'");
    }

    if ($statusTask && $statusTask != '') {
      $task = $task->where('wms_schedules.status', '=', $statusTask);
    }

    if ($date_start) {
      $task = $task->whereRaw("DATE_FORMAT(wms_schedules.created_at, '%Y-%m-%d') >= '$date_start'");
    }

    if ($date_end) {
      $task = $task->whereRaw("DATE_FORMAT(wms_schedules.updated_at, '%Y-%m-%d') <= '$date_end'");
    }

    if ($grouped_task && $grouped_task == 'Si') {
      $task = $task->groupBy(DB::raw("CASE WHEN wms_schedules.name LIKE '%Picking%' THEN 'picking'
                                            WHEN wms_schedules.name LIKE '%Packing%' THEN 'Packing'
                                            END"));

      $task = $task->selectRaw("
            '' as diferencia,
            SUM(totalDispatch) AS totalDispatch,
            SUM(TIMESTAMPDIFF(MINUTE, wms_schedules.created_at, wms_schedules.updated_at)) / SUM(totalDispatch) as cantidadPorMinuto,
            COUNT(distinct wms_schedules.id) as totalTareas,
            wms_schedules.created_at,
            '' as updated_at,
            CASE
                WHEN wms_schedules.name LIKE '%Picking%' THEN 'Picking'
                WHEN wms_schedules.name LIKE '%Packing%' THEN 'Packing'
                WHEN wms_schedules.name LIKE '%Devolución%' THEN 'Devolución'
                WHEN wms_schedules.name LIKE '%Cancelar%' THEN 'Cancelado'
                WHEN wms_schedules.name LIKE '%Suspender%' THEN 'Suspendido'
                WHEN wms_schedules.name LIKE '%Ubicar%' THEN 'Ubicar'
            END as name,
            wms_schedules.status,
            CASE
                WHEN wms_schedules.status = 'process' THEN 'En proceso'
                WHEN wms_schedules.status = 'closed' THEN 'Terminada'
                ELSE ''
            END as status,
            '' as usuario
            ");
    } else {
      $task = $task->selectRaw("
            TIMEDIFF(wms_schedules.updated_at, wms_schedules.created_at) as diferencia,
            IFNULL(totalDispatch, 0) AS totalDispatch,
            TIMESTAMPDIFF(MINUTE, wms_schedules.created_at, wms_schedules.updated_at) / totalDispatch as cantidadPorMinuto,
            '1' as totalTareas,
            wms_schedules.created_at,
            wms_schedules.updated_at,
            wms_schedules.name,
            wms_schedules.status,
            CASE
                WHEN wms_schedules.status = 'process' THEN 'En proceso'
                WHEN wms_schedules.status = 'closed' THEN 'Terminada'
                ELSE ''
            END as status,
            admin_users.name as usuario
            ");
    }

    return $task->get();
  }

  public function getUsersWithTask(Request $request)
  {
    $company = $request->input('company_id');
    $users = User::with('person', 'role')
      ->join('wms_schedules', 'wms_schedules.user_id', '=', 'admin_users.id')
      ->where('admin_users.company_id', $company)
      ->selectRaw('admin_users.id, admin_users.name')
      ->groupBy('admin_users.id')
      ->orderBy('name')->get();

    return $users->toArray();
  }

  public function getClients($search)
  {
    return Client::where('name', 'LIKE', '%' . $search . '%')->get()->toArray();
  }

  public function getClientsV()
  {
    return Client::with('city')->where('is_vendor', 1)->get()->toArray();
  }

  public function getPackingByFilter(Request $request)
  {
    $data = $request->all();
    $companyId = $request->input('company_id');
    $departure = DocType::Departure;

    $client = array_key_exists('client', $data) ? $data['client'] : NULL;
    // return $client['id'];
    $number_document = array_key_exists('number_document', $data) ? $data['number_document'] : NULL;
    $status = array_key_exists('status', $data) ? $data['status'] : NULL;
    $date_start = array_key_exists('date_start', $data) ? $data['date_start'] : NULL;
    $date_end = array_key_exists('date_end', $data) ? $data['date_end'] : NULL;
    $type_document = array_key_exists('type_document', $data) ? $data['type_document'] : NULL;
    $external_number = array_key_exists('external_number', $data) ? $data['external_number'] : NULL;
    $facturation_number = array_key_exists('facturation_number', $data) ? $data['facturation_number'] : NULL;

    $document = DB::table('wms_documents')
      ->join('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
      ->leftJoin(DB::raw("(
            SELECT
                SUM(totalDispatch) totalDispatch,
                SUM( totalCajas ) AS totalCajas,
                SUM( weight ) AS totalPeso,
                document_id,
                container_id
            FROM
                (
                SELECT
                    SUM( e14d.quanty ) AS totalDispatch,
                    COUNT( DISTINCT e14.id ) AS totalCajas,
                    IFNULL( e14.weight, 0 ) AS weight,
                    document_id,
                    container_id
                FROM
                    `wms_documents`
                    INNER JOIN `wms_ean_codes14` AS `e14` ON `wms_documents`.`id` = `e14`.`document_id`
                    INNER JOIN `wms_ean_codes14_detail` AS `e14d` ON `e14`.`id` = `e14d`.`ean_code14_id`
                    INNER JOIN `wms_products` ON `e14d`.`product_id` = `wms_products`.`id`
                GROUP BY
                    `e14`.`id`
                ) AS e14d
            GROUP BY
                e14d.document_id
            ) as e14"), function ($join) {
        $join->on('wms_documents.id', '=', 'e14.document_id');
      })
      ->join(DB::raw("(
            SELECT
                id,
                status,
                parent_schedule_id,
                created_at,
                updated_at
            FROM
                wms_schedules
            WHERE
                ( NAME LIKE '%Gestionar Despacho%' OR NAME LIKE '%Ubicar pedido%' )
                AND status = 'closed'
            GROUP BY
                parent_schedule_id
            ) as task"), function ($join) {
        $join->on('wms_documents.id', '=', 'task.parent_schedule_id');
      })


      ->leftJoin('wms_clients', 'wms_documents.client', '=', 'wms_clients.id')
      ->leftJoin('cities', 'wms_clients.city_id', '=', 'cities.id')
      ->leftJoin('wms_containers', 'e14.container_id', '=', 'wms_containers.id')
      ->leftJoin('wms_schedule_documents', 'wms_schedule_documents.document_id', '=', 'wms_documents.id')
      ->leftJoin('wms_schedules', 'wms_schedules.id', '=', 'wms_schedule_documents.schedule_id')
      ->orderBy("wms_documents.date", 'asc')
      ->groupBy('wms_documents.id')
      ->where('wms_documents.document_type', $departure)
      ->where('wms_documents.company_id', $companyId)
      ->where('wms_documents.status', 'dispatch')
      ->selectRaw("
            wms_documents.id,
            task.updated_at,
            wms_documents.date,
            wms_documents.number,
            wms_documents.external_number,
            wms_documents.guia_transp,
            wms_documents.facturation_number,
            wms_documents.city as name,
            wms_documents.consecutive_dispatch,
            wms_documents.fmm_authorization,
            wms_documents.transportation_company,
            wms_clients.name as clientName,
            SUM( wms_document_details.quanty) as totalUnit,
            IFNULL(totalDispatch, 0) AS totalDispatch,
            IFNULL(totalCajas, 0) AS totalCajas,
            IFNULL(totalPeso, 0) AS totalPeso,
            CASE
                WHEN wms_documents.status = 'picking' THEN 'Picking'
                WHEN wms_documents.status = 'packing' THEN 'Empaque'
                WHEN wms_documents.status = 'transsition' THEN 'Por facturar'
                WHEN wms_documents.status = 'process' THEN 'Por asignar'
                WHEN wms_documents.status = 'cancel' THEN 'Cancelado'
                WHEN wms_documents.status = 'pending_cancel' THEN 'Por cancelar'
                WHEN wms_documents.status = 'suspend' THEN 'Suspendido'
                WHEN wms_documents.status = 'pending_suspend' THEN 'Por suspender'
                WHEN wms_documents.status = 'pending_dispatch' THEN 'Por despachar'
                WHEN wms_documents.status = 'dispatch' THEN 'Despachado'
                ELSE ''
            END as status,
            group_concat(DISTINCT wms_containers.name SEPARATOR '  -  ' ) as nameContainer
        ")
      ->groupBy('wms_documents.id')
      ->orderBy("wms_documents.date", 'asc');

    if ($client) {
      $document = $document->where('wms_clients.id', '=', $client);
    }

    if ($number_document) {
      $document = $document->where('wms_documents.number', '=', $number_document);
    }

    if ($date_start) {
      $document = $document->whereRaw("DATE_FORMAT(task.updated_at, '%Y-%m-%d') >= '$date_start'");
    }

    if ($date_end) {
      $document = $document->whereRaw("DATE_FORMAT(task.updated_at, '%Y-%m-%d') >= '$date_end'");
    }

    if ($type_document) {
      $document = $document->whereRaw("wms_documents.number like '$type_document%' AND totalPeso > 0");
    }

    if ($external_number) {
      $document = $document->where('wms_documents.external_number', '=', $external_number);
    }

    if ($facturation_number) {
      $document = $document->where('wms_documents.facturation_number', '=', $facturation_number);
    }

    return $document->get();
  }

  public function getDocumentPlanT(Request $request)
  {


    $data = $request->all();
    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    // $concept_position = $settingsObj->get('concept_position');

    // return $companyId;
    $documents = DocumentDetail::with('client.city', 'document', 'product.stock.zone_position.zone.warehouse', 'product.stock.ean14', 'product.stock.zone_position.zone.zone_type', 'product.stock.zone_position.concept')
      ->whereHas('document', function ($query) use ($companyId) {
        $query->where('company_id', $companyId);
      })->whereIn('document_id', $data)->get();

    $documents = DB::table('wms_document_details')
      ->Join('wms_documents', 'wms_document_details.document_id', '=', 'wms_documents.id')
      ->Join('wms_clients', 'wms_documents.client', '=', 'wms_clients.id')
      ->Join('wms_products', 'wms_document_details.product_id', '=', 'wms_products.id')
      // ->Join('wms_stock', 'wms_stock.product_id', '=', 'wms_products.id')
      // ->Join('wms_zone_positions', 'wms_zone_positions.id', '=', 'wms_stock.zone_position_id')
      // ->Join('wms_zones', 'wms_zones.id', '=', 'wms_zone_positions.zone_id')
      // ->Join('wms_warehouses', 'wms_warehouses.id', '=', 'wms_zones.warehouse_id')
      // ->Join('wms_zone_concepts', 'wms_zone_concepts.id', '=', 'wms_zone_positions.concept_id')
      // ->Join('wms_zone_types', 'wms_zone_types.id', '=', 'wms_zones.zone_type_id')
      // ->groupBy('wms_document_details.product_id')
      ->whereIn('wms_documents.id', $data)
      ->where('wms_documents.company_id', $companyId)
      ->select('wms_products.ean', 'wms_products.reference', 'wms_document_details.quanty as pedido', 'wms_products.id as product_id', 'wms_documents.warehouse_origin', 'wms_documents.id', 'wms_documents.number', 'wms_clients.id as client')
      ->get();

    foreach ($documents as &$value) {
      // return $value->product_id;
      if ($value->warehouse_origin == 'ECOMM' || $value->warehouse_origin == '001' || $value->warehouse_origin == 'CONPT' || $value->warehouse_origin == 'DEVOLUCIONES FISICAS' || $value->warehouse_origin == 'DEV.FISICAS CALIDAD' || $value->warehouse_origin == 'DFWEB' || $value->warehouse_origin == 'CLIENTE ESPECIAL SOU' || $value->warehouse_origin == 'CON' || $value->warehouse_origin == '269') {
        $value->warehouse_origin = 'ArtMode';
      }
      $inventario = DB::table('wms_stock')
        ->Join('wms_zone_positions', 'wms_zone_positions.id', '=', 'wms_stock.zone_position_id')
        ->Join('wms_zones', 'wms_zones.id', '=', 'wms_zone_positions.zone_id')
        ->Join('wms_warehouses', 'wms_warehouses.id', '=', 'wms_zones.warehouse_id')
        ->Join('wms_zone_concepts', 'wms_zone_concepts.id', '=', 'wms_zone_positions.concept_id')
        ->Join('wms_zone_types', 'wms_zone_types.id', '=', 'wms_zones.zone_type_id')
        ->where('wms_stock.product_id', $value->product_id)
        ->where('wms_zone_concepts.name', $value->warehouse_origin)
        ->select(DB::raw('SUM(wms_stock.quanty) as inventario'), 'wms_zones.name as zone', 'wms_warehouses.name as warehouse', 'wms_zone_concepts.name as concept')
        ->get();
      $value->inventario = $inventario[0]->inventario;
      $value->zone = $inventario[0]->zone;
      $value->warehouse = $inventario[0]->warehouse;
      $value->concept = $inventario[0]->concept;
    }

    return $documents;
  }

  public function Codigos14ByDocument(Request $request)
  {
    $data = $request->all();
    $documentos = array_key_exists('documentos', $data) ? $data['documentos'] : NULL;
    $ids = [];
    foreach ($documentos as $value) {
      $ids[] = $value['id'];
    }
    $codigo = EanCode14::with('document', 'detalles.product', 'document.clientdocument')->whereIn('document_id', $ids)->where('master', null)->get()->toArray();
    foreach ($codigo as &$value) {
      $value['cantidad'] = 0;
      foreach ($value['detalles'] as $value1) {
        $value['cantidad'] += $value1['quanty'];
      }
    }
    return $codigo;
  }

  public function CreateMasterBox(Request $request)
  {
    $data = $request->all();
    $cogidos = array_key_exists('cogidos', $data) ? $data['cogidos'] : NULL;
    $pesoMaster = array_key_exists('peso', $data) ? $data['peso'] : NULL;
    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    $concept_position = $settingsObj->get('ZonaCajaMaster');

    $position = Zone::with('zone_positions')->where('name', $concept_position)->first()->toArray();

    $data14 = EanCode14::orderBy('master', 'desc')->first();
    $code = $data14->master ? $data14->master + 1 : '200000';
    $aux = 0;
    $contador = 0;
    foreach ($cogidos as $value) {
      EanCode14::where('id', $value['id'])->update(['master' => $code]);
      Stock::where('code_ean14', $value['id'])->update(['zone_position_id' => $position['zone_positions'][0]['id']]);
      if (!$aux || $aux !== $value['document_id']) {
        $aux = $value['document_id'];
        $contador = $contador + 1;
      }

      //--------------MASTER BOX-----------
      $box = [
        'code14_id' => $value['id'],
        'master' => $code,
        'peso' => $pesoMaster,
      ];
      MasterBox::create($box);
    }

    $catorces = EanCode14::where('master', $code)->get();
    $peso_total = 0;
    foreach ($catorces as $value) {
      $peso_total += $value['weight'];
    }
    return ['master' => $code, 'peso_total' => number_format($peso_total, 3, '.', ','), 'documentos' => $contador];
  }

  public function documentosEntregados(Request $request, $company)
  {
    // $companyId = DB::table('admin_users')
    // ->where('username', $company)
    // ->value('company_id');

    $data = $request->all();
    // $data =  base64_decode($request->all());
    // return $data;

    if (isset($company)) {

      $numero = explode("-", $data['number']);
      //Check if the document already exists
      $documentId = DB::table('wms_documents')->where('id', $numero[1])->where('company_id', $company)->value('id');

      // return $documentId;

      // $receiptTypeId = DB::table('wms_receipt_types')->where('document_name',$data['receipt_type'])->value('id');

      // $table->string('warehouse_origin')->nullable();
      // $table->string('warehouse_destination')->nullable();

      DB::beginTransaction();

      try {

        if (!$documentId) {
          abort(500, 'No existe este documento' . $numero[1]);
        }

        // $cajas = DB::table('wms_eancodes14_packing')->where('id', $documentId)->get();
        // foreach ($cajas as $value) {
        // return [DB::table('wms_ean_codes14')->where('id', $value->code_ean14)->first()];
        DB::table('wms_documents')->where('id', $documentId)->update(['status' => $data['status'], 'facturation_number' => $data['facturation_number'], 'facturation_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1]]);
        // }
        // {
        // "pin": "3110001",
        // "url_rotulo": "https://storage.googleapis.com/contenedor-rotulos/f52ccdcfd6a77c6132081c1f8fc63036.pdf"
        // }

        //Insert the product features

        DB::commit();

        $this->res['respuesta'] = "Documento actualizado con éxito";
        $this->res['exito']     = 1;
        $this->res['mensaje']   = "Documento actualizado con éxito";
      } catch (\Exception $e) {
        DB::rollBack();

        $this->res['respuesta'] = "No se puede actualizar el Documento";
        $this->res['exito']     = 0;
        $this->res['mensaje']   = $e->getMessage();
      }
    } else {
      $this->res['respuesta'] = "La compañía " . $company . " no existe";
      $this->res['exito'] = 0;
      $this->res['mensaje'] = "Compañía no encontrada";
    }

    return $this->res;
  }

  public function getAllDepartureDocumentF(Request $request)
  {
    $data = $request->all();
    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    $departure = DocType::Departure;

    $username = User::where('id', $data['session_user_id'])->first();

    $document = DB::table('wms_documents')
      // ->leftJoin('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
      ->leftJoin('wms_clients', 'wms_documents.client', '=', 'wms_clients.id')
      ->leftJoin('cities', 'wms_clients.city_id', '=', 'cities.id')
      ->leftJoin('wms_ean_codes14', 'wms_documents.id', '=', 'wms_ean_codes14.document_id')
      ->leftJoin('wms_master_box as mb', 'mb.code14_id', '=', 'wms_ean_codes14.id')
      ->where('wms_documents.document_type', $departure)
      ->where('wms_documents.company_id', $companyId)
      ->where('wms_documents.status', 'Facturado SAYA')
      ->where('wms_documents.date', '>=', '2021-08-01')
      ->select(
        'wms_documents.number',
        'wms_documents.facturation_number',
        'wms_documents.external_number',
        'wms_documents.observation',
        'wms_documents.date',
        'wms_documents.total_cost',
        'total_benefit',
        'wms_documents.client',
        'wms_documents.status',
        'wms_documents.count_status',
        'wms_documents.id',
        'wms_documents.min_date',
        'wms_documents.city as name',
        'wms_documents.lead_time as max_date',
        'cities.dispatch_time',
        'wms_clients.name as clientName',
        'wms_documents.warehouse_origin',
        'wms_documents.warehouse_destination',
        DB::raw("'$username->name' as responsible "),
        "wms_documents.group",
        "wms_documents.master_guide",
        "wms_documents.guia_transp",
        "wms_documents.transportation_company",
        "wms_documents.fmm_authorization",
        DB::raw("GROUP_CONCAT(mb.master,',') as master")
      )
      ->groupBy('wms_documents.id')
      ->get();

    // $document = DB::table('wms_documents')
    //     ->leftJoin('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
    //     ->leftJoin('wms_clients', 'wms_documents.client', '=', 'wms_clients.id')
    //     ->leftJoin('cities', 'wms_clients.city_id', '=', 'cities.id')
    //     ->groupBy('wms_documents.id')
    //     ->where('wms_documents.document_type', $departure)
    //     ->where('wms_documents.company_id', $companyId)
    //     ->select('wms_documents.number', 'wms_documents.total_cost', 'total_benefit', 'wms_documents.client', 'wms_documents.status', 'wms_documents.id', 'wms_documents.min_date', 'cities.name', 'wms_documents.lead_time as max_date', 'cities.dispatch_time', 'wms_clients.name as clientName', DB::raw("'$username->name' as responsible, SUM(wms_document_details.unit) as totalUnit"))
    //     ->get();


    //$document['name_user'] = $username->name;

    return $document;
  }

  public function CreateDocumentTask(Request $request)
  {
    $data = $request->all();
    // return $data;
    $params = array_key_exists('params', $data) ? $data['params'] : NULL;
    $documents = array_key_exists('documents', $data) ? $data['documents'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;

    $documentos = Document::whereIn('wms_documents.id', $documents)
      ->leftjoin('wms_clients', 'client', '=', 'wms_clients.id')
      ->select(
        'wms_documents.id',
        'facturation_number',
        'wms_clients.name'
      )
      ->get();
    $pedidos = "";
    foreach ($documentos as $value) {
      $pedidos .= $value['name'] . ' - ' . $value['facturation_number'] . ';   ';
      Document::where('id', $value['id'])->update(['status' => 'En documentación']);
    }
    // return $params['userId'];

    $taskSchedules = [
      'name' => "Ingresar Documentos para los pedidos: " . $pedidos,
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::Documentos,
      'status' => ScheduleStatus::Process,
      'user_id' => $params['userId']['user']['id'],
      'parent_schedule_id' => '',
      'company_id' => $company_id
    ];
    $schedule = Schedule::create($taskSchedules);

    foreach ($documentos as $value) {
      ScheduleDocument::create(['schedule_id' => $schedule->id, 'document_id' => $value['id']]);
    }
  }

  public function getDocumentByIdS($id)
  {

    $documentos = ScheduleDocument::with('document.clientdocument')->where('schedule_id', $id)->get()->toArray();
    $pedidos = "";
    $ids = [];
    foreach ($documentos as $value) {
      $pedidos .= $value['document']['number'] . '-';
      $ids[] = $value['document']['id'];
    }

    return $documentos;
  }

  public function searchStock($id)
  {
    $stock = DB::table('wms_stock')
      ->Join('wms_zone_positions', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
      ->Join('wms_zones', 'wms_zone_positions.zone_id', '=', 'wms_zones.id')
      ->Join('wms_warehouses', 'wms_zones.warehouse_id', '=', 'wms_warehouses.id')
      ->Join('wms_products', 'wms_stock.product_id', '=', 'wms_products.id')
      ->groupBy('wms_zone_positions.id')
      ->orderBy('wms_zone_positions.code')
      ->where('wms_products.reference', $id)
      ->select('wms_warehouses.name as bodega', 'wms_zone_positions.code as ubicacion', DB::raw('SUM(wms_stock.quanty) as unidades'), 'wms_products.reference')
      ->get();
    $total_stock = 0;
    foreach ($stock as $value) {
      $total_stock += $value->unidades;
    }


    $transition = DB::table('wms_stock_transition')
      ->Join('wms_products', 'wms_stock_transition.product_id', '=', 'wms_products.id')
      ->groupBy('wms_stock_transition.product_id')
      ->orderBy('wms_products.reference')
      ->where('wms_products.reference', $id)
      ->select('wms_products.reference', DB::raw('SUM(wms_stock_transition.quanty) as unidades'))
      ->get();

    $total_transition = 0;
    foreach ($transition as $value) {
      $total_transition += $value->unidades;
    }

    return ['stock' => $stock, 'transition' => $transition, 'total_stock' => $total_stock, 'total_transition' => $total_transition];
  }

  public function saveDocuments(Request $request)
  {
    $data = $request->all();

    //   return $data;
    //Create the register on the table
    $scheduleImage = ScheduleImage::create($data);

    //Now we should use the $scheduleImage->id for the image name
    $png_url = $scheduleImage->id . ".pdf";

    //concat the path
    $file =  "/storage/app/" . $data['name'];
    $path = public_path() . $file;

    //   return $path;

    //Save physically the path
    //   Image::make(file_get_contents($data["uri"]))->encode('jpg', 50)->save($path);
    Storage::disk('local')->put($data['name'], file_get_contents($data["uri"]));

    //Get the http url
    $scheduleImage->url = url('/') . $file;

    //Update the register on the table
    $scheduleImage->save();

    return  $this->response->created();
  }

  public function getDocumentsMaajiR(Request $request)
  {
    $document = Document::with('detail.product', 'detail.ean14.detail', 'clientdocument', 'ean14')
      ->where('document_type', 'receipt')
      ->where('status', 'process')
      ->get();
    return $document->toArray();
  }

  public function getDocumentsEan14(Request $request)
  {
    $document = Document::with('ean14', 'ean14.detail', 'ean14.document', 'clientdocument')
      ->where('document_type', 'receipt')
      ->get();
    return $document->toArray();
  }

  public function getConcepts()
  {

    $stock = DB::table('wms_zone_concepts')
      ->select('wms_zone_concepts.name', 'wms_zone_concepts.id')
      ->get();


    return $stock;
  }

  public function searchStockPosition($position)
  {

    $position = DB::table('wms_zone_positions')
      ->where('wms_zone_positions.code', $position)
      ->select('wms_zone_positions.id')
      ->first();

    if (!$position) {
      return $this->response->error('La posición no existe', 404);
    }

    $stock = Stock::with('zone_position.zone', 'product')->where('zone_position_id', $position->id)->get();


    return $stock->toArray();
  }

  public function sendStockCountPosition(Request $request)
  {
    $data = $request->all();
    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);

    foreach ($data['stocks'] as $value) {
      if ($value['totalFound'] > 0) {
        $consult = StockCountPosition::where('stock_id', $value['id'])->first();
        if ($consult) {
          // return 'entro';
          StockCountPosition::where('stock_id', $value['id'])->delete();
        }

        $document = StockCountPosition::create([
          'product_id' => $value['product_id'],
          'stock_id' => $value['id'],
          'zone_position_id' => $value['zone_position_id'],
          'quanty_stock' => $value['quanty'],
          'quanty_real' => $value['totalFound'],
        ]);

        $chargeUserName = $settingsObj->get('logistic_role');

        $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
          $q->where('name', $chargeUserName);
        })->first();

        if (empty($user)) {
          return $this->response->error('user_not_found', 404);
        }

        $consultS = Schedule::where('parent_schedule_id', $value['id'])->first();
        if ($consultS) {
          Schedule::where('parent_schedule_id', $value['id'])->where('schedule_action', 'Inventario')->where('status', 'process')->delete();
        }

        $taskSchedulesW = [
          'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
          'name' => 'Realizar ajuste inventario:',
          'schedule_type' => ScheduleType::EnlistPlan,
          'schedule_action' => ScheduleAction::Inventario,
          'status' => ScheduleStatus::Process,
          'user_id' => $user->id,
          'parent_schedule_id' => $value['id'],
          'company_id' => $companyId
        ];
        $scheduleW = Schedule::create($taskSchedulesW);
      }
    }
  }

  public function searchStockPositionByStock($stock_id)
  {


    $stock = StockCountPosition::with('zone_position.zone', 'product')->where('stock_id', $stock_id)->get();


    return $stock->toArray();
  }

  public function adjustPosition(Request $request)
  {
    $data = $request->all();

    return Stock::where('id', $data['stock_id'])->update(['quanty' => $data['selected'], 'quanty_14' => $data['selected']]);
  }

  public function saveFileComEx(Request $request)
  {
    $data = $request->all();
    $name = $data['name'];
    $uri = explode(',', $data['uri'])[1];
    $fileStream = fopen(public_path() . '/' . $name, "wb");
    fwrite($fileStream, base64_decode($uri));
    fclose($fileStream);

    return  $this->response->created();
  }

  public function updateBLComExWithDocument($file_name, $documents)
  {
    $folder = public_path();
    // return $documents;
    $datos = Excel::toArray(new ComExImport, $folder . '/' . $file_name);
    if (!$datos) {
      throw new Exception('No se encontraron datos en la plantilla importada');
    }

    foreach ($documents as $dataDocument) {
      foreach ($datos[0] as $pos => $dato) {
        if ($pos !== 0) {
          if (!$dato[0]) {
            throw new RuntimeException("La fila " . ($pos + 1) . " se encuentra vacía. Por favor elimínela.");
          }
          $factura = $dato[1];
          $transportador = $dato[2];
          $guia = $dato[3];
          $fmmAutorizacion = $dato[4];
          $fechaFmmAutorizacion = $dato[5];
          $fechaSalida = $dato[6];
          $grupo = $dato[7];
          $guiaMaster = $dato[8];


          if ($dataDocument->document->facturation_number == $factura) {
            Document::where("id", $dataDocument->document->id)->update([
              "transportation_company" => $transportador,
              "guia_transp" => $guia,
              // "consecutive_dispatch" => $consecutivo,
              "fmm_authorization" => $fmmAutorizacion,
              "sizfra_merchandise_form" => $fmmAutorizacion,
              "datefmm_authorization" => $fechaFmmAutorizacion,
              "departure_date" => $fechaSalida,
              // "status" => "pending_dispatch",
              "group" => $grupo,
              "master_guide" => $guiaMaster,
              "sizfra_merchandise_form" => $fmmAutorizacion,
            ]);
          }
        }
      }
    }
  }

  public function updateBLComExWithOutDocument($file_name)
  {
    $folder = public_path();

    $datos = Excel::toArray(new ComExImport, $folder . '/' . $file_name);
    if (!$datos) {
      throw new Exception('No se encontraron datos en la plantilla importada');
    }

    foreach ($datos[0] as $pos => $dato) {
      if ($pos !== 0) {
        if (!$dato[0]) {
          throw new RuntimeException("La fila " . ($pos + 1) . " se encuentra vacía. Por favor eliminela.");
        }
        $factura = (string)$dato[1];
        $transportador = $dato[2];
        $guia = $dato[3];
        $fmmAutorizacion = $dato[4];
        $fechaFmmAutorizacion = $dato[5];
        $fechaSalida = $dato[6];
        $grupo = $dato[7];
        $guiaMaster = $dato[8];
        Document::where("facturation_number", '=', $factura)->update([
          "transportation_company" => $transportador,
          "guia_transp" => $guia,
          // "consecutive_dispatch" => $consecutivo,
          "fmm_authorization" => $fmmAutorizacion,
          "sizfra_merchandise_form" => $fmmAutorizacion,
          "datefmm_authorization" => $fechaFmmAutorizacion,
          "departure_date" => $fechaSalida,
          "group" => $grupo,
          "master_guide" => $guiaMaster,
          // "status" => "pending_dispatch",
        ]);
      }
    }
  }

  public function getDocumentByTask($id)
  {
    $documentos = ScheduleDocument::join('wms_documents', 'wms_schedule_documents.document_id', 'wms_documents.id')
      ->join('wms_clients', 'wms_documents.client', '=', 'wms_clients.id')
      ->where('schedule_id', $id)
      ->selectRaw(
        '
          wms_documents.facturation_number as facturation_number,
          wms_documents.master_guide as master_guide,
          wms_documents.guia_transp as guia_transp,
          wms_documents.transportation_company as transportation_company,
          wms_clients.name as client,
          wms_documents.id,
          wms_documents.fmm_authorization
        '
      )
      ->groupBy('wms_documents.id')
      ->get();
    return $documentos;
  }

  public function getEanDetailByDocument($documentId)
  {
    $tulas = EanCode14Detail::from("wms_ean_codes14 as ead")
      ->leftjoin("wms_ean_codes14_detail as c", "c.ean_code14_id", "ead.id")
      ->leftjoin("wms_products as p", "c.product_id", "p.id")
      ->where('ead.document_id', $documentId)
      ->get();
    return $tulas->toArray();
  }

  public function getEanDetailInDocuments(Request $request)
  {

    $data = $request->all();
    $documentosIds = $data['documents'];
    $tulas = EanCode14Detail::from("wms_ean_codes14 as ead")
      ->leftjoin("wms_documents as d", "ead.document_id", "d.id")
      ->leftjoin("wms_clients as c", "d.client", "c.id")
      ->whereIn('ead.document_id', $documentosIds)
      ->select("d.number", "ead.facturation_number", "ead.code14", "ead.quanty", "ead.document_id", "ead.status", "ead.id", "ead.consecutive", "ead.company_id")
      ->get();
    return $tulas->toArray();
  }

  /**
   *  Creación de tarea para generar el despacho y seleccionar el conducto en la siguiente tarea
   * @author Julian Osorio
   */
  public function createTaskDispatch(Request $request)
  {
    $data = $request->all();
    $params = array_key_exists('codes14', $data) ? $data['codes14'] : NULL;
    $user = array_key_exists('params', $data) ? $data['user'] : NULL;
    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    foreach ($params as $key => $value) {
      $documents[$value["document_id"]] = $value["document_id"];
    }
    $nombres = '';
    //Creamos los nombres para documentos
    foreach ($documents as $key => $value) {
      $documentModel = Document::find($key);
      $nombres = $nombres . " - " . $documentModel->number;
    }
    $chargeUserName = $settingsObj->get('intermediario');
    $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
      $q->where('name', $chargeUserName);
    })->first();
    if (empty($user)) {
      return $this->response->error('user_not_found', 404);
    }
    $taskSchedulesW = [];
    DB::beginTransaction();
    try {
      $taskSchedulesW = [
        'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
        'name' => 'Programar despacho de O.P:' . $nombres . "  ",
        'schedule_type' => ScheduleType::Task,
        'schedule_action' => ScheduleAction::DispatchTulas,
        'status' => ScheduleStatus::Process,
        'user_id' => $user->id,
        'parent_schedule_id' => null,
        'company_id' => $companyId
      ];
      $tareaDespacho  = Schedule::create($taskSchedulesW);
      //Crear detalle de tarea
      foreach ($documents as $key => $value) {
        ScheduleDocument::create([
          'schedule_id' => $tareaDespacho->id,
          'document_id' => $key
        ]);
      }
      foreach ($params as $key => $value) {
        ScheduleEAN14::create([
          'schedule_id' => $tareaDespacho->id,
          'ean14_id' => $value['id']
        ]);
        EanCode14::where('id', $value['id'])->update(['status' => 12, 'schedule_id' => $tareaDespacho->id]);
      }
      // $this->cambioEstadoDocuments($documents);
      DB::commit();
      return response('Tarea creada con exito', Response::HTTP_OK);
    } catch (Exception $e) {
      DB::rollBack();
      return response($e->getMessage(), Response::HTTP_CONFLICT);
    }
  }

  /**
   *  Creación de tarea para generar el despacho y seleccionar el conducto en la siguiente tarea
   * Cambio estado documents
   * @author Julian Osorio
   */
  public function cambioEstadoDocuments($documents)
  {
    foreach ($documents as $key => $value) {
      $code14 = EanCode14::where('document_id', $key)->get();
      $switch = true;
      foreach ($code14 as $values) {
        if ($values->status != 12) {
          $switch = false;
        }
      }
      if ($switch) {
        Document::where('id', $key)->update(['status' => 'closed']);
      }
    }
  }

  /**
   *  Metodo que se encarga de cerrar los documentos desde panel-control para que ya no se
   * vean reflejados
   * @author Julian Osorio
   */
  public function cerrarDocuments(Request $request)
  {
    $data = $request->all();
    $documents = array_key_exists('documents', $data) ? $data['documents'] : NULL;
    if (!$documents) {
      return $this->response->error('documents_not_found', 404);
    }
    DB::beginTransaction();
    try {
      foreach ($documents as  $value) {
        Document::where('id', $value["id"])->update(['status' => 'closed']);
      }
      DB::commit();
      return response('Documentos cerrados con exito', Response::HTTP_OK);
    } catch (Exception $e) {
      DB::rollBack();
      return response($e->getMessage(), Response::HTTP_CONFLICT);
    }
  }

  /**
   * this task is for validate the entrance to the warehouse
   * @param string $documentType
   * @author Julian Osorio
   */
  public function generateTaskIngresoSizfra(Request $request)
  {
    $data = $request->all();
    $documents = array_key_exists('documents', $data) ? $data['documents'] : NULL;
    if (!$documents) {
      return $this->response->error('documents_not_found', 404);
    }
    $nombresOps = '';
    foreach ($documents as $value) {
      $nombresOps = $nombresOps . ' ' . $value['number'];
    }
    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    $chargeUserName = $settingsObj->get('transport');
    $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
      $q->where('name', $chargeUserName);
    })->first();
    if (empty($user)) {
      return $this->response->error('user_not_found', 404);
    }
    $taskSchedules = [
      'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'name' => 'Ingresar consecutivo Sizfra de O.Ps ' . $nombresOps,
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::ValidateFacturation,
      'status' => ScheduleStatus::Process,
      'user_id' => $user->id,
      'company_id' => $companyId
    ];
    DB::beginTransaction();
    try {
      $schedule = Schedule::create($taskSchedules);
      foreach ($documents as $value) {
        ScheduleDocument::create(['schedule_id' => $schedule->id, 'document_id' => $value['id']]);
      }
      DB::commit();
      return response('Tarea creada con exito', Response::HTTP_OK);
    } catch (Exception $e) {
      DB::rollBack();
      return response($e->getMessage(), Response::HTTP_CONFLICT);
    }
  }

  /*
    * @author Julian Osorio
    * Consulta de documentos para el despacho
    * @params $id es el id de la tarea que estoy ejecutando para consultar los documentos
    */
  public function getDocumentsBySchedule(Request $request)
  {
    $data = $request->all();
    $id = array_key_exists('task_id', $data) ? $data['task_id'] : NULL;
    $tulas = Document::join("wms_schedule_documents as sd", "sd.document_id", "wms_documents.id")
      ->join("wms_clients as c", "c.id", "wms_documents.client")
      ->where('sd.schedule_id', $id)
      ->where('document_type', "receipt")
      // ->where('status', "process")
      ->select('wms_documents.id', 'c.name', 'wms_documents.number', 'wms_documents.facturation_number')
      ->get();
    return $tulas->toArray();
  }

  /*
    * @author Julian Osorio
    * Consulta de conductores para el despacho de tulas a zona franca
    */
  public function getDriverDispatch()
  {
    $document = User::from("admin_users as au")
      ->join("wms_personal as wp", "wp.id", "au.personal_id")
      ->select("au.id", "wp.name")
      ->where("charge_id", "=", "362")
      ->get();
    return $document->toArray();
  }

  /*
    * @author Julian Osorio
    * Consulta de las placas para el despacho de tulas a zona franca
    */
  public function getPlateDriverDispatch()
  {
    $boxDrive = BoxDriver::get();
    return $boxDrive->toArray();
  }

  /**
   * Esta tarea es la encargada de validar que se recibieron las tulas y estan listas para el despacho
   * @param string $documentType
   * @author Julian Osorio
   */
  public function validateOp(Request $request)
  {
    $data = $request->all();
    $id = $data['id'];
    // Document::where('id', $id)->update(['active' => 3]);
    foreach ($data['tulas'] as $value) {
      if (isset($value['receipt']) && $value['status'] == 10) {
        EanCode14::where('id', $value['id'])->update(['weight' => $value['weight'], 'observation_auditor' => $value['observation_auditor'], 'observation_driver' => $value['observation_driver'], 'status' => 11]);
      }
    }
    return $id;
  }


  /**
   * Esta tarea se encnarga de guardar los datos para el envio de las tulas a zona franca,
   * desde aca se crea la tarea para el cargue del camion y para la tarea de gestionar recibo
   * @param string $documentType
   * @author Julian Osorio
   */
  public function generateTaskDispatch(Request $request)
  {
    $data = $request->all();
    $dispatch = array_key_exists('dispatch', $data) ? $data['dispatch'] : NULL;
    $documents = array_key_exists('documents', $data) ? $data['documents'] : NULL;
    $user = User::find($dispatch["driver"]);

    if (!$dispatch) {
      return $this->response->error('data_not_found', 404);
    }
    if (!$documents) {
      return $this->response->error('documents_not_found', 404);
    }
    DB::beginTransaction();
    try {
      $this->generateTaskDriverCitaDispatch($request);
      // $this->gestionarReciboCedi($request);
      foreach ($documents as $value) {
        $personal = Person::find($user->personal_id);
        $vehiculo = BoxDriver::find($dispatch["plate"]);
        $document = Document::find($value['id']);
        $document->driver_name = $personal->name;
        $document->driver_identification = $personal->identification;
        $document->vehicle_plate = $vehiculo->plate;
        $document->warehouse_destination = $dispatch["warehouse"];
        $document->save();
      }
      DB::commit();
      return response('Tarea creada con exito', Response::HTTP_OK);
    } catch (Exception $e) {
      DB::rollBack();
      return response($e->getMessage(), Response::HTTP_CONFLICT);
    }
  }

  /**
   * this task is for validate the entrance to the warehouse
   * Esta tarea genera la cita para el cargue del camion con lod cocumentos que se seleccionaron en panel de control
   * Los documentos se asiganan a la tarea
   * @param string $documentType
   * @author Julian Osorio
   */
  public function generateTaskDriverCitaDispatch($request)
  {
    $data = $request->all();
    $dispatch = array_key_exists('dispatch', $data) ? $data['dispatch'] : NULL;
    $documents = array_key_exists('documents', $data) ? $data['documents'] : NULL;
    $companyId = $request->input('company_id');
    $user = User::find($dispatch["driver"]);


    $documentsName = '';
    foreach ($documents as $value) {
      $documentData = Document::find($value['id']);
      $documentsName = $documentData->number . ', ' . $documentsName . ' ';
    }

    $taskSchedules = [
      'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'name' => 'Despacho Zona Franca ' . $documentsName,
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::DispatchTulasDriver,
      'status' => ScheduleStatus::Process,
      'user_id' => $user->id,
      'company_id' => $companyId
    ];
    $schedule = Schedule::create($taskSchedules);
    foreach ($documents as $value) {
      ScheduleDocument::create(['schedule_id' => $schedule->id, 'document_id' => $value['id']]);
    }
    //Guardamos la información de despacho.
    $personal = Person::find($user->personal_id);
    $vehiculo = BoxDriver::find($dispatch["plate"]);
    $scheduleDispatch = new ScheduleDispatch();
    $scheduleDispatch->schedule_id = $schedule->id;
    $scheduleDispatch->driver_name = $personal->name;
    $scheduleDispatch->driver_identification = $personal->identification;
    $scheduleDispatch->vehicle_plate = $vehiculo->plate;
    $scheduleDispatch->warehouse_id = $dispatch["warehouse"];
    $scheduleDispatch->save();
  }

  /**
   * this task is for gestion rec dispatch
   * @param string $documentType
   * @author Julian Osorio
   */
  public function gestionarReciboCedi($request)
  {
    $data = $request->all();
    $dispatch = array_key_exists('dispatch', $data) ? $data['dispatch'] : NULL;
    $documents = array_key_exists('documents', $data) ? $data['documents'] : NULL;
    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    $chargeUserIdentification = $settingsObj->get('Gestionar_recibo_tulas_cedi');

    $documentsName = '';
    foreach ($documents as $value) {
      $documentData = Document::find($value['id']);
      $documentsName = $documentData->number . ', ' . $documentsName . ' ';
    }


    $user = User::whereHas('person.charge', function ($q) use ($chargeUserIdentification) {
      $q->where('identification', $chargeUserIdentification);
    })->first();
    if (empty($user)) {
      return $this->response->error('user_not_found', 404);
    }
    $taskSchedules = [
      'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'name' => 'Gestionar recibo de O.Ps ' . $documentsName,
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::ManagementReceipt,
      'status' => ScheduleStatus::Process,
      'user_id' => $user->id,
      'company_id' => $companyId
    ];
    $schedule = Schedule::create($taskSchedules);
    foreach ($documents as $value) {
      ScheduleDocument::create(['schedule_id' => $schedule->id, 'document_id' => $value['id']]);
    }
    return $schedule;
  }


  /**
   * Este metodo retorna la data necesaria para generar el despacho de los documentos a zona franca dependiendo de la tarea enviada.
   * @param string $documentType
   * @author Julian Osorio
   */
  public function getDataDispatchTula(Request $request)
  {
    $data = $request->all();
    $taskId = array_key_exists('task_id', $data) ? $data['task_id'] : NULL;
    $documents = ScheduleDocument::where('schedule_id', '=', $taskId)->get();
    $documentsId = [];
    foreach ($documents as $value) {
      $documentsId[] = $value->document_id;
    }
    $documentsData = Document::whereIn("id", $documentsId)->get();
    foreach ($documentsData as $value) {
      $ean14 = EanCode14::where('document_id', $value->id)->where('status', 12)->get();
      $value->details = $ean14;
      $value->totalDetails = isset($ean14) ? count($ean14) : 0;
      $peso = 0;
      foreach ($ean14 as $detail) {
        $peso = ($detail->weight ?? 0 + $peso ?? 0);
      }
      $value->totalPeso = $peso;
    }
    return [
      "documents" => $documentsData
    ];
  }

  /**
   * Metodo que actualiza las tulas que seran despachadas.
   * @param string $documentType
   * @author Julian Osorio
   */
  public function updateDispatchTulas(Request $request)
  {
    $data = $request->all();
    $params = array_key_exists('detailsDispatch', $data) ? $data['detailsDispatch'] : NULL;
    DB::beginTransaction();
    try {
      // $schedule = $this->gestionarReciboCedi($request);
      $schedule = $this->generateTaskPesoTulas($request);

      foreach ($params as $value) {
        foreach ($value as  $detail) {
          $isSelected = $detail["isSelected"] ?? 0;
          if ($isSelected == 1) {
            EanCode14::where('id', $detail['id'])->update(['status' => 13, 'schedule_id' => $schedule->id]);
          } else {
            EanCode14::where('id', $detail['id'])->update(['status' => 11, 'schedule_id' => null]);
          }
        }
      }
      DB::commit();
      return response('Proceso ejecutado con exito', Response::HTTP_OK);
    } catch (Exception $e) {
      DB::rollBack();
      return response($e->getMessage(), Response::HTTP_CONFLICT);
    }
  }

  /**
   * this task is for gestion peso.
   * @param string $documentType
   * @author Julian Osorio
   */
  public function generateTaskPesoTulas($request)
  {
    $data = $request->all();
    $dispatch = array_key_exists('dispatch', $data) ? $data['dispatch'] : NULL;
    $documents = array_key_exists('documents', $data) ? $data['documents'] : NULL;
    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    $chargeUserIdentification = $settingsObj->get('Gestionar_recibo_tulas_cedi');

    $documentsName = '';
    foreach ($documents as $value) {
      $documentData = Document::find($value['id']);
      $documentsName = $documentData->number . ', ' . $documentsName . ' ';
    }

    $user = User::whereHas('person.charge', function ($q) use ($chargeUserIdentification) {
      $q->where('identification', $chargeUserIdentification);
    })->first();
    if (empty($user)) {
      return $this->response->error('user_not_found', 404);
    }
    $taskSchedules = [
      'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'name' => 'Ingresar peso tulas ' . $documentsName,
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::TulasWeight,
      'status' => ScheduleStatus::Process,
      'user_id' => $user->id,
      'company_id' => $companyId
    ];
    $schedule = Schedule::create($taskSchedules);
    foreach ($documents as $value) {
      ScheduleDocument::create(['schedule_id' => $schedule->id, 'document_id' => $value['id']]);
    }
    return $schedule;
  }


  /**
   * Este metodo retorna la data para la tarea de gestion recibo en zona franca
   * @param string $documentType
   * @author Julian Osorio
   */
  public function getDataManagementReceipt(Request $request)
  {
    $data = $request->all();
    $taskId = array_key_exists('task_id', $data) ? $data['task_id'] : NULL;
    $documents = ScheduleDocument::where('schedule_id', '=', $taskId)->get();
    $documentsId = [];
    foreach ($documents as $value) {
      $documentsId[] = $value->document_id;
    }
    $documentsData = Document::whereIn("id", $documentsId)->get();
    foreach ($documentsData as $value) {
      $ean14 = EanCode14::where('document_id', $value->id)->get();
      $value->details = $ean14;
      $value->totalDetails = isset($ean14) ? count($ean14) : 0;
      $peso = 0;
      foreach ($ean14 as $key => $detail) {
        $peso = ($detail->weight ?? 0 + $peso ?? 0);
      }
      $value->totalPeso = $peso;
    }
    return [
      "documents" => $documentsData
    ];
  }

  /**
   * Esté metodo es para recibo, desde este metodo se crea la tarea para recibir las tulas enviadas
   * @author Julian Osorio
   */
  public function generateTaskReceiptTulas(Request $request)
  {
    $data = $request->all();
    $dataModel = array_key_exists('data', $data) ? $data['data'] : NULL;
    $managementReceipt = array_key_exists('managementReceipt', $data) ? $data['managementReceipt'] : NULL;
    $taskId = array_key_exists('task_id', $data) ? $data['task_id'] : NULL;
    $companyId = $request->input('company_id');

    if (!$data) {
      return $this->response->error('data_not_found', 404);
    }
    if (!$taskId) {
      return $this->response->error('task_not_found', 404);
    }
    if (!$managementReceipt) {
      return $this->response->error('managementReceipt_not_found', 404);
    }
    $documentsTask = ScheduleDocument::where('schedule_id', '=', $taskId)->get();
    if (!$documentsTask) {
      return $this->response->error('No se encontraron documentos programar el recibo.', 404);
    }
    $documentsName = '';
    foreach ($documentsTask as $value) {
      $documentData = Document::find($value->document_id);
      $documentsName = $documentData->number . ',' . $documentsName . ' ';
    }
    DB::beginTransaction();
    try {
      foreach ($dataModel["personal"] as $value) {
        $user = User::where("personal_id", $value["personal_id"])->first();
        $taskSchedules = [
          'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
          'name' => 'Recibo de O.Ps ' . $documentsName,
          'schedule_type' => ScheduleType::Task,
          'schedule_action' => ScheduleAction::ReceiveTulas,
          'status' => ScheduleStatus::Process,
          'user_id' => $user->id,
          'company_id' => $companyId
        ];
        $schedule = Schedule::create($taskSchedules);
        //Guardamos los documentos de la tarea anterior para saber cuales van a ser recibidas.
        foreach ($documentsTask as $valueDoct) {
          ScheduleDocument::create(['schedule_id' => $schedule->id, 'document_id' => $valueDoct->document_id]);
        }
        EanCode14::where('schedule_id', $taskId)->update(['schedule_id' => $schedule->id]);
      }
      DB::commit();
      return response('Tarea creada con exito', Response::HTTP_OK);
    } catch (Exception $e) {
      DB::rollBack();
      return response($e->getMessage(), Response::HTTP_CONFLICT);
    }
  }

  /**
   * Este metodo retorna la data para los recibos de las tulas  en zona franca.
   * @param string $documentType
   * @author Julian Osorio
   */
  public function getDataReceipt(Request $request)
  {
    $data = $request->all();
    $taskId = array_key_exists('task_id', $data) ? $data['task_id'] : NULL;
    $documents = ScheduleDocument::where('schedule_id', '=', $taskId)->get();
    $documentsId = [];
    foreach ($documents as $value) {
      $documentsId[] = $value->document_id;
    }
    $documentsData = Document::whereIn("id", $documentsId)->get();
    foreach ($documentsData as $value) {
      $ean14 = EanCode14::from("wms_ean_codes14 as cd")
        ->leftjoin("wms_ean_codes14_detail as wdd", "wdd.ean_code14_id", "=", "cd.id")
        ->leftjoin("wms_products as p", "wdd.product_id", "=", "p.id")
        ->leftjoin("wms_document_details as dd", function ($query) {
          $query->on("dd.product_id", "=", "p.id")
            ->on("dd.document_id", "=", "cd.document_id");
        })
        ->where('cd.document_id', $value->id)
        ->where('cd.status', 13)
        ->where('cd.schedule_id', $taskId)
        ->groupBy("wdd.id")
        ->select(
          "cd.*",
          "p.reference",
          "p.description",
          "wdd.quanty_receive",
          "wdd.id as ean_code14_detail_id",
          "dd.id as document_detail_id",
          "wdd.good_receive",
          "wdd.sin_conf_receive",
          "wdd.seconds_receive",
          "wdd.good_pallet",
          "wdd.seconds_pallet",
          "wdd.sin_conf_pallet",
          "dd.product_id",
          "p.ean as code13",
          "wdd.quanty",
        )
        ->get();
      $value->details = $ean14;
      $peso = 0;
      $recibido = 0;
      $total13 = 0;
      foreach ($ean14 as $key => $detail) {
        $peso = ($detail->weight ?? 0 + $peso ?? 0);
        $recibido = ($detail->quanty_receive + $recibido);
        $total13 = isset($ean14) ? ($detail->quanty + $total13) : 0;
      }
      $value->totalDetails13 = $total13;
      $value->totalPeso = $peso;
      $value->totalDetailsRecibido = $recibido;
      $tulas = EanCode14::where('document_id', $value->id)
        ->where('status', 13)
        ->where('schedule_id', $taskId)
        ->get();
      $value->totalDetails = isset($tulas) ? count($tulas) : 0;
    }
    return [
      "documents" => $documentsData
    ];
  }

  /**
   * Este metodo que actualiza las placa de los documentos.
   * @author Julian Osorio
   */
  public function setPlateReceipt(Request $request)
  {
    $data = $request->all();
    $taskId = array_key_exists('task_id', $data) ? $data['task_id'] : NULL;
    $plate = array_key_exists('plate', $data) ? $data['plate'] : NULL;
    $documents = ScheduleDocument::where('schedule_id', '=', $taskId)->get();
    $documentsId = [];
    foreach ($documents as $value) {
      $documentsId[] = $value->document_id;
    }
    $documentsData = Document::whereIn("id", $documentsId)->get();
    foreach ($documentsData as $value) {
      $value->vehicle_plate =  $plate;
      $value->save();
    }
    return response('Se actualiza correctamente.', Response::HTTP_OK);
  }

  /**
   * Este metodo actualiza la tarea de recibo y la cierra.
   * @author Julian Osorio
   */
  public function closeTaskReceipt(Request $request)
  {
    $data = $request->all();
    $documents = array_key_exists('documents', $data) ? $data['documents'] : [];
    foreach ($documents as $value) {
      foreach ($value["details"] as $value) {
        EanCode14::where('id', $value['id'])->update(['status' => 14]);
      }
    }
    return response('Se actualiza correctamente.', Response::HTTP_OK);
  }


  public function patsServiceByPacking($id, Request $request)
  {
    $document = Document::with('detail.product', 'enlistplan.product')->where('id', $id)->first()->toArray();
    $companyId = $request->input('company_id');
    $detalles = [];
    foreach ($document['enlistplan'] as $value) {
      if ($value['picked_quanty'] > 0) {
        $detalles[] = [
          "quantity" => $value['picked_quanty'],
          "code" => $value['product']['reference']
        ];
      }
    }
    $obje = [
      "number" => $document['number'],
      "id_number" => $document['id'],
      "detail" => $detalles
    ];

    $objeto = [
      'user' => env("API_SAYA_USERNAME", "ptolomeo"),
      'pass' => env("API_SAYA_PASSWORD", "PTML*_2250")
    ];

    $res = SoberanaServices::getToken($objeto, $companyId);
    $porciones = explode(":", $res);
    $porciones1 = explode('"', $porciones[1]);
    $token = explode('"', $porciones1[1]);

    $respuesta = SoberanaServices::saveOrder($obje, $token[0], $companyId);

    $vuelta = explode(":", $respuesta);
    $vuelta1 = explode('"', $vuelta[0]);

    if ($vuelta1[1] != 'error') {
      Document::where('id', $id)->update(['status' => 'Por facturar SAYA', 'send_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1]]);
    }
  }

  /**
   * Este metodo actualiza la tarea de recibo y la cierra.
   * @author Julian Osorio
   */
  public function saveArchivoDocuments(Request $request)
  {
    DB::beginTransaction();
    try {
      $data = $request->all();
      $dataDocument = $data["documents"];
      $dataDocumentosWms = $data["documentos"];
      foreach ($dataDocumentosWms as $valorDocumento) {
        foreach ($dataDocument as $value) {
          $folder = env("AWS_FOLDER");
          Storage::disk('s3')->put($folder . '/' . $value['name'], file_get_contents($value["uri"]), 'public');

          if (isset($value["schedule_id"])) {
            $scheduleImage = new ScheduleImage();
            $scheduleImage->schedule_id = $value["schedule_id"];
            $scheduleImage->document_id = $valorDocumento["id"];
            $scheduleImage->name_file = $value['name'];
            $scheduleImage->url = $folder . '/' . $value['name'];
            $scheduleImage->save();
          }
        }
      }
      DB::commit();
      return response($data, 200);
    } catch (Exception $e) {
      DB::rollBack();
      return response(["message" => $e->getMessage()], 500);
    }
  }

  public function saveImage($data, $valorDocumento)
  {
    $file =  "/documents/comex/" . $data['name'];
    $path = public_path() . $file;
    Image::make(file_get_contents($data["uri"]))->encode('jpg', 50)->save($path);
    if (isset($data["schedule_id"])) {
      $scheduleImage = new ScheduleImage();
      $scheduleImage->schedule_id = $data["schedule_id"];
      $scheduleImage->document_id = $valorDocumento["id"];
      $scheduleImage->name_file = $data['name'];
      $scheduleImage->url = url('/') . $file;
      $scheduleImage->save();
    }
    return  $this->response->created();
  }

  public function getDocumentsDateTulas(Request $request)
  {
    $data = $request->all();
    $client_id = array_key_exists('client_id', $data) ? $data['client_id'] : NULL;
    $document_id = array_key_exists('document_id', $data) ? $data['document_id'] : NULL;
    $document = Document::with('detail.product', 'detail.ean14.detail', 'clientdocument', 'ean14')
      ->where('document_type', 'receipt')
      ->where('status', 'process')
      ->when($client_id, function ($query) use ($client_id) {
        $query->where('client', $client_id);
      })
      ->when($document_id, function ($query) use ($document_id) {
        $query->where('id', $document_id);
      })
      ->get();
    return $document->toArray();
  }

  public function getCodesEan14ByDocument($id)
  {
    $codes14 = EanCode14::where('document_id', $id)
      ->get();
    return  $codes14->toArray();
  }

  public function getConfeccionistas()
  {
    $confeccionistas = Client::where('is_vendor', 1)->get();
    return  $confeccionistas->toArray();
  }

  public function getDocumentosConsult(Request $request)
  {
    $data = $request->all();
    $companyId = $request->input('company_id');
    $formularioDespacho = $request->get('formularioDespacho', null);
    $guiaMaster = $request->get('guiaMaster', null);
    $factura = $request->get('factura', null);
    $number = $request->get('number', null);
    $username = User::with('role')->find($data['session_user_id']);
    $documentos = DB::table('wms_documents')
      ->leftJoin('wms_clients', 'wms_documents.client', '=', 'wms_clients.id')
      ->leftJoin('cities', 'wms_clients.city_id', '=', 'cities.id')
      ->groupBy('wms_documents.id')
      ->where('wms_documents.company_id', $companyId)
      ->when($formularioDespacho, function ($query) use ($formularioDespacho) {
        $query->where('wms_documents.sizfra_merchandise_form', '=', $formularioDespacho);
      })
      ->when($guiaMaster, function ($query) use ($guiaMaster) {
        $query->where('wms_documents.master_guide', '=', $guiaMaster);
      })
      ->when($factura, function ($query) use ($factura) {
        $query->where('wms_documents.facturation_number', '=', $factura);
      })
      ->when($number, function ($query) use ($number) {
        $query->where('wms_documents.number', '=', $number);
      })
      ->select(
        'wms_clients.name as clientName',
        'wms_documents.external_number',
        'wms_documents.number',
        'wms_documents.facturation_number',
        'wms_documents.observation',
        'wms_documents.date',
        'wms_documents.total_cost',
        'total_benefit',
        'wms_documents.client',
        'wms_documents.status',
        'wms_documents.count_status',
        'wms_documents.id',
        'wms_documents.min_date',
        'wms_documents.city as name',
        'wms_documents.lead_time as max_date',
        'cities.dispatch_time',
        'wms_documents.warehouse_origin',
        'wms_documents.warehouse_destination',
        "wms_documents.group",
        "wms_documents.master_guide",
        "wms_documents.guia_transp",
        "wms_documents.state",
        "wms_documents.country",
        "wms_documents.transportation_company",
        "wms_documents.sizfra_merchandise_form",
        "wms_documents.fmm_authorization"
      )
      ->get();
    foreach ($documentos as  $value) {
      $documentosIds[] = $value->id;
    }
    $adjuntos = ScheduleImage::whereIn("document_id", $documentosIds)->groupBy('name_file')->get();
    return [
      'documentos' => $documentos,
      'adjuntos' => $adjuntos,
      'rol' => $username
    ];
  }


  public function getTulasPeso($id)
  {
    $tulas = EanCode14::with('detail.product', 'document')
      ->where('schedule_id', $id)
      ->where('status', 13)
      ->get();
    foreach ($tulas as  $value) {
      $documentosIds[] = $value->document_id;
    }
    $documentos = Document::whereIn("id", $documentosIds)->get();

    return [
      'details' => $tulas->toArray(),
      'documents' => $documentos
    ];
  }

  /**
   * Metodo que actualiza las tulas que seran despachadas.
   * @param string $documentType
   * @author Julian Osorio
   */
  public function updatePesoTulas(Request $request)
  {
    $data = $request->all();
    $params = array_key_exists('details', $data) ? $data['details'] : NULL;
    DB::beginTransaction();
    try {
      $schedule = $this->gestionarReciboCedi($request);
      foreach ($params as $value) {
        EanCode14::where('id', $value['id'])->where('status', 13)->update(['weight' => $value['weight'], 'schedule_id' => $schedule->id]);
      }
      DB::commit();
      return response('Proceso ejecutado con exito', Response::HTTP_OK);
    } catch (Exception $e) {
      DB::rollBack();
      return response($e->getMessage(), Response::HTTP_CONFLICT);
    }
  }

  /**
   * Metodo que elimina los archivos cargados de comex
   * @param string $documentType
   * @author Julian Osorio
   */
  public function deleteArchivoComex($id)
  {
    DB::beginTransaction();
    try {
      $registro = ScheduleImage::find($id);
      Storage::disk('s3')->delete($registro->url);
      ScheduleImage::where('name_file', $registro->name_file)->delete();
      DB::commit();
      return response('Proceso ejecutado con exito', Response::HTTP_OK);
    } catch (Exception $e) {
      DB::rollBack();
      return response($e->getMessage(), Response::HTTP_CONFLICT);
    }
  }

  /**
   * Este metodo actualiza la tarea de recibo y la cierra.
   * @author Julian Osorio
   */
  public function guardarArchivo(Request $request)
  {
    DB::beginTransaction();
    try {
      $data = $request->all();
      $dataDocument = $data["documents"];
      $ext  = $data["ext"];
      foreach ($dataDocument as $value) {
        $folder = env("AWS_FOLDER");
        Storage::disk('s3')->put($folder . '/' . $data['name'], file_get_contents($data["uri"]), 'public');

        $scheduleImage = new ScheduleImage();
        $scheduleImage->document_id = $value["id"];
        $scheduleImage->name_file = $data['name'];
        $scheduleImage->url = $folder . '/' . $data['name'];
        $scheduleImage->save();
      }
      DB::commit();
      return response($data, 200);
    } catch (Exception $e) {
      DB::rollBack();
      return response(["message" => $e->getMessage()], 500);
    }
  }

  /**
   * Metodo que elimina los archivos cargados de comex
   * @param string $documentType
   * @author Julian Osorio
   */
  public function abrirArchivo(Request $request)
  {
    $data = $request->all();
    $url = $data['url'];
    $assetPath = Storage::disk('s3')->url($url);
    return $assetPath;
  }

  /**
   * Este metodo es un refactor, ya que esta funcion ya estaba pero se le hicieron cambios en el proceso
   * @author Julian Osorio
   */
  public function dispatch(Request $request)
  {
    $data = $request->all();
    $data['status'] = ScheduleStatus::Process;
    $companyId = $request->input('company_id');
    $data['name'] = "Despacho";
    $schedule_id = $data['schedule_id'];
    // $data['schedule_receipt']['company'] = $companyId;
    $data['schedule_receipt']['warehouse_id'] = $data['warehouse_id'];
    $scheduleRow = Schedule::create($data);
    Schedule::where('id', $scheduleRow->id)->update(['status' => 'closed']);
    $concat = '';
    $groups = '';
    $grupo = '';
    foreach ($data['documents'] as &$value) {
      Document::where('id', $value['id'])->update(['status' => 'pending_dispatch']);
      $documento = Document::where('wms_documents.id', $value['id'])
        ->join('wms_clients', 'client', '=', 'wms_clients.id')
        ->select(
          'wms_documents.id',
          'facturation_number',
          'wms_clients.name',
          'group'
        )
        ->first();
      $concat .= $documento->name . ' - ' . $documento->facturation_number . '; ';
      $groups .= $documento->group == $grupo ? '' : $documento->group . ', ';
      $grupo = $documento->group;
    }

    $scheduleReceipt = new ScheduleDispatch($data['schedule_receipt']);
    $schedule_personal = array_key_exists('schedule_personal', $data) ? $data['schedule_personal'] : null;
    $scheduleRow->schedule_dispatch()->save($scheduleReceipt);

    $taskName = '';
    $taskName = 'Despachar Camión Grupo: ' . $groups . ' Cliente: ' . $concat;
    $taskSchedules = [];
    if ($schedule_personal) {
      foreach ($schedule_personal as $row) {
        $user = $row['user'];
        $taskSchedules[] = [
          'start_date' => $data['start_date'],
          'end_date' => $data['end_date'],
          'name' => $taskName,
          'schedule_type' => ScheduleType::Deliver,
          'status' => ScheduleStatus::Process,
          'notified' => false,
          'user_id' => $user['id'],
          'schedule_action' => ScheduleAction::Dispatch,
          'parent_schedule_id' => $scheduleRow->id,
          'company_id' => $companyId
        ];
      }
      foreach ($data['documents'] as &$value) {
        $value['document_id'] = $value['id'];
        $obje = [
          "document_id" => $value['id'],
          "schedule_id" => $scheduleRow->id
        ];
        DocumentSchedule::create($obje);
      }
    }
    Schedule::insert($taskSchedules);


    // if (array_key_exists('schedule_documents', $data)) {
    //     $schedule->schedule_documents()->createMany($data['schedule_documents']);
    // }
    if (array_key_exists('schedule_count', $data)) {
      $scheduleRow->schedule_count()->createMany($data['schedule_count']);
    }
    Schedule::where('id', $schedule_id)->update(['status' => 'closed']);
    return $this->response->item($scheduleRow, new ScheduleTransformer)->setStatusCode(201);
  }

  public function updateDriverDispatch(Request $request)
  {
    $scheduleId = $request->input('scheduleId', null);
    $company = $request->get('company', null);
    $tranport_indentity = $request->get('tranport_indentity', null);
    $driver_phone = $request->get('phone', null);
    $vehicle_plate = $request->get('vehicle_plate', null);
    $warehouse_id = $request->get('warehouse_id', null);

    // $personal = Person::find($user->personal_id);
    // $vehiculo = BoxDriver::find($dispatch["plate"]);
    $scheduleDispatch = new ScheduleDispatch();
    $scheduleDispatch->schedule_id = $scheduleId;
    $scheduleDispatch->company = $company;
    $scheduleDispatch->driver_identification = $tranport_indentity;
    $scheduleDispatch->vehicle_plate = $vehicle_plate;
    $scheduleDispatch->warehouse_id = $warehouse_id;
    $scheduleDispatch->driver_phone = $driver_phone;
    $scheduleDispatch->save();
    return response($scheduleDispatch, 200);
  }

  /**
   * Método que consulta la información de los documentos en inventario
   * para validar si están disponibles las referencias en el proceso de alocación
   * @author Santiago Muñoz
   */
  public function getDocumentPlanMassive(Request $request)
  {
    $data = $request->all();
    $companyId = $request->input('company_id');

    $documents = DB::table('wms_document_details')
      ->Join('wms_documents', 'wms_document_details.document_id', '=', 'wms_documents.id')
      ->Join('wms_clients', 'wms_documents.client', '=', 'wms_clients.id')
      ->Join('wms_products', 'wms_document_details.product_id', '=', 'wms_products.id')
      ->leftJoin('wms_waves as ww', function ($join) {
        $join->where(DB::raw("FIND_IN_SET(wms_documents.id, ww.documents)"), "<>", "0");
      })
      ->leftJoin('wms_enlist_products_waves as wepw', function ($query) {
        $query->on('ww.id', '=', 'wepw.wave_id')
          ->on('wms_document_details.product_id', '=', 'wepw.product_id');
      })
      ->whereIn('wms_documents.id', $data)
      ->where('wms_documents.company_id', $companyId)
      ->select('wms_products.ean', 'wms_products.reference', DB::raw('SUM(wms_document_details.quanty) as pedido'), 'wms_products.id as product_id', 'wms_documents.warehouse_origin', 'wms_documents.number', 'wms_clients.id as client', DB::raw('SUM(wms_document_details.quanty - IFNULL(wepw.quanty, 0)) as missing_assing'), DB::raw('GROUP_CONCAT(DISTINCT wms_documents.id) as document_id'))
      ->groupBy('wms_products.id')
      ->havingRaw('missing_assing > 0')
      ->get();

    foreach ($documents as &$value) {
      if ($value->warehouse_origin == 'ECOMM' || $value->warehouse_origin == '001' || $value->warehouse_origin == 'CONPT' || $value->warehouse_origin == 'DEVOLUCIONES FISICAS' || $value->warehouse_origin == 'DEV.FISICAS CALIDAD' || $value->warehouse_origin == 'DFWEB' || $value->warehouse_origin == 'CLIENTE ESPECIAL SOU' || $value->warehouse_origin == 'CON' || $value->warehouse_origin == '269') {
        $value->warehouse_origin = 'ArtMode';
      }

      $value->inventario = 0;

      $inventario = DB::table('wms_stock')
        ->Join('wms_zone_positions', 'wms_zone_positions.id', '=', 'wms_stock.zone_position_id')
        ->Join('wms_zone_concepts', 'wms_zone_concepts.id', '=', 'wms_zone_positions.concept_id')
        ->where('wms_stock.product_id', $value->product_id)
        ->where('wms_zone_concepts.name', $value->warehouse_origin)
        ->select(DB::raw('SUM(IFNULL(wms_stock.quanty, 0)) as inventario'))
        ->groupBy('wms_stock.product_id')
        ->first();

      if ($inventario) {
        $value->inventario = $inventario->inventario;
      }
    }
    return ['data' => $documents, 'uuid' => SoberanaServices::getUUID()];
  }

  /**
   * Método que contiene el proceso algoritmicoo para generar el picking masivo por referencia y empleado
   * asignando la cantidad por empleado
   * @author Santiago Muñoz
   */
  public function createPickingMassive(Request $request)
  {
    $data = $request->all();
    $params = array_key_exists('params', $data) ? $data['params'] : NULL;
    $documents = array_key_exists('documents', $data) ? substr($data['documents'], 0, -1) : NULL;
    $references = array_key_exists('references', $data) ? $data['references'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $uuid = array_key_exists('uuid', $data) ? $data['uuid'] : NULL;

    DB::beginTransaction();

    try {
      $wave = $this->updateOrCreateWave($uuid, $documents);

      foreach ($references as $dataReference) {
        $calculo = self::calculateQuantyByEmpleado($dataReference['assign_quanty'], $dataReference['personal']);
        $client = Client::where('id', $dataReference['client'])->first();
        $waveEnlistProduct = $this->updateOrCreateWaveEnlistProduct($wave, $dataReference);

        foreach ($dataReference['personal'] as $idx => $dataEmpleados) {
          if ($calculo['extra'] == 0) {
            $dataReference['personal'][$idx]['assign_quanty'] = $calculo['unidades'];
          } else {
            if ($idx !== (count($dataReference['personal']) - 1)) {
              $dataReference['personal'][$idx]['assign_quanty'] = $calculo['unidades'];
            } else {
              $dataReference['personal'][$idx]['assign_quanty'] = $calculo['extra'];
            }
          }

          $waveEnlistProductUser = $this->updateOrCreateWaveEnlistProductUser($waveEnlistProduct, $dataReference['personal'][$idx]['assign_quanty'], $dataEmpleados['user']['id']);

          $taskSchedulesW = [
            'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
            'end_date' => $params['end_date'],
            'name' => 'Realizar picking de: ' . $client->name . ' para la referencia ' . $dataReference['reference'],
            'schedule_type' => ScheduleType::Task,
            'schedule_action' => ScheduleAction::PickingMassiveAction,
            'status' => ScheduleStatus::Process,
            'user_id' => $dataEmpleados['user']['id'],
            'parent_schedule_id' => $waveEnlistProductUser->id,
            'company_id' => $company_id
          ];
          $schedule = Schedule::create($taskSchedulesW);

          $documentsArray = explode(',', $documents);
          foreach ($documentsArray as $dataDocuments) {
            ScheduleDocument::create(['schedule_id' => $schedule->id, 'document_id' => $dataDocuments]);
            Document::where('id', $dataDocuments)->update(['status' => 'picking']);
          }
        }
      }
      DB::commit();
      return response('', 200);
    } catch (\Exception $e) {
      DB::rollBack();
      return response($e->getMessage(), 500);
    }
  }

  /**
   * Método que contiene el proceso algoritmico para generar el picking masivo por referencia y empleado
   * asignando la cantidad por empleado, distribuyendo la cantidad de referencias en los empleados seleccionados
   * @author Santiago Muñoz
   */
  public function createPickingMassiveByReferences(Request $request)
  {
    $data = $request->all();
    $params = array_key_exists('params', $data) ? $data['params'] : NULL;
    $documents = array_key_exists('documents', $data) ? substr($data['documents'], 0, -1) : NULL;
    $references = array_key_exists('references', $data) ? $data['references'] : NULL;
    $personal = array_key_exists('personal', $data) ? $data['personal'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $uuid = array_key_exists('uuid', $data) ? $data['uuid'] : NULL;

    DB::beginTransaction();

    try {

      $empleados = count($personal);
      $unidades = count($references);
      $resultado = $unidades / $empleados;
      $extra = 0;

      if (is_float($resultado)) {
        $sobrante = $unidades % $empleados;
        $resultado = floor($resultado);
        $extra =  $sobrante + $resultado;
      }
      $arrayPos = [];
      for ($i = 0; $i < count($personal); $i++) {
        if ($i != (count($personal) - 1)) {
          $arrayPos[] = ['empleado' => $personal[$i], 'ref' => $resultado];
        } else {
          if ($extra > 0) {
            $arrayPos[] = ['empleado' => $personal[$i], 'ref' => $extra];
          } else {
            $arrayPos[] = ['empleado' => $personal[$i], 'ref' => $resultado];
          }
        }
      }

      $posInicial = 0;
      $posFinal = 0;
      for ($i = 0; $i < count($personal); $i++) {
        for ($j = 0; $j < count($arrayPos); $j++) {
          if ($personal[$i]['id'] == $arrayPos[$j]['empleado']['id']) {
            $posFinal = $posFinal + ($arrayPos[$j]['ref']);
            for ($k = $posInicial; $k <= $posFinal - 1; $k++) {
              $references[$posInicial]['personal'][0] = $personal[$i];
              $posInicial++;
            }
          }
        }
      }

      $wave = $this->updateOrCreateWave($uuid, $documents);

      foreach ($references as $dataReference) {
        $calculo = self::calculateQuantyByEmpleado($dataReference['assign_quanty'], $dataReference['personal']);
        $client = Client::where('id', $dataReference['client'])->first();
        $waveEnlistProduct = $this->updateOrCreateWaveEnlistProduct($wave, $dataReference);

        foreach ($dataReference['personal'] as $idx => $dataEmpleados) {
          if ($calculo['extra'] == 0) {
            $dataReference['personal'][$idx]['assign_quanty'] = $calculo['unidades'];
          } else {
            if ($idx !== (count($dataReference['personal']) - 1)) {
              $dataReference['personal'][$idx]['assign_quanty'] = $calculo['unidades'];
            } else {
              $dataReference['personal'][$idx]['assign_quanty'] = $calculo['extra'];
            }
          }

          $waveEnlistProductUser = $this->updateOrCreateWaveEnlistProductUser($waveEnlistProduct, $dataReference['personal'][$idx]['assign_quanty'], $dataEmpleados['user']['id']);

          $taskSchedulesW = [
            'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
            'end_date' => $params['end_date'],
            'name' => 'Realizar picking de: ' . $client->name . ' para la referencia ' . $dataReference['reference'],
            'schedule_type' => ScheduleType::Task,
            'schedule_action' => ScheduleAction::PickingMassiveAction,
            'status' => ScheduleStatus::Process,
            'user_id' => $dataEmpleados['user']['id'],
            'parent_schedule_id' => $waveEnlistProductUser->id,
            'company_id' => $company_id
          ];
          $schedule = Schedule::create($taskSchedulesW);

          $documentsArray = explode(',', $documents);
          foreach ($documentsArray as $dataDocuments) {
            ScheduleDocument::create(['schedule_id' => $schedule->id, 'document_id' => $dataDocuments]);
            Document::where('id', $dataDocuments)->update(['status' => 'picking']);
          }
        }
      }
      DB::commit();
      return response('', 200);
    } catch (\Exception $e) {
      DB::rollBack();
      return response($e->getMessage(), 500);
    }
  }

  /**
   * Método que contiene el proceso algoritmico para calcular la cantidad de unidades por empleados
   * asociado a las referencias de la alocación
   * @author Santiago Muñoz
   */
  protected function calculateQuantyByEmpleado($unidades, $arrayEmpleados)
  {
    $empleados = count($arrayEmpleados);
    $resultado = $unidades / $empleados;
    $extra = 0;

    if (is_float($resultado)) {
      $sobrante = $unidades % $empleados;
      $resultado = floor($resultado);
      $extra =  $sobrante + $resultado;
    }

    return ['unidades' => $resultado, 'extra' => $extra];
  }

  /**
   * Método que contiene el proceso algoritmico para calcular la cantidad de unidades por empleados
   * asociado a las referencias de la alocación
   * @author Santiago Muñoz
   */
  protected function calculateQuantyReferencesByEmpleado($unidades, $arrayEmpleados)
  {
    $empleados = count($arrayEmpleados);
    $resultado = $unidades / $empleados;
    $extra = 0;

    if (is_float($resultado)) {
      $sobrante = $unidades % $empleados;
      $resultado = floor($resultado);
      $extra =  $sobrante + $resultado;
    }

    return ['unidades' => $resultado, 'extra' => $extra];
  }

  /**
   * Método que contiene el proceso algoritmicoo para validar si una ola ya existe o se debe
   * crear según su UUID
   * @author Santiago Muñoz
   */
  protected function updateOrCreateWave($uuid, $documents)
  {
    $waves = Waves::where('uuid', $uuid)->first();
    if (!$waves) {
      $waves = new Waves();
      $waves->uuid = $uuid;
      $waves->documents = $documents;
    } else {
      $waves->documents = $documents;
    }
    $waves->save();
    return $waves;
  }

  /**
   * Método que contiene el proceso algoritmicoo para validar si el producto de ola ya existe o se debe
   * crear según la ola padre
   * @author Santiago Muñoz
   */
  protected function updateOrCreateWaveEnlistProduct($wave, $product)
  {
    $waveEnlistProduct = EnlistProductsWaves::where('wave_id', $wave->id)->where('product_id', $product['product_id'])->first();
    if (!$waveEnlistProduct) {
      $waveEnlistProduct = new EnlistProductsWaves();
      $waveEnlistProduct->wave_id = $wave->id;
      $waveEnlistProduct->product_id = $product['product_id'];
      $waveEnlistProduct->order_quanty = $product['pedido'];
      $waveEnlistProduct->quanty = $product['assign_quanty'];
      $waveEnlistProduct->picked_quanty = 0;
    } else {
      $waveEnlistProduct->quanty = $waveEnlistProduct->quanty + $product['assign_quanty'];
    }
    $waveEnlistProduct->save();

    return $waveEnlistProduct;
  }

  /**
   * Método que contiene el proceso algoritmicoo para validar si el usuario de ola producto ya existe o se debe
   * crear según la ola producto padre
   * @author Santiago Muñoz
   */
  protected function updateOrCreateWaveEnlistProductUser($waveEnlistProduct, $quanty, $user)
  {
    $waveEnlistProductUser = EnlistProductsWavesUsers::where('enlistproductwave_id', $waveEnlistProduct->id)->where('user_id', $user)->first();
    if (!$waveEnlistProductUser) {
      $waveEnlistProductUser = new EnlistProductsWavesUsers();
      $waveEnlistProductUser->enlistproductwave_id = $waveEnlistProduct->id;
      $waveEnlistProductUser->user_id = $user;
      $waveEnlistProductUser->quanty = $quanty;
      $waveEnlistProductUser->picked_quanty = 0;
    } else {
      $waveEnlistProductUser->quanty = $waveEnlistProductUser->quanty + $quanty;
    }
    $waveEnlistProductUser->save();

    return $waveEnlistProductUser;
  }

  /**
   * Método que contiene el proceso algoritmico para procesar la unidad mercada desde el picking masivo
   * @author Santiago Muñoz
   */
  public function pickSuggestionMassive(Request $request)
  {
    $settings = new Settings(22);
    $data = $request->all();
    $waveUserId = $data['parent_id'];
    $gunsmithZone = $settings->Get('dispatch_zone');

    DB::beginTransaction();
    try {

      $zone = Zone::where('name', $gunsmithZone)->first();
      $newZonePosition = ZonePosition::where('zone_id', $zone->id)->first();
      $producto = Product::where('ean', $data['ean'])->first();
      if (!$producto) {
        throw new RuntimeException('No existe el producto.');
      }

      $enlistProductWave = EnlistProductsWaves::join('wms_enlist_products_waves_users', 'wms_enlist_products_waves.id', 'wms_enlist_products_waves_users.enlistproductwave_id')
        ->where('wms_enlist_products_waves_users.id', $waveUserId)
        ->where('wms_enlist_products_waves.product_id', $producto->id)
        ->selectRaw('wms_enlist_products_waves.*')
        ->first();

      if ($enlistProductWave) {
        if ($enlistProductWave->picked_quanty == $enlistProductWave->quanty) {
          throw new RuntimeException('No es posible mercar. El producto ya fue mercado en su totalidad.');
        }

        $position = ZonePosition::where('code', $data['zone_position'])->first();
        if (!$position) {
          throw new RuntimeException('No existe la ubicación ingresada.');
        }

        $stock_search = Stock::where('zone_position_id', $position->id)->where('product_id', $producto->id)->first();
        if (!$stock_search) {
          throw new RuntimeException('No hay inventario en la ubicación ingresada.');
        }
        $enlistProductWaveUser = EnlistProductsWavesUsers::find($waveUserId);
        if ($enlistProductWaveUser->picked_quanty == $enlistProductWaveUser->quanty) {
          throw new RuntimeException('No es posible mercar. El producto ya fue mercado en su totalidad en su tarea.');
        }

        $packingWave =  EnlistPackingWaves::where('wave_id', $enlistProductWave->wave_id)->where('product_id', $producto->id)->first();
        if (!$packingWave) {
          $stock_search->decrement('quanty', 1);

          $enlistProductWave->increment('picked_quanty', 1);
          $enlistProductWaveUser->increment('picked_quanty', 1);
          $objeto = [
            'product_id' => $producto->id,
            'zone_position_id' => $newZonePosition->id,
            'quanty' => 1,
            'code128_id' => $stock_search->code128_id,
            'code_ean14' => $stock_search->code_ean14,
            'document_detail_id' => $stock_search->document_detail_id,
            'quanty_14' => 1,
            'good' => $enlistProductWave->good > 0 ? 1 : 0,
            'seconds' => $enlistProductWave->seconds > 0 ? 1 : 0
          ];
          $stockNew = Stock::create($objeto);

          $obj = [
            "wave_id" => $enlistProductWave->wave_id,
            'quanty' => 1,
            'stock_id' => $stockNew->id,
            'product_id' => $enlistProductWave->product_id
          ];
          EnlistPackingWaves::create($obj);
          if ($stock_search->quanty === 0) {
            $stock_search = Stock::where('zone_position_id', $position->id)->where('product_id', $producto->id)->delete();
          }
        } else {
          $packingWave = EnlistPackingWaves::where('wave_id', $enlistProductWave->wave_id)->where('product_id', $producto->id)->first();
          $stock_search_new = Stock::where('zone_position_id', $newZonePosition->id)->where('product_id', $producto->id)->where('id', $packingWave->stock_id)->first();
          $stock_search = Stock::where('zone_position_id', $position->id)->where('product_id', $producto->id)->first();
          $stock_search->decrement('quanty', 1);

          $enlistProductWave->increment('picked_quanty', 1);
          $enlistProductWaveUser->increment('picked_quanty', 1);
          $stock_search_new->increment('quanty', 1);
          $stock_search_new->increment('quanty_14', 1);

          $packingWave->increment('quanty', 1);
          $stock_searche = Stock::where('zone_position_id', $position->id)->where('product_id', $producto->id)->first();

          if ($stock_searche->quanty === 0) {
            Stock::where('zone_position_id', $position->id)->where('product_id', $producto->id)->delete();
          }
        }
        DB::commit();

        return response('Unidad mercada correctamente', 200);
      }
      return $this->response->error('El producto no se encuentra ligado a esta tarea', 404);
    } catch (\Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response($e->getMessage(), 500);
    }
  }

  /**
   * Método que contiene el proceso algoritmicoo para crear un packing masivo partiendo de una wave mercada
   * @author Santiago Muñoz
   */
  public function createPackingMassive(Request $request)
  {
    $data = $request->all();
    $params = array_key_exists('params', $data) ? $data['params'] : NULL;
    $documents = array_key_exists('documents', $data) ? $data['documents'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $wave = Waves::find($params['parentId']);
    DB::beginTransaction();
    try {
      foreach ($params['users'] as $dataUser) {
        $taskSchedules = [
          'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
          'end_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
          'name' => "Realizar packing de la ola: $wave->UUID",
          'schedule_type' => ScheduleType::Task,
          'schedule_action' => ScheduleAction::PackingMassiveAction,
          'status' => ScheduleStatus::Process,
          'user_id' => $dataUser['user']['id'],
          'parent_schedule_id' => $wave->id,
          'company_id' => $company_id
        ];
        Schedule::create($taskSchedules);
      }

      foreach ($documents as $dataDocument) {
        Document::where('id', $dataDocument['id'])->update(['status' => 'packing']);
      }
      DB::commit();

      return response('Packing creado correctamente', 200);
    } catch (\Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response($e->getMessage(), 500);
    }
  }

  /**
   * Método que contiene el proceso algoritmicoo para crear la tarea de impirmir rótulos de la ola
   * @author Santiago Muñoz
   */
  public function taskPrintWaves(Request $request)
  {
    $data = $request->all();
    $uuid = array_key_exists('uuid', $data) ? $data['uuid'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $wave = Waves::where('uuid', $uuid)->first();

    $settingsObj = new Settings($company_id);
    $chargeUserName = $settingsObj->get('picking_dispatch');
    $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
      $q->where('name', $chargeUserName);
    })->where('active', 1)->first();

    if (empty($user)) {
      return $this->response->error('No se encontró un usuario para asignar la tarea', 409);
    }

    $taskSchedulesW = [
      'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'end_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'name' => 'Imprimir rótulos para la ola ' . $uuid,
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::PrintWaves,
      'status' => ScheduleStatus::Process,
      'user_id' => $user->id,
      'parent_schedule_id' => $wave->id,
      'company_id' => $company_id
    ];
    Schedule::create($taskSchedulesW);
    return response('Tarea generada con éxito', 200);
  }

  /**
   * Método que contiene el proceso algoritmicoo para crear la tarea de impirmir rótulos de la ola
   * @author Santiago Muñoz
   */
  public function createReubicatePickingMassive($waveUserId, Request $request)
  {
    $data = $request->all();
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $wave = EnlistProductsWavesUsers::join('wms_enlist_products_waves', 'wms_enlist_products_waves_users.enlistproductwave_id', 'wms_enlist_products_waves.id')
      ->join('wms_waves', 'wms_enlist_products_waves.wave_id', 'wms_waves.id')
      ->selectRaw('wms_waves.*')
      ->where('wms_enlist_products_waves_users.id', $waveUserId)
      ->first();
    $userId = $request->input('session_user_id');

    $taskSchedulesW = [
      'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'end_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'name' => 'Reubicar unidades picking massivo de la ola ' . $wave->UUID,
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::ReubicateProductsWaves,
      'status' => ScheduleStatus::Process,
      'user_id' => $userId,
      'parent_schedule_id' => $waveUserId,
      'company_id' => $company_id
    ];
    Schedule::create($taskSchedulesW);
    return response('Tarea generada con éxito', 200);
  }

  /**
   * Método que contiene el proceso para consultar los documentos asociados a una ola
   * @author Santiago Muñoz
   */
  public function getDocumentsByWave($waveId, Request $request)
  {
    $data = $request->all();
    $username = User::where('id', $data['session_user_id'])->first();

    $document = DB::table('wms_waves')
      ->leftJoin('wms_documents', function ($join) {
        $join->where(DB::raw("FIND_IN_SET(wms_documents.id, wms_waves.documents)"), "<>", "0");
      })
      ->leftJoin('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
      ->leftJoin('wms_clients', 'wms_documents.client', '=', 'wms_clients.id')
      ->groupBy('wms_documents.id')
      ->where('wms_waves.id', $waveId)
      ->select('wms_documents.number', 'wms_documents.external_number', 'wms_documents.date', DB::raw('SUM(wms_document_details.quanty) as quanty'), 'wms_documents.total_cost', 'total_benefit', 'wms_documents.client', 'wms_documents.status', 'wms_documents.observation', 'wms_documents.id', 'wms_documents.min_date', 'wms_documents.city as name', 'wms_documents.lead_time as max_date', 'wms_clients.name as clientName', 'wms_documents.warehouse_origin', 'wms_documents.warehouse_destination', DB::raw("'$username->name' as responsible"))
      ->groupBy('wms_documents.id')
      ->get();

    return $document;
  }

  /**
   * Método que contiene el proceso para generar los eanes por documentos
   * asociados a una ola
   * @author Santiago Muñoz
   */
  public function generateCodesByWave($waveId, Request $request)
  {
    $data = $request->all();
    $companyId = $request->input('company_id');
    $documents = DB::table('wms_waves')
      ->leftJoin('wms_documents', function ($join) {
        $join->where(DB::raw("FIND_IN_SET(wms_documents.id, wms_waves.documents)"), "<>", "0");
      })
      ->select('wms_documents.*')
      ->where('wms_waves.id', $waveId)
      ->get();

    $settingsObj = new Settings($companyId);
    $containerDefault = $settingsObj->get('container_default');
    $container = Container::where('name', $containerDefault)
      ->where('active', 1)->first();

    if (empty($container)) {
      return $this->response->error('No se encontró un contenedor por defecto para imprimir', 409);
    }

    DB::beginTransaction();

    try {
      $codes14 = [];
      foreach ($documents as $document) {
        $document14 = EanCode14::where('document_id', $document->id)->first();
        if ($document14) {
          $codes14[] = EanCode14::with('document.clientdocument')->find($document14->id);
        } else {
          $data14 = EanCode14::where('status', 20)->orderBy('id', 'desc')->first();
          $code = $data14 ? $data14->code14 + 1 : '10000000000000';
          $code14 = EanCode14::create([
            'code14' => $code,
            'container_id' => $container->id,
            'document_id' => $document->id,
            'company_id' => $companyId,
            'status' => 20
          ]);
          $codes14[] = EanCode14::with('document.clientdocument')->find($code14->id);
        }
      }
      DB::commit();
      return response(["message" => $codes14], 201);
    } catch (Exception $e) {
      DB::rollBack();
      return response(["message" => $e->getMessage()], 500);
    }
  }

  /**
   * Método que contiene el proceso para consultar los documentos asociados a una ola con sus respectivos eanes 14
   * @author Santiago Muñoz
   */
  public function getDocumentsAndCode14ByWave($waveId, Request $request)
  {
    $documents = DB::table('wms_waves')
      ->leftJoin('wms_documents', function ($join) {
        $join->where(DB::raw("FIND_IN_SET(wms_documents.id, wms_waves.documents)"), "<>", "0");
      })
      ->where('wms_waves.id', $waveId)
      ->leftJoin('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
      ->select('wms_documents.number', DB::raw('SUM(wms_document_details.quanty) as quanty'), DB::raw('wms_documents.id as documentId'), 'wms_waves.uuid', 'wms_waves.id')
      ->groupBy('wms_documents.id')
      ->get();

    foreach ($documents as $key => $document) {
      $code14 = EanCode14::leftJoin('wms_waves_eancodes14 as wwe14', 'wms_ean_codes14.id', 'wwe14.eancode14_id')
        ->where('document_id', $document->documentId)
        ->whereNull('wwe14.id')
        ->first();


      if ($code14) {
        $document->code14 = $code14['code14'];
        $document->code14Reubicado = $code14['code14'];
        $document->position = '';
      } else {
        $code14Position = EanCode14::leftJoin('wms_waves_eancodes14 as wwe14', 'wms_ean_codes14.id', 'wwe14.eancode14_id')
          ->leftJoin('wms_stock as ws', 'wwe14.stock_id', 'ws.id')
          ->leftJoin('wms_zone_positions as wzp', 'ws.zone_position_id', 'wzp.id')
          ->where('document_id', $document->documentId)
          ->selectRaw('wzp.code, wms_ean_codes14.code14')
          ->first();

        $document->code14 = 'REUBICADO';
        $document->code14Reubicado = $code14Position['code14'];
        $document->position = $code14Position['code'];
      }
    }

    return $documents;
  }

  /**
   * Método que contiene el proceso para consultar los documentos asociados a una ola con sus respectivos eanes 14
   * @author Santiago Muñoz
   */
  public function getCode14ByWave($waveId, Request $request)
  {
    $code14 = DB::table('wms_waves')
      ->leftJoin('wms_documents', function ($join) {
        $join->where(DB::raw("FIND_IN_SET(wms_documents.id, wms_waves.documents)"), "<>", "0");
      })
      ->join('wms_ean_codes14', 'wms_documents.id', 'wms_ean_codes14.document_id')
      ->where('wms_waves.id', $waveId)
      ->where('wms_ean_codes14.stored', 0)
      ->select('wms_ean_codes14.code14', 'wms_ean_codes14.id')
      ->groupBy('wms_ean_codes14.id')
      ->get();

    return $code14;
  }

  /**
   * Método que contiene el proceso para generar la tarea de gestión del packing
   * @author Santiago Muñoz
   */
  public function createManagePackingMassive($waveId, Request $request)
  {
    $data = $request->all();
    $companyId = $request->input('company_id');
    $username = User::where('id', $data['session_user_id'])->first();
    $wave = Waves::find($waveId);

    $taskSchedulesW = [
      'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'end_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'name' => 'Gestionar packing de la ola ' . $wave->UUID,
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::ManagePackingMassive,
      'status' => ScheduleStatus::Process,
      'user_id' => $username->id,
      'parent_schedule_id' => $waveId,
      'company_id' => $companyId
    ];
    Schedule::create($taskSchedulesW);

    return [];
  }

  /**
   * Método que contiene el proceso algoritmicoo reubicar el ean code 14 de la wave
   * @author Santiago Muñoz
   */
  public function reubicateCode14PickingMassive(Request $request)
  {
    $data = $request->all();
    $parentId = array_key_exists('parentId', $data) ? $data['parentId'] : NULL;
    $positionDestino = array_key_exists('positionDestino', $data) ? $data['positionDestino'] : NULL;
    $ean14 = array_key_exists('ean14', $data) ? $data['ean14'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;

    DB::beginTransaction();
    try {

      $position = ZonePosition::where('code', $positionDestino)->first();
      if (!$position) {
        throw new RuntimeException('No existe la ubicación ingresada.');
      }

      $eanCode14 = EanCode14::where('code14', $ean14)->first();
      if (!$eanCode14) {
        throw new RuntimeException('No existe el ean 14 ingresado.');
      }

      $stockSearch = Stock::where('zone_position_id', $position->id)->where('code14_id', $eanCode14->id)->first();
      if (!$stockSearch) {
        $modelStock = new Stock();
        $modelStock->code14_id = $eanCode14->id;
        $modelStock->zone_position_id = $position->id;
        $modelStock->active = 1;
        $modelStock->quanty = 1;
        $modelStock->quanty_14 = 1;
        $modelStock->save();

        $waveCode14 = new WavesCodes14();
        $waveCode14->wave_id = $parentId;
        $waveCode14->stock_id = $modelStock->id;
        $waveCode14->eancode14_id = $eanCode14->id;
        $waveCode14->save();

        $eanCode14->zone_position_id = $position->id;
        $eanCode14->save();
      }

      DB::commit();

      return response('14 reubicado correctamente', 201);
    } catch (\Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response($e->getMessage(), 500);
    }
  }

  /**
   * Método que contiene el proceso para consultar la información de la referencia de una wave
   * @author Santiago Muñoz
   */
  public function getDataReferenceByWave($waveUserId, Request $request)
  {
    $reference = DB::table('wms_enlist_products_waves_users as wepvu')
      ->join('wms_enlist_products_waves as wepv', 'wepvu.enlistproductwave_id', 'wepv.id')
      ->join('wms_products as p', 'wepv.product_id', 'p.id')
      ->where('wepvu.id', $waveUserId)
      ->selectRaw('p.reference, p.ean, wepvu.picked_quanty, wepv.wave_id, null as position')
      ->first();

    $referenceRelocated = EnlistPackingWaves::where('wave_id', $reference->wave_id)->where('relocated', 1)->first();

    if ($referenceRelocated) {
      $stock = Stock::join('wms_zone_positions', 'wms_stock.zone_position_id', 'wms_zone_positions.id')
        ->where('wms_stock.id', $referenceRelocated->stock_id)
        ->select('wms_zone_positions.code')
        ->first();

      $reference->position = $stock->code;
    }

    return ['reference' => $reference];
  }

  /**
   * Método que contiene el proceso algoritmicoo reubicar la referencia de la wave
   * @author Santiago Muñoz
   */
  public function reubicatePickingMassive(Request $request)
  {
    $data = $request->all();
    $waveUserId = array_key_exists('parentId', $data) ? $data['parentId'] : NULL;
    $positionDestino = array_key_exists('positionDestino', $data) ? $data['positionDestino'] : NULL;
    $ean13 = array_key_exists('ean13', $data) ? $data['ean13'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;

    DB::beginTransaction();
    try {

      $producto = Product::where('ean', $ean13)->first();
      if (!$producto) {
        throw new RuntimeException('No existe el producto.');
      }

      $position = ZonePosition::where('code', $positionDestino)->first();
      if (!$position) {
        throw new RuntimeException('No existe la ubicación ingresada.');
      }

      $wave = DB::table('wms_enlist_products_waves_users as wepvu')
        ->join('wms_enlist_products_waves as wepv', 'wepvu.enlistproductwave_id', 'wepv.id')
        ->where('wepvu.id', $waveUserId)
        ->where('wepv.product_id', $producto->id)
        ->selectRaw('wepv.wave_id, wepv.product_id, wepvu.picked_quanty')
        ->first();

      if (!$wave) {
        throw new RuntimeException('La referencia ingresada no pertenece a esta tarea.');
      }

      $packing = EnlistPackingWaves::where('wave_id', $wave->wave_id)->where('product_id', $producto->id)->first();

      $stockAtual = Stock::where('id', $packing->stock_id)->first();
      $stockAtual->decrement('quanty', $wave->picked_quanty);

      $stockNuevoId = 0;
      $stockNuevo = Stock::where('zone_position_id', $position->id)->where('product_id', $producto->id)->first();
      if ($stockNuevo) {
        $stockNuevo->zone_position_id = $position->id;
        $stockNuevo->quanty = $stockNuevo->quanty + $wave->picked_quanty;
        $stockNuevo->quanty_14 = $stockNuevo->quanty_14 + $wave->picked_quanty;
        $stockNuevo->save();
        $stockNuevoId = $stockNuevo->id;
      } else {
        $modelStock = new Stock();
        $modelStock->product_id = $producto->id;
        $modelStock->zone_position_id = $position->id;
        $modelStock->active = 1;
        $modelStock->quanty = $wave->picked_quanty;
        $modelStock->quanty_14 = $wave->picked_quanty;
        $modelStock->save();
        $stockNuevoId = $modelStock->id;
      }

      EnlistPackingWaves::where('id', $packing->id)->update(['stock_id' => $stockNuevoId, 'relocated' => 1]);

      if ($stockAtual->quanty === 0) {
        Stock::where('id', $stockAtual->id)->delete();
      }

      DB::commit();

      return response('Unidad reubicada correctamente', 201);
    } catch (\Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response($e->getMessage(), 500);
    }
  }

  /**
   * Método que contiene el proceso para consultar la sugerencia tanto de eanes como de unidades a empacar
   * en el packing masivo
   * @author Santiago Muñoz
   */
  public function getDataPackingMassive($waveId, $ean13, Request $request)
  {
    $sugerencia = DB::table('wms_waves as ww')
      ->join('wms_waves_eancodes14 as wwe', 'ww.id', 'wwe.wave_id')
      ->join('wms_ean_codes14 as wec', 'wwe.eancode14_id', 'wec.id')
      ->join('wms_documents as wd', 'wec.document_id', 'wd.id')
      ->join('wms_document_details as wdd', 'wd.id', 'wdd.document_id')
      ->join('wms_products as p', 'wdd.product_id', 'p.id')
      ->join('wms_stock as ws', 'wwe.stock_id', 'ws.id')
      ->join('wms_zone_positions as wzp', 'ws.zone_position_id', 'wzp.id')
      ->where('ww.id', $waveId)
      ->where('p.ean', $ean13)
      ->where('wec.stored', 0)
      ->selectRaw('wzp.code as position, wec.code14 , wd.number as pedido, ww.UUID, IF(wec.stored = 1, "Cerrado", "Abierto") as estadoContenedor, wd.id as documentId, wec.id as code14Id')
      ->get();

    foreach ($sugerencia as $pedido) {
      $cantidadPedida = DB::table('wms_waves')
        ->join('wms_documents', function ($join) {
          $join->where(DB::raw("FIND_IN_SET(wms_documents.id, wms_waves.documents)"), "<>", "0");
        })
        ->join('wms_document_details', 'wms_documents.id', 'wms_document_details.document_id')
        ->where('wms_waves.id', $waveId)
        ->where('wms_documents.id', $pedido->documentId)
        ->selectRaw('SUM(wms_document_details.quanty) as pedido')
        ->first();

      $cantidadEmpacada = DB::table('wms_ean_codes14 as wec')
        ->join('wms_ean_codes14_detail as wecd', 'wec.id', 'wecd.ean_code14_id')
        // ->where('wec.code14', $pedido->code14)
        ->where('wec.document_id', $pedido->documentId)
        ->selectRaw('IFNULL(SUM(wecd.quanty), 0) as empacado')
        ->first();

      $pedido->cantidadPedida = $cantidadPedida->pedido;
      $pedido->cantidadEmpacada = $cantidadEmpacada->empacado;
      $pedido->cantidadRestante = $cantidadPedida->pedido - $cantidadEmpacada->empacado;
    }

    $referencias = DB::table('wms_packing_waves')
      ->join('wms_enlist_products_waves', function ($join) {
        $join->on('wms_packing_waves.wave_id', 'wms_enlist_products_waves.wave_id')
          ->on('wms_packing_waves.product_id', 'wms_enlist_products_waves.product_id');
      })
      ->join('wms_products', 'wms_packing_waves.product_id', 'wms_products.id')
      ->where('wms_packing_waves.wave_id', $waveId)
      ->where('wms_packing_waves.relocated', 1)
      ->whereRaw('wms_packing_waves.quanty - wms_packing_waves.packaged_quanty > 0')
      ->select('wms_products.reference', 'wms_products.ean', 'wms_products.description', 'wms_enlist_products_waves.order_quanty as pedido', 'wms_enlist_products_waves.picked_quanty as mercado', 'wms_packing_waves.packaged_quanty as empacado', DB::raw('wms_packing_waves.quanty - wms_packing_waves.packaged_quanty as restante'))
      ->get();

    $isFinally = EnlistPackingWaves::where('wave_id', $waveId)->whereRaw('quanty <> packaged_quanty')->first();
    if ($isFinally) {
      $finaliza = false;
    } else {
      $finaliza = true;
    }

    return ['sugerencia' => $sugerencia, 'referencias' => $referencias, 'finaliza' => $finaliza];
  }

  /**
   * Método que contiene el proceso para guardar las unicades que se van emapcando en el packing masivo
   * indicando la posición donde se van a ubicar para el respectivo despacho
   * @author Santiago Muñoz
   */
  public function savePackingMassive(Request $request)
  {
    $data = $request->all();
    $parentId = array_key_exists('parentId', $data) ? $data['parentId'] : NULL;
    $position = array_key_exists('position', $data) ? $data['position'] : NULL;
    $ean14 = array_key_exists('ean14', $data) ? $data['ean14'] : NULL;
    $ean13 = array_key_exists('ean13', $data) ? $data['ean13'] : NULL;
    $finaliza = false;

    DB::beginTransaction();
    try {
      $position = ZonePosition::where('code', $position)->first();
      if (!$position) {
        throw new RuntimeException('No existe la ubicación ingresada.');
      }

      $dataEan14 = DB::table('wms_waves_eancodes14 as wwe')
        ->join('wms_ean_codes14 as wec', 'wwe.eancode14_id', 'wec.id')
        ->join('wms_stock as ws', 'wwe.stock_id', 'ws.id')
        ->where('wec.code14', $ean14)
        ->where('wwe.wave_id', $parentId)
        ->where('ws.zone_position_id', $position->id)
        ->selectRaw('wec.*')
        ->first();

      if (!$dataEan14) {
        throw new RuntimeException('El EAN 14 o la posición no existe o no están asociados a esta ola');
      }

      if ($dataEan14->stored) {
        throw new RuntimeException('El EAN 14 ya se encuentra cerrado');
      }

      $dataProduct = Product::where('ean', $ean13)
        ->join('wms_packing_waves', 'wms_products.id', '=', 'wms_packing_waves.product_id')
        ->where('wms_packing_waves.wave_id', $parentId)
        ->selectRaw('wms_products.*')
        ->first();

      if (!$dataProduct) {
        throw new RuntimeException('No se encontró un producto asociado al ean ingresado');
      }

      $documentProduct = DocumentDetail::where('document_id', $dataEan14->document_id)->where('product_id', $dataProduct->id)->first();
      if (!$documentProduct) {
        throw new RuntimeException('La referencia ingresada no corresponde al pedido');
      }

      $packing = EnlistPackingWaves::where('wave_id', $parentId)->where('product_id', $dataProduct->id)->first();

      EnlistPackingWaves::where('id', $packing->id)->update([
        'packaged_quanty' => ($packing->packaged_quanty + 1)
      ]);

      $packingWave = EnlistPackingWaves::where('wave_id', $parentId)->where('product_id', $dataProduct->id)->first();
      if ($packingWave->quanty < $packingWave->packaged_quanty) {
        throw new RuntimeException('Está empacando más unidades de las mercadas en esta referencia');
      }

      $dataEan14Detail = EanCode14Detail::where('ean_code14_id', $dataEan14->id)->where('product_id', $dataProduct->id)->first();
      if ($dataEan14Detail) {
        $dataEan14Detail->quanty = $dataEan14Detail->quanty + 1;
        $dataEan14Detail->good = $dataEan14Detail->good + 1;
        $dataEan14Detail->save();
      } else {
        EanCode14Detail::create([
          "ean_code14_id" => $dataEan14->id,
          "product_id" => $dataProduct->id,
          "quanty" => 1,
          "good" => 1,
        ]);
      }

      $dataDocumentPedido = DB::table('wms_waves')
        ->join('wms_documents', function ($join) {
          $join->where(DB::raw("FIND_IN_SET(wms_documents.id, wms_waves.documents)"), "<>", "0");
        })
        ->join('wms_document_details', 'wms_documents.id', 'wms_document_details.document_id')
        ->where('wms_waves.id', $parentId)
        ->where('wms_documents.id', $dataEan14->document_id)
        ->where('wms_document_details.product_id', $dataProduct->id)
        ->selectRaw('SUM(wms_document_details.quanty) as pedido')
        ->first();

      $dataDocumentEmpacado = DB::table('wms_ean_codes14 as wec')
        ->join('wms_ean_codes14_detail as wecd', 'wec.id', 'wecd.ean_code14_id')
        ->where('wec.id', $dataEan14->id)
        ->where('wec.document_id', $dataEan14->document_id)
        ->where('wecd.product_id', $dataProduct->id)
        ->selectRaw('IFNULL(SUM(wecd.quanty), 0) as empacado')
        ->first();

      if ($dataDocumentPedido->pedido < $dataDocumentEmpacado->empacado) {
        throw new RuntimeException('Está empacando más unidades de las pedidas en esta referencia para el pedido asociado');
      }

      $stockAtual = Stock::where('id', $packing->stock_id)->first();
      $stockAtual->decrement('quanty', 1);
      $stockAtual->save();
      if ($stockAtual->quanty == 0) {
        EnlistPackingWaves::where('id', $packing->id)->update(['stock_id' => null]);
        $stockAtual->delete();
      }

      $documentDetail = DocumentDetail::where('document_id', $dataEan14->document_id)->where('product_id', $dataProduct->id)->first();

      $dataTransition = StockTransition::where('product_id', $dataProduct->id)->where('document_detail_id', $documentDetail->id)->where('code_ean14', $dataEan14->id)->first();
      if ($dataTransition) {
        $dataTransition->increment('quanty', 1);
        $dataTransition->increment('quanty_14', 1);
        $dataTransition->save();
      } else {
        StockTransition::create([
          "product_id" => $dataProduct->id,
          "quanty" => 1,
          "action" => "output",
          "document_detail_id" => $documentDetail->id,
          "code_ean14" => $dataEan14->id,
          "quanty_14" => 1
        ]);
      }

      $isFinally = EnlistPackingWaves::where('wave_id', $parentId)->whereRaw('quanty <> packaged_quanty')->first();
      if ($isFinally) {
        $finaliza = false;
      } else {
        $finaliza = true;
      }

      DB::commit();
      return response(['finaliza' => $finaliza], 201);
    } catch (Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response(["message" => $e->getMessage()], 500);
    }
  }

  /**
   * Método que contiene el proceso cerrar una caja del packing validando que
   * tenga unidades empacadas y que pertenezca a la ola actual
   * adicional crea la tarea de ubicar pedido
   * @author Santiago Muñoz
   */
  public function closeEan14PackingMassive(Request $request)
  {
    $data = $request->all();
    $waveId = array_key_exists('parentId', $data) ? $data['parentId'] : NULL;
    $ean14 = array_key_exists('ean14', $data) ? $data['ean14'] : NULL;
    $peso = array_key_exists('peso', $data) ? $data['peso'] : NULL;
    $container = array_key_exists('container', $data) ? $data['container'] : NULL;
    $companyId = $request->input('company_id');

    DB::beginTransaction();
    try {

      $data14 = WavesCodes14::join('wms_ean_codes14', 'wms_waves_eancodes14.eancode14_id', 'wms_ean_codes14.id')
        ->where('wave_id', $waveId)
        ->where('code14', $ean14)
        ->select('wms_ean_codes14.id', 'wms_ean_codes14.document_id')
        ->first();

      if (!$data14) {
        throw new RuntimeException("El EAN 14 no está asociado a esta ola");
      }

      $detail = EanCode14Detail::where('ean_code14_id', $data14->id)->first();

      if (!$detail) {
        throw new RuntimeException("El EAN 14 no puede cerrarse porque no contiene unidades");
      }

      EanCode14::where('id', $data14->id)->where('code14', $ean14)->where('status', 20)->update(['stored' => 1, 'weight' => $peso, 'container_id' => $container]);

      $document = Document::where('id', $data14->document_id)->first();

      if ($document->status == 'pending_cancel' || $document->status == 'cancel') {
        throw new RuntimeException("No se puede crear la tarea de ubicar porque el pedido está en proceso de cancelación");
      }

      $validateTask = Schedule::where('parent_schedule_id', $document->id)->where('schedule_action', 'ReubicarPackingAction')->first();
      if (!$validateTask) {
        Document::where('id', $data14->document_id)->update(['status' => 'transsition']);
        $client = Client::where('id', $document->client)->first();

        $taskSchedules = [
          'name' => "Ubicar pedido de: $client->name para el pedido $document->number",
          'schedule_type' => ScheduleType::Task,
          'schedule_action' => ScheduleAction::ReubicarPackingAction,
          'status' => ScheduleStatus::Process,
          'user_id' => $data['session_user_id'],
          'parent_schedule_id' => $document->id,
          'company_id' => $companyId
        ];
        Schedule::create($taskSchedules);
      }

      // TODO enviar información a saya luego de packing massivo
      // $companyId = $request->input('company_id');
      // $detalles = [];
      // foreach ($document['enlistplan'] as $value) {
      //   if ($value['picked_quanty'] > 0) {
      //     $detalles[] = [
      //       "quantity" => $value['picked_quanty'],
      //       "code" => $value['product']['reference']
      //     ];
      //   }
      // }
      // $obje = [
      //   "number" => $document['number'],
      //   "id_number" => $document['id'],
      //   "detail" => $detalles
      // ];

      // $objeto = [
      //   'user' => env("API_SAYA_USERNAME", "ptolomeo"),
      //   'pass' => env("API_SAYA_PASSWORD", "PTML*_2250")
      // ];

      // $res = SoberanaServices::getToken($objeto, $companyId);
      // $porciones = explode(":", $res);
      // $porciones1 = explode('"', $porciones[1]);
      // $token = explode('"', $porciones1[1]);

      // $respuesta = SoberanaServices::saveOrder($obje, $token[0], $companyId);

      // $vuelta = explode(":", $respuesta);
      // $vuelta1 = explode('"', $vuelta[0]);

      // if ($vuelta1[1] != 'error') {
      //   Document::where('id', $id)->update(['status' => 'Por facturar SAYA', 'send_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1]]);
      // }

      DB::commit();
      return response(["message" => "EAN cerrado correctamente"], 200);
    } catch (Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response(["message" => $e->getMessage()], 500);
    }
  }

  /**
   * Método que contiene el proceso para crear una caja adicional en la ola
   * @author Santiago Muñoz
   */
  public function generateEan14AditionalPackingMassive(Request $request)
  {
    $data = $request->all();
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $waveId = array_key_exists('parentId', $data) ? $data['parentId'] : NULL;
    $document = array_key_exists('document', $data) ? $data['document'] : NULL;
    $container = array_key_exists('container', $data) ? $data['container'] : NULL;

    DB::beginTransaction();
    try {

      $document14 = EanCode14::where('document_id', $document)->first();
      $wave14 = WavesCodes14::where('eancode14_id', $document14->id)->where('wave_id', $waveId)->first();

      $data14 = EanCode14::where('status', 20)->orderBy('id', 'desc')->first();
      $code = $data14 ? $data14->code14 + 1 : '10000000000000';
      $code14 = EanCode14::create([
        'code14' => $code,
        'container_id' => $container,
        'document_id' => $document,
        'company_id' => $company_id,
        'status' => 20
      ]);

      WavesCodes14::create([
        'wave_id' => $waveId,
        'eancode14_id' => $code14->id,
        'stock_id' => $wave14->stock_id
      ]);

      $cantRotulos = EanCode14::where('document_id', $document)->selectRaw('COUNT(id) as cantidad')->first();

      $codes14 = EanCode14::with('document.clientdocument')->find($code14->id);
      DB::commit();
      return response(["message" => $codes14, "cantRotulos" => $cantRotulos->cantidad], 200);
    } catch (Exception $e) {
      DB::rollBack();
      return response(["message" => $e->getMessage()], 500);
    }
  }

  /**
   * Método que contiene el proceso para validar que todas las cajas del packing
   * se encuentren cerradas y con peso
   * @author Santiago Muñoz
   */
  public function validateCloseTaskPackingMassive($waveId, Request $request)
  {
    try {
      $waveCode14 = DB::table('wms_waves_eancodes14')
        ->join('wms_ean_codes14', 'wms_waves_eancodes14.eancode14_id', 'wms_ean_codes14.id')
        ->where('wave_id', $waveId)
        ->where('stored', 0)
        ->select('wms_ean_codes14.*')
        ->first();
      if ($waveCode14) {
        throw new RuntimeException("El contenedor $waveCode14->code14 no se encuentra cerrado");
      }
      return response([], 200);
    } catch (Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response(["message" => $e->getMessage()], 500);
    }
  }

  public function getDocumentsReceiveReport(Request $request)
  {
    $tulas = Document::from('wms_documents as d')
      ->leftjoin("wms_schedule_documents as sd", "sd.document_id", "d.id")
      ->join("wms_clients as c", "c.id", "d.client")
      ->leftjoin("wms_ean_codes14 as c14", "c14.document_id", "d.id")
      // ->leftjoin("wms_document_details as dd", function ($query) {
      //     $query->on("dd.document_id", "=", "cd.document_id");
      // })
      ->where('document_type', "receipt")
      ->selectRaw(
        'd.id,
            c.name,
            d.number,
            d.facturation_number,
            d.departure_date as fechaCierre,
            COUNT(DISTINCT c14.id) as totalTulas,
            d.status
        '
      )
      ->groupBy('d.id')
      ->get();
    return $tulas->toArray();
  }


  /**
   * Este metodo retorna la data para los recibos de las tulas  en zona franca.
   * @param string $documentType
   * @author Julian Osorio
   */
  public function getDetailsDocumentsReceipt(Request $request)
  {
    $data = $request->all();
    $documentId = array_key_exists('document_id', $data) ? $data['document_id'] : NULL;
    $ean14 = EanCode14::from("wms_ean_codes14 as cd")
      ->leftjoin("wms_ean_codes14_detail as wdd", "wdd.ean_code14_id", "=", "cd.id")
      ->leftjoin("wms_products as p", "wdd.product_id", "=", "p.id")
      ->leftjoin("wms_document_details as dd", function ($query) {
        $query->on("dd.product_id", "=", "p.id")
          ->on("dd.document_id", "=", "cd.document_id");
      })
      ->where('cd.document_id', $documentId)
      // ->where('cd.status', 13)
      ->groupBy("p.id")
      ->selectRaw(
        "cd.*,
                p.reference,
                p.description,
                SUM(dd.quanty_received) as quanty_received,
                wdd.id as ean_code14_detail_id,
                dd.id as document_detail_id,
                SUM(wdd.good_receive) as good_receive,
                wdd.sin_conf_receive,
                wdd.seconds_receive,
                wdd.good_pallet,
                wdd.seconds_pallet,
                wdd.sin_conf_pallet,
                dd.product_id,
                p.ean as code13,
                SUM(dd.quanty) as quanty",
      )
      ->get();
    $peso = 0;
    $recibido = 0;
    $total = 0;
    $diferencia = 0;
    foreach ($ean14 as $detail) {
      $recibido += $detail->quanty_received ?? 0;
      $total += $detail->quanty ?? 0;
    }
    return [
      "documentsDetails" => $ean14,
      "totalDetail" => $total,
      "totalRecibido" => $recibido,
    ];
  }


  public function getDocumentComexFilter(Request $request)
  {
    $data = $request->all();
    $companyId = $request->input('company_id');
    $departure = DocType::Departure;

    $numeroFactura = array_key_exists('numeroFactura', $data) ? $data['numeroFactura'] : NULL;
    $guiaMaster = array_key_exists('guiaMaster', $data) ? $data['guiaMaster'] : NULL;
    $formularioDespacho = array_key_exists('formularioDespacho', $data) ? $data['formularioDespacho'] : NULL;
    $guiaHija = array_key_exists('guiaHija', $data) ? $data['guiaHija'] : NULL;


    $document = Document::from('wms_documents')
      ->leftJoin('wms_document_details', 'wms_documents.id', 'wms_document_details.document_id')
      ->leftJoin(DB::raw("(
                SELECT
                    SUM(totalDispatch) totalDispatch,
                    SUM( totalCajas ) AS totalCajas,
                    SUM( weight ) AS totalPeso,
                    document_id,
                    container_id
                FROM
                    (
                    SELECT
                        SUM( e14d.quanty ) AS totalDispatch,
                        COUNT( DISTINCT e14.id ) AS totalCajas,
                        IFNULL( e14.weight, 0 ) AS weight,
                        document_id,
                        container_id
                    FROM
                        wms_documents
                        INNER JOIN wms_ean_codes14 AS e14 ON wms_documents.id = e14.document_id
                        INNER JOIN wms_ean_codes14_detail AS e14d ON e14.id = e14d.ean_code14_id
                        INNER JOIN wms_products ON e14d.product_id = wms_products.id
                    GROUP BY
                        e14.id
                    ) AS e14d
                GROUP BY
                    e14d.document_id
            ) as e14"), function ($join) {
        $join->on('wms_documents.id', '=', 'e14.document_id');
      })
      ->leftjoin(DB::raw("(
                SELECT
                    id,
                    status,
                    parent_schedule_id,
                    created_at,
                    updated_at
                FROM
                    wms_schedules
                WHERE
                    ( NAME LIKE '%Gestionar Despacho%' OR NAME LIKE '%Ubicar pedido%' )
                    AND status = 'closed'
                GROUP BY
                    parent_schedule_id
                ) as task"), function ($join) {
        $join->on('wms_documents.id', '=', 'task.parent_schedule_id');
      })
      ->leftJoin('wms_clients', 'wms_documents.client', '=', 'wms_clients.id')
      ->leftJoin('cities', 'wms_clients.city_id', '=', 'cities.id')
      ->leftJoin('wms_containers', 'e14.container_id', '=', 'wms_containers.id')
      ->leftJoin('wms_schedule_documents', 'wms_schedule_documents.document_id', '=', 'wms_documents.id')
      ->leftJoin('wms_schedules', 'wms_schedules.id', '=', 'wms_schedule_documents.schedule_id')
      ->where('wms_documents.document_type', $departure)
      ->where('wms_documents.company_id', $companyId)
      ->where('wms_documents.status', 'dispatch')
      ->orWhere('wms_documents.status', 'En documentación')
      ->orWhere('wms_documents.status', 'cancel')
      ->selectRaw("
          wms_documents.id,
          task.updated_at,
          wms_documents.date,
          wms_documents.number,
          wms_documents.external_number,
          wms_documents.guia_transp,
          wms_documents.facturation_number,
          wms_documents.city as name,
          wms_documents.consecutive_dispatch,
          wms_documents.fmm_authorization,
          wms_clients.name as clientName,
          SUM( wms_document_details.quanty) as totalUnit,
          IFNULL(totalDispatch, 0) AS totalDispatch,
          IFNULL(totalCajas, 0) AS totalCajas,
          IFNULL(totalPeso, 0) AS totalPeso,
          group_concat(DISTINCT wms_containers.name SEPARATOR '  -  ' ) as nameContainer,
          CASE
          WHEN wms_documents.status = 'cancel' THEN 'Cancelado'
          WHEN wms_documents.status = 'pending_dispatch' THEN 'Por despachar'
          WHEN wms_documents.status = 'En documentación' THEN 'En documentación'
          WHEN wms_documents.status = 'dispatch' THEN 'Despachado'
          ELSE ''
          END as status
        ")
      ->groupBy('wms_documents.id')
      ->orderBy("wms_documents.date", 'asc');

    if ($numeroFactura) {
      $document = $document->where('wms_documents.facturation_number', '=', $numeroFactura);
    }

    if ($guiaMaster) {
      $document = $document->where('wms_documents.master_guide', '=', $guiaMaster);
    }

    if ($formularioDespacho) {
      $document = $document->where('wms_documents.fmm_authorization', '=', $formularioDespacho);
    }

    if ($guiaHija) {
      $document = $document->where('wms_documents.guia_transp', '=', $guiaHija);
    }
    return $document->get();
  }

  public function getDocumentComexByFilter(Request $request)
  {
    $data = $request->all();
    $companyId = $request->input('company_id');
    $departure = DocType::Departure;
    $numeroFactura = array_key_exists('numeroFactura', $data) ? $data['numeroFactura'] : NULL;
    $guiaMaster = array_key_exists('guiaMaster', $data) ? $data['guiaMaster'] : NULL;
    $formularioDespacho = array_key_exists('formularioDespacho', $data) ? $data['formularioDespacho'] : NULL;
    $guiaHija = array_key_exists('guiaHija', $data) ? $data['guiaHija'] : NULL;
    $number = array_key_exists('number', $data) ? $data['number'] : NULL;

    $bandera = false;
    if ($numeroFactura) {
      $bandera = true;
    }
    if ($guiaMaster) {
      $bandera = true;
    }
    if ($formularioDespacho) {
      $bandera = true;
    }
    if ($guiaHija) {
      $bandera = true;
    }
    if ($number) {
      $bandera = true;
    }

    if (!$bandera) {
      throw new RuntimeException('Debe agregar un filtro para continuar.');
    }

    $document = Document::from('wms_documents')
      ->leftjoin('wms_document_details', 'wms_documents.id', 'wms_document_details.document_id')
      ->leftjoin('wms_ean_codes14 as e14', 'wms_documents.id', '=', 'e14.document_id')
      ->leftjoin('wms_clients', 'wms_documents.client', '=', 'wms_clients.id')
      ->leftjoin('cities', 'wms_clients.city_id', '=', 'cities.id')
      ->leftjoin('wms_containers', 'e14.container_id', '=', 'wms_containers.id')
      ->when($numeroFactura, function ($query) use ($numeroFactura) {
        $query->where('wms_documents.facturation_number', '=', $numeroFactura);
      })
      ->when($guiaMaster, function ($query) use ($guiaMaster) {
        $query->where('wms_documents.master_guide', '=', $guiaMaster);
      })
      ->when($formularioDespacho, function ($query) use ($formularioDespacho) {
        $query->where('wms_documents.fmm_authorization', '=', $formularioDespacho);
      })
      ->when($guiaHija, function ($query) use ($guiaHija) {
        $query->where('wms_documents.guia_transp', '=', $guiaHija);
      })
      ->when($number, function ($query) use ($number) {
        $query->where('wms_documents.number', '=', $number);
      })
      ->where('wms_documents.document_type', $departure)
      ->where('wms_documents.company_id', $companyId)
      ->whereIn('wms_documents.status', ['dispatch', 'En documentación', 'cancel', 'pending_dispatch'])
      ->selectRaw("
          wms_documents.id,
          wms_documents.date,
          wms_documents.number,
          wms_documents.external_number,
          wms_documents.guia_transp,
          wms_documents.facturation_number,
          wms_documents.city as name,
          wms_documents.consecutive_dispatch,
          wms_documents.fmm_authorization,
          wms_clients.name as clientName,
          group_concat(DISTINCT wms_containers.name SEPARATOR '  -  ' ) as nameContainer,
          wms_documents.status,
          SUM(wms_document_details.quanty) as totalUnit,
          wms_documents.transportation_company
        ")
      ->groupBy('wms_documents.id')
      ->orderBy("wms_documents.date", 'asc')
      ->limit(1000)
      ->get();

    foreach ($document as $value) {
      $documentDetail = Document::from('wms_documents')
        ->leftjoin('wms_ean_codes14 as e14', 'wms_documents.id', 'e14.document_id')
        ->leftjoin('wms_ean_codes14_detail as e14d', 'e14.id', 'e14d.ean_code14_id')
        ->where('wms_documents.id', $value->id)
        ->selectRaw('
          SUM(e14d.quanty) as totalDispatch,
          SUM(IFNULL(e14.weight,0)) as totalPeso,
          COUNT( DISTINCT e14.id ) AS totalCajas,
          e14.master
        ')
        ->first();
      $code14Peso = Document::from('wms_documents')
        ->leftjoin('wms_ean_codes14 as e14', 'wms_documents.id', 'e14.document_id')
        ->where('wms_documents.id', $value->id)
        ->selectRaw('
          SUM(IFNULL(e14.weight,0)) as totalPeso
        ')
        ->first();
      $value->totalDispatch = $documentDetail->totalDispatch;
      $value->totalPeso = $code14Peso->totalPeso;
      $value->master = $documentDetail->master;
      $value->totalCajas = $documentDetail->totalCajas;
    }
    return $document->toArray();
  }

  public function getCodesByMaster(Request $request)
  {
    $data = $request->all();
    $cajaMaster = array_key_exists('cajaMaster', $data) ? $data['cajaMaster'] : NULL;
    $codes = MasterBox::from('wms_master_box as cm')
      ->join('wms_ean_codes14 as c14', 'cm.code14_id', '=', 'c14.id')
      ->join('wms_documents as dc', 'c14.document_id', 'dc.id')
      ->leftjoin('wms_clients as cli', 'dc.client', '=', 'cli.id')
      ->where('cm.master', $cajaMaster)
      ->select('c14.id', 'c14.code14', 'cm.master', 'dc.number', 'cli.name as clienteName')
      ->get();
    return $codes;
  }

  public function deleteCode14ByMaster(Request $request)
  {
    $data = $request->all();
    $code14Id = array_key_exists('code14Id', $data) ? $data['code14Id'] : NULL;
    $master = array_key_exists('master', $data) ? $data['master'] : NULL;
    $codes = MasterBox::where('code14_id', $code14Id)->where('master', $master)->first();
    if (!$codes) {
      throw new RuntimeException('No se encontro el codigo en la caja master.');
    }
    $codes->delete();

    $code14Ean = EanCode14::where('id', $code14Id)->first();
    $code14Ean->master = null;
    $code14Ean->save();

    $cajaMasterModel = MasterBox::where('master', $master)->first();
    return response(["cajaMaster" => $cajaMasterModel], 200);
  }

  public function updateMasterBox(Request $request)
  {
    $data = $request->all();
    $code14 = array_key_exists('code14', $data) ? $data['code14'] : NULL;
    $cajaMaster = array_key_exists('cajaMaster', $data) ? $data['cajaMaster'] : NULL;
    $cliente = EanCode14::where('master', $cajaMaster)
      ->join('wms_documents as dc', 'wms_ean_codes14.document_id', 'dc.id')
      ->select('client')
      ->first();
    if (!$cliente) {
      throw new RuntimeException('La caja master no tiene un documento asociado.');
    }

    //CODIGO 14
    $code14Ean = EanCode14::where('code14', $code14)->first();
    if (!$code14Ean) {
      throw new RuntimeException('No se ha encontrado el código escaneado.');
    }
    //VALIDACION CLIENTE
    $code14EanClient = EanCode14::join('wms_documents as dc', 'wms_ean_codes14.document_id', 'dc.id')
      ->where('code14', $code14)
      ->where('dc.client', $cliente['client'])
      ->first();
    if (!$code14EanClient) {
      throw new RuntimeException('El código no pertenece al cliente de la caja master.');
    }

    $masterBox = MasterBox::where('code14_id', $code14Ean->id)->first();
    if ($masterBox) {
      throw new RuntimeException('El código ya existe en una caja master.');
    }
    EanCode14::where('id', $code14Ean->id)->update(['master' => $cajaMaster]);

    $box = [
      'code14_id' => $code14Ean->id,
      'master' => $cajaMaster,
    ];
    MasterBox::create($box);
    return response('Código actualizado correctamente', 200);
  }


  public function updatePesoMasterBox(Request $request)
  {
    $data = $request->all();
    $peso = array_key_exists('peso', $data) ? $data['peso'] : NULL;
    $master = array_key_exists('cajaMaster', $data) ? $data['cajaMaster'] : NULL;
    $cajaMaster = MasterBox::where('master', $master)->get();
    if ($cajaMaster->count() == 0) {
      throw new RuntimeException('No se encontro el codigo en la caja master.');
    }
    foreach ($cajaMaster as  $value) {
      $masterModel = MasterBox::find($value->id);
      $masterModel->peso = $peso;
      $masterModel->save();
    }
    return response('Actualizado con éxito', 200);
  }

  public function getMasterBoxByFilter(Request $request)
  {
    $data = $request->all();
    $companyId = $request->input('company_id');
    $cajaMaster = array_key_exists('cajaMaster', $data) ? $data['cajaMaster'] : NULL;
    $numeroPedido = array_key_exists('numeroPedido', $data) ? $data['numeroPedido'] : NULL;
    $numeroExterno = array_key_exists('numeroExterno', $data) ? $data['numeroExterno'] : NULL;
    $numeroFactura = array_key_exists('numeroFactura', $data) ? $data['numeroFactura'] : NULL;
    $code14 = array_key_exists('code14', $data) ? $data['code14'] : NULL;

    $bandera = false;
    if ($cajaMaster) {
      $bandera = true;
    }
    if ($numeroPedido) {
      $bandera = true;
    }
    if ($numeroExterno) {
      $bandera = true;
    }
    if ($numeroFactura) {
      $bandera = true;
    }
    if ($code14) {
      $bandera = true;
    }

    if (!$bandera) {
      throw new RuntimeException('Debe agregar un filtro para continuar.');
    }

    $document = MasterBox::from('wms_master_box as mb')
      ->leftjoin('wms_ean_codes14 as e14', 'e14.id', '=', 'mb.code14_id')
      ->leftjoin('wms_documents', 'wms_documents.id', 'e14.document_id')
      ->leftjoin('wms_clients', 'wms_documents.client', '=', 'wms_clients.id')
      ->leftjoin('cities', 'wms_clients.city_id', '=', 'cities.id')
      ->when($cajaMaster, function ($query) use ($cajaMaster) {
        $query->where('mb.master', '=', $cajaMaster);
      })
      ->when($numeroPedido, function ($query) use ($numeroPedido) {
        $query->where('wms_documents.number', '=', $numeroPedido);
      })
      ->when($numeroExterno, function ($query) use ($numeroExterno) {
        $query->where('wms_documents.external_number', '=', $numeroExterno);
      })
      ->when($numeroFactura, function ($query) use ($numeroFactura) {
        $query->where('wms_documents.facturation_number', '=', $numeroFactura);
      })
      ->when($code14, function ($query) use ($code14) {
        $query->where('e14.code14', '=', $code14);
      })
      ->where('wms_documents.company_id', $companyId)
      // ->whereIn('wms_documents.status', ['dispatch', 'En documentación', 'cancel'])
      ->selectRaw("
          e14.id,
          wms_documents.date,
          wms_documents.number,
          wms_documents.external_number,
          wms_documents.guia_transp,
          wms_documents.facturation_number,
          wms_documents.city as name,
          wms_documents.consecutive_dispatch,
          wms_documents.fmm_authorization,
          wms_clients.name as clientName,
          wms_documents.status,
          mb.master,
          e14.code14,
          mb.peso
        ")
      ->groupBy('e14.id')
      ->limit(6000)
      ->get();

    // foreach ($document as $value) {
    //   $documentDetail = Document::from('wms_documents')
    //   ->leftjoin('wms_ean_codes14 as e14','wms_documents.id', 'e14.document_id')
    //   ->leftjoin('wms_ean_codes14_detail as e14d','e14.id', 'e14d.ean_code14_id')
    //   ->where('wms_documents.id',$value->id)
    //   ->selectRaw('
    //     SUM(e14d.quanty) as totalDispatch,
    //     SUM(IFNULL(e14.weight,0)) as totalPeso
    //   ')
    //   ->first();
    //   $value->totalDispatch = $documentDetail->totalDispatch;
    //   $value->totalPeso = $documentDetail->totalPeso;
    // }
    return $document->toArray();
  }

  public function getDetailCode14(Request $request)
  {
    $data = $request->all();
    $id = array_key_exists('id', $data) ? $data['id'] : NULL;
    $master = array_key_exists('master', $data) ? $data['master'] : NULL;

    $document = MasterBox::from('wms_master_box as mb')
      ->leftjoin('wms_ean_codes14 as e14', 'e14.id', '=', 'mb.code14_id')
      ->leftjoin("wms_ean_codes14_detail as wdd", "wdd.ean_code14_id", "=", "e14.id")
      ->leftjoin("wms_products as p", "wdd.product_id", "=", "p.id")
      ->leftjoin("wms_documents as d", "e14.document_id", "=", "d.id")
      ->leftjoin("wms_clients as c", "d.client", "=", "c.id")
      // ->where('e14.id', $id)
      ->where('mb.master', $master)
      ->selectRaw("
          e14.id,
          mb.master,
          e14.code14,
          wdd.quanty,
          p.reference,
          p.description,
          mb.peso,
          d.facturation_number,
          d.number,
          c.name
        ")
      ->limit(5000)
      ->get();
    return $document->toArray();
  }

  public function getPesoByMaster(Request $request)
  {
    $data = $request->all();
    $cajaMaster = array_key_exists('cajaMaster', $data) ? $data['cajaMaster'] : NULL;
    $codes = MasterBox::from('wms_master_box as cm')
      ->where('cm.master', $cajaMaster)
      ->first();
    return $codes;
  }

  public static function guardarArchivoPostman(Request $request)
  {
    $folder = env("AWS_FOLDER");

    $post = new ScheduleImage();
    $post->schedule_id = $request->name;
    $post->document_id = $request->name;
    $post->name_file = $request->name;
    Storage::disk('s3')->put($folder, file_get_contents($request->uri), 'public');
    // Storage::disk('s3')->put($folder, $request->uri, 'public');

    $post->url = $folder . "/" . $request->name;
    $post->save();
  }

  public function buscarStock(Request $request)
  {
    $data = $request->all();
    $position = array_key_exists('position', $data) ? $data['position'] : NULL;
    try {
      $zonePosition = ZonePosition::where('code', $position)->first();
      if (!$zonePosition) {
        throw new InvalidArgumentException("No se encontró una posición para el código ingresado");
      }
      $stock = DB::table('wms_stock')
        ->leftjoin('wms_products', 'wms_stock.product_id', '=', 'wms_products.id')
        ->leftjoin('wms_product_categories', 'wms_products.category_id', '=', 'wms_product_categories.id')
        ->leftjoin('wms_zone_positions', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
        ->leftjoin('wms_zones', 'wms_zone_positions.zone_id', '=', 'wms_zones.id')
        ->where('wms_stock.zone_position_id', $zonePosition->id)
        ->where('wms_stock.quanty', '>', 0)
        ->select(
          'wms_products.description',
          'wms_zone_positions.code as position',
          'wms_products.ean as ean13',
          'wms_products.reference',
          'wms_stock.quanty',
        )->get();

      return  $stock;
    } catch (Exception $e) {
      return response($e->getMessage(), Response::HTTP_CONFLICT);
    }
  }

  public function deleteStock(Request $request)
  {
    $data = $request->all();
    $position = array_key_exists('position', $data) ? $data['position'] : NULL;
    DB::beginTransaction();
    try {
      $zonePosition = ZonePosition::where('code', $position)->first();
      if (!$zonePosition) {
        throw new InvalidArgumentException("No se encontró una posición para el código ingresado");
      }
      $username = User::where('id', $data['session_user_id'])->first();
      $stockModel = Stock::where('zone_position_id', $zonePosition->id)
        ->leftjoin('wms_products', 'wms_stock.product_id', '=', 'wms_products.id')
        ->leftjoin('wms_zone_positions', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
        ->leftjoin('wms_ean_codes128', 'wms_stock.code128_id', '=', 'wms_ean_codes128.id')
        ->select(
          'wms_products.reference',
          'wms_stock.product_id',
          'wms_products.ean',
          'wms_stock.quanty',
          'wms_zone_positions.code',
          'wms_ean_codes128.code128'
        )
        ->get();
      foreach ($stockModel as $value) {
        $stockMovements = new StockMovement();
        $stockMovements->product_id = $value->product_id;
        $stockMovements->product_reference = $value->reference;
        $stockMovements->product_ean = $value->ean;
        $stockMovements->product_quanty = $value->quanty;
        $stockMovements->zone_position_code = $value->code;
        $stockMovements->code128 = $value->code128;
        $stockMovements->username = $username->name;
        $stockMovements->action = 'delete';
        $stockMovements->save();
      }
      Stock::where('zone_position_id', $zonePosition->id)->delete();
      DB::commit();
      return response('Eliminado correctamente', 200);
    } catch (Exception $e) {
      DB::rollBack();
      return response($e->getMessage(), Response::HTTP_CONFLICT);
    }
  }

  /**
   * Método que consulta la información de los documentos en inventario
   * para validar si están disponibles los documentos
   * @author Santiago Muñoz
   */
  public function getDocumentPlanMassiveAllocation(Request $request)
  {
    $data = $request->all();
    $companyId = $request->input('company_id');

    // $documents = DocumentDetail::with('client.city', 'document', 'product.stock.zone_position.zone.warehouse', 'product.stock.ean14', 'product.stock.zone_position.zone.zone_type', 'product.stock.zone_position.concept')
    //   ->whereHas('document', function ($query) use ($companyId) {
    //     $query->where('company_id', $companyId);
    //   })->whereIn('document_id', $data)->get();

    $documents = DB::table('wms_document_details')
      ->join('wms_documents', 'wms_document_details.document_id', '=', 'wms_documents.id')
      ->join('wms_clients', 'wms_documents.client', '=', 'wms_clients.id')
      ->leftJoin('wms_schedule_documents', 'wms_documents.id', 'wms_schedule_documents.document_id')
      ->whereIn('wms_documents.id', $data)
      ->where('wms_documents.company_id', $companyId)
      ->whereNull('wms_schedule_documents.id')
      ->select(DB::raw('SUM(wms_document_details.quanty) as pedido'), DB::raw('GROUP_CONCAT(DISTINCT wms_document_details.product_id) as product_id'), 'wms_documents.warehouse_origin', 'wms_documents.number', 'wms_clients.name as client', 'wms_documents.id as documentId')
      ->groupBy('wms_documents.id')
      ->get();

    foreach ($documents as &$value) {
      if ($value->warehouse_origin == 'ECOMM' || $value->warehouse_origin == '001' || $value->warehouse_origin == 'CONPT' || $value->warehouse_origin == 'DEVOLUCIONES FISICAS' || $value->warehouse_origin == 'DEV.FISICAS CALIDAD' || $value->warehouse_origin == 'DFWEB' || $value->warehouse_origin == 'CLIENTE ESPECIAL SOU' || $value->warehouse_origin == 'CON' || $value->warehouse_origin == '269') {
        $value->warehouse_origin = 'ArtMode';
      }

      $documentDetail = DocumentDetail::where('document_id', $value->documentId)->get();
      $cantInventario = 0;
      foreach ($documentDetail as $dataDetail) {
        $inventario = DB::table('wms_stock')
          ->Join('wms_zone_positions', 'wms_zone_positions.id', '=', 'wms_stock.zone_position_id')
          ->Join('wms_zone_concepts', 'wms_zone_concepts.id', '=', 'wms_zone_positions.concept_id')
          ->where('wms_stock.product_id', $dataDetail->product_id)
          ->where('wms_zone_concepts.name', $value->warehouse_origin)
          ->select(DB::raw('SUM(IFNULL(wms_stock.quanty, 0)) as inventario'))
          ->groupBy('wms_stock.product_id')
          ->first();

        $cantInventario = $cantInventario + ($inventario ? $inventario->inventario : 0);
      }
      $value->inventario = $cantInventario;
    }

    return ['data' => $documents, 'uuid' => SoberanaServices::getUUID()];
  }

  /**
   * Método que contiene el proceso algoritmico para generar el picking por empleado
   * @author Santiago Muñoz
   */
  public function createPickingAllocationMassive(Request $request)
  {
    $data = $request->all();
    $params = array_key_exists('params', $data) ? $data['params'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $arrayDocuments = array_key_exists('arrayDocuments', $data) ? $data['arrayDocuments'] : NULL;
    $documents = array_key_exists('documents', $data) ? $data['documents'] : NULL;
    $uuid = array_key_exists('uuid', $data) ? $data['uuid'] : NULL;

    $personal = $arrayDocuments[0]['personal'];
    $arrayReferences = DocumentDetail::from('wms_document_details as dt')
      ->join('wms_products as p', 'dt.product_id', 'p.id')
      ->where('dt.document_id', $arrayDocuments[0]['documentId'])
      ->selectRaw(
        'dt.quanty as assign_quanty,
      dt.client_id as client,
      dt.document_id,
      p.ean,
      dt.quanty as missing_assing,
      dt.quanty as pedido,
      dt.product_id,
      p.reference'
      )
      ->get();

    DB::beginTransaction();
    try {
      $wave = $this->updateOrCreateWave($uuid, $documents);

      $empleados = count($personal);
      $references = count($arrayReferences);
      $resultado = $references / $empleados;
      $extra = 0;

      if (is_float($resultado)) {
        $sobrante = $references % $empleados;
        $resultado = floor($resultado);
        $extra =  $sobrante + $resultado;
      }

      // Cuando lo hice, solo Dios y yo sabíamos como funcionaba, ahora solo sabe Dios
      // Contador de cantidad de veces que se ha intentado mover este código: 1
      $arrayPos = [];
      for ($i = 0; $i < count($personal); $i++) {
        if ($i != (count($personal) - 1)) {
          $arrayPos[] = ['empleado' => $personal[$i], 'doc' => $resultado];
        } else {
          if ($extra > 0) {
            $arrayPos[] = ['empleado' => $personal[$i], 'doc' => $extra];
          } else {
            $arrayPos[] = ['empleado' => $personal[$i], 'doc' => $resultado];
          }
        }
      }

      $posInicial = 0;
      $posFinal = 0;
      for ($i = 0; $i < count($personal); $i++) {
        for ($j = 0; $j < count($arrayPos); $j++) {
          if ($personal[$i]['id'] == $arrayPos[$j]['empleado']['id']) {
            $posFinal = $posFinal + ($arrayPos[$j]['doc']);
            for ($k = $posInicial; $k <= $posFinal - 1; $k++) {
              $arrayReferences[$posInicial]['personal'] = $personal[$i]['user']['id'];
              $posInicial++;
            }
          }
        }
      }

      foreach ($arrayReferences as $dataReference) {
        $waveEnlistProduct = $this->updateOrCreateWaveEnlistProduct($wave, $dataReference);
        $this->updateOrCreateWaveEnlistProductUser($waveEnlistProduct, $dataReference['assign_quanty'], $dataReference['personal']);
      }

      foreach ($personal as $dataEmpleados) {
        $taskSchedulesW = [
          'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
          'end_date' => $params['end_date'],
          'name' => "Realizar picking masivo por referencias para la ola #$wave->id",
          'schedule_type' => ScheduleType::Task,
          'schedule_action' => ScheduleAction::PickingAllocationMassiveAction,
          'status' => ScheduleStatus::Process,
          'user_id' => $dataEmpleados['user']['id'],
          'parent_schedule_id' => $wave->id,
          'company_id' => $company_id
        ];

        $schedule = Schedule::create($taskSchedulesW);
      }

      $documentsArray = explode(',', $documents);
      foreach ($documentsArray as $dataDocuments) {
        if ($dataDocuments != '') {
          ScheduleDocument::create(['schedule_id' => $schedule->id, 'document_id' => $dataDocuments]);
          Document::where('id', $dataDocuments)->update(['status' => 'picking']);
        }
      }

      DB::commit();
      return response('', 200);
    } catch (\Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response($e->getMessage(), 500);
    }
  }

  /**
   * Método que contiene el proceso algoritmico para generar el picking masivo por documento y empleado
   * asignando la cantidad por empleado, distribuyendo la cantidad de documentos en los empleados seleccionados
   * @author Santiago Muñoz
   */
  public function createPickingAllocationMassiveByDocuments(Request $request)
  {
    $data = $request->all();
    $params = array_key_exists('params', $data) ? $data['params'] : NULL;
    $arrayDocuments = array_key_exists('arrayDocuments', $data) ? $data['arrayDocuments'] : NULL;
    $personal = array_key_exists('personal', $data) ? $data['personal'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $documents = array_key_exists('documents', $data) ? $data['documents'] : NULL;
    $uuid = array_key_exists('uuid', $data) ? $data['uuid'] : NULL;

    DB::beginTransaction();

    try {
      $arrayReferences = DB::table('wms_document_details as dt')
        ->join('wms_products as p', 'dt.product_id', 'p.id')
        ->whereRaw("dt.document_id in (" . substr($documents, 0, -1) . ")")
        ->selectRaw(
          'SUM(dt.quanty) as assign_quanty,
      dt.client_id as client,
      dt.document_id,
      p.ean,
      SUM(dt.quanty) as missing_assing,
      SUM(dt.quanty) as pedido,
      dt.product_id,
      p.reference'
        )
        ->groupBy('dt.product_id')
        ->get();

      $wave = $this->updateOrCreateWave($uuid, $documents);

      $empleados = count($personal);
      $references = count($arrayReferences);
      $resultado = $references / $empleados;
      $extra = 0;

      if (is_float($resultado)) {
        $sobrante = $references % $empleados;
        $resultado = floor($resultado);
        $extra =  $sobrante + $resultado;
      }

      // Cuando lo hice, solo Dios y yo sabíamos como funcionaba, ahora solo sabe Dios
      // Contador de cantidad de veces que se ha intentado mover este código: 1
      $arrayPos = [];
      for ($i = 0; $i < count($personal); $i++) {
        if ($i != (count($personal) - 1)) {
          $arrayPos[] = ['empleado' => $personal[$i], 'doc' => $resultado];
        } else {
          if ($extra > 0) {
            $arrayPos[] = ['empleado' => $personal[$i], 'doc' => $extra];
          } else {
            $arrayPos[] = ['empleado' => $personal[$i], 'doc' => $resultado];
          }
        }
      }

      $posInicial = 0;
      $posFinal = 0;
      for ($i = 0; $i < count($personal); $i++) {
        for ($j = 0; $j < count($arrayPos); $j++) {
          if ($personal[$i]['id'] == $arrayPos[$j]['empleado']['id']) {
            $posFinal = $posFinal + ($arrayPos[$j]['doc']);
            for ($k = $posInicial; $k <= $posFinal - 1; $k++) {
              $arrayReferences[$posInicial]->personal = $personal[$i]['user']['id'];
              $posInicial++;
            }
          }
        }
      }

      foreach ($arrayReferences as $dataReferenceObject) {
        $dataReference = (array) $dataReferenceObject;
        $waveEnlistProduct = $this->updateOrCreateWaveEnlistProduct($wave, $dataReference);
        $this->updateOrCreateWaveEnlistProductUser($waveEnlistProduct, $dataReference['assign_quanty'], $dataReference['personal']);
      }

      foreach ($personal as $dataEmpleados) {
        $taskSchedulesW = [
          'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
          'end_date' => $params['end_date'],
          'name' => "Realizar picking masivo por referencias para la ola #$wave->id",
          'schedule_type' => ScheduleType::Task,
          'schedule_action' => ScheduleAction::PickingAllocationMassiveAction,
          'status' => ScheduleStatus::Process,
          'user_id' => $dataEmpleados['user']['id'],
          'parent_schedule_id' => $wave->id,
          'company_id' => $company_id
        ];

        $schedule = Schedule::create($taskSchedulesW);
      }

      $documentsArray = explode(',', $documents);
      foreach ($documentsArray as $dataDocuments) {
        if ($dataDocuments != '') {
          ScheduleDocument::create(['schedule_id' => $schedule->id, 'document_id' => $dataDocuments]);
          Document::where('id', $dataDocuments)->update(['status' => 'picking']);
        }
      }

      DB::commit();
      return response('', 200);
    } catch (\Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response($e->getMessage(), 500);
    }
  }

  private static function createEnlistProductByAlocationMassive($documentId, $scheduleId)
  {
    $documentDetail = DocumentDetail::where('document_id', $documentId)->get();
    foreach ($documentDetail as $dataDetail) {
      $obj = [
        "product_id" => $dataDetail['product_id'],
        "quanty" => $dataDetail['quanty'],
        "good" => $dataDetail['quanty'],
        "seconds" => 0,
        "unit" => $dataDetail['quanty'],
        "document_id" => $documentId,
        "schedule_id" => $scheduleId
      ];
      EnlistProducts::create($obj);
    }
  }

  public function pickSuggestionAllocationMassive(Request $request)
  {
    $settings = new Settings(22);
    $data = $request->all();
    $userId = $request->input('session_user_id');
    $waveId = $data['parent_id'];
    $gunsmithZone = $settings->Get('dispatch_zone');

    DB::beginTransaction();
    try {

      $zone = Zone::where('name', $gunsmithZone)->first();
      $newZonePosition = ZonePosition::where('zone_id', $zone->id)->first();
      $producto = Product::where('ean', $data['ean'])->first();
      if (!$producto) {
        throw new RuntimeException('No existe el producto.');
      }

      $enlistProductWave = EnlistProductsWaves::join('wms_enlist_products_waves_users', 'wms_enlist_products_waves.id', 'wms_enlist_products_waves_users.enlistproductwave_id')
        ->where('wms_enlist_products_waves.wave_id', $waveId)
        ->where('wms_enlist_products_waves_users.user_id', $userId)
        ->where('wms_enlist_products_waves.product_id', $producto->id)
        ->selectRaw('wms_enlist_products_waves.*')
        ->first();

      if ($enlistProductWave) {
        if ($enlistProductWave->picked_quanty == $enlistProductWave->quanty) {
          throw new RuntimeException('No es posible mercar. El producto ya fue mercado en su totalidad.');
        }

        $position = ZonePosition::where('code', $data['zone_position'])->first();
        if (!$position) {
          throw new RuntimeException('No existe la ubicación ingresada.');
        }

        $stock_search = Stock::where('zone_position_id', $position->id)->where('product_id', $producto->id)->first();
        if (!$stock_search) {
          throw new RuntimeException('No hay inventario en la ubicación ingresada.');
        }

        $enlistProductWaveUser = EnlistProductsWavesUsers::join('wms_enlist_products_waves', 'wms_enlist_products_waves.id', 'wms_enlist_products_waves_users.enlistproductwave_id')
          ->where('wms_enlist_products_waves.wave_id', $waveId)
          ->where('wms_enlist_products_waves_users.user_id', $userId)
          ->where('wms_enlist_products_waves_users.enlistproductwave_id', $enlistProductWave->id)
          ->selectRaw('wms_enlist_products_waves_users.*')
          ->first();

        if ($enlistProductWaveUser->picked_quanty == $enlistProductWaveUser->quanty) {
          throw new RuntimeException('No es posible mercar. El producto ya fue mercado en su totalidad en su tarea.');
        }

        $packingWave =  EnlistPackingWaves::where('wave_id', $enlistProductWave->wave_id)->where('product_id', $producto->id)->first();
        if (!$packingWave) {
          $stock_search->decrement('quanty', 1);

          $enlistProductWave->increment('picked_quanty', 1);
          $enlistProductWaveUser->increment('picked_quanty', 1);
          $objeto = [
            'product_id' => $producto->id,
            'zone_position_id' => $newZonePosition->id,
            'quanty' => 1,
            'code128_id' => $stock_search->code128_id,
            'code_ean14' => $stock_search->code_ean14,
            'document_detail_id' => $stock_search->document_detail_id,
            'quanty_14' => 1,
            'good' => $enlistProductWave->good > 0 ? 1 : 0,
            'seconds' => $enlistProductWave->seconds > 0 ? 1 : 0
          ];
          $stockNew = Stock::create($objeto);

          $obj = [
            "wave_id" => $enlistProductWave->wave_id,
            'quanty' => 1,
            'stock_id' => $stockNew->id,
            'product_id' => $enlistProductWave->product_id
          ];
          EnlistPackingWaves::create($obj);
          if ($stock_search->quanty === 0) {
            $stock_search = Stock::where('zone_position_id', $position->id)->where('product_id', $producto->id)->delete();
          }
        } else {
          $packingWave = EnlistPackingWaves::where('wave_id', $enlistProductWave->wave_id)->where('product_id', $producto->id)->first();
          $stock_search_new = Stock::where('zone_position_id', $newZonePosition->id)->where('product_id', $producto->id)->where('id', $packingWave->stock_id)->first();
          $stock_search = Stock::where('zone_position_id', $position->id)->where('product_id', $producto->id)->first();
          $stock_search->decrement('quanty', 1);

          $enlistProductWave->increment('picked_quanty', 1);
          $enlistProductWaveUser->increment('picked_quanty', 1);
          $stock_search_new->increment('quanty', 1);
          $stock_search_new->increment('quanty_14', 1);

          $packingWave->increment('quanty', 1);
          $stock_searche = Stock::where('zone_position_id', $position->id)->where('product_id', $producto->id)->first();

          if ($stock_searche->quanty === 0) {
            Stock::where('zone_position_id', $position->id)->where('product_id', $producto->id)->delete();
          }
        }
        DB::commit();

        return response('Unidad mercada correctamente', 200);
      }
      return $this->response->error('El producto no se encuentra ligado a esta tarea', 404);
    } catch (\Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response($e->getMessage(), 500);
    }
  }

  /**
   * Método que contiene el proceso algoritmico para crear la tarea de impirmir rótulos de la ola
   * @author Santiago Muñoz
   */
  public function createReubicatePickingAllocationMassive($waveId, Request $request)
  {
    $userId = $request->input('session_user_id');
    $data = $request->all();
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $wave = EnlistProductsWavesUsers::join('wms_enlist_products_waves', 'wms_enlist_products_waves_users.enlistproductwave_id', 'wms_enlist_products_waves.id')
      ->join('wms_waves', 'wms_enlist_products_waves.wave_id', 'wms_waves.id')
      ->selectRaw('wms_waves.*')
      ->where('wms_waves.id', $waveId)
      ->where('wms_enlist_products_waves_users.user_id', $userId)
      ->first();
    $userId = $request->input('session_user_id');

    $taskSchedulesW = [
      'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'end_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'name' => 'Reubicar unidades para la ola #' . $wave->id . ' ' . Carbon::now(new DateTimeZone('America/Bogota')),
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::ReubicateAllocationMassive,
      'status' => ScheduleStatus::Process,
      'user_id' => $userId,
      'parent_schedule_id' => $waveId,
      'company_id' => $company_id
    ];
    Schedule::create($taskSchedulesW);
    return response('Tarea generada con éxito', 200);
  }

  /**
   * Método que contiene el proceso para consultar la información de la referencia de una wave
   * @author Santiago Muñoz
   */
  public function getDataReferenceByTaskPickingEnlist($waveId, Request $request)
  {
    $userId = $request->input('session_user_id');
    $positionRelocated = '';

    $reference = DB::table('wms_enlist_products_waves_users as wepvu')
      ->join('wms_enlist_products_waves as wepv', 'wepvu.enlistproductwave_id', 'wepv.id')
      ->join('wms_packing_waves as wpw', function ($query) {
        $query->on('wepv.product_id', 'wpw.product_id')
          ->on('wepv.wave_id', 'wpw.wave_id');
      })
      ->join('wms_products as p', 'wepv.product_id', 'p.id')
      ->where('wepv.wave_id', $waveId)
      ->where('wepvu.user_id', $userId)
      ->selectRaw('p.reference, p.ean, wepvu.picked_quanty, wepv.wave_id, null as position, wpw.relocated')
      ->get();

    $referenceRelocated = EnlistPackingWaves::where('wave_id', $waveId)->where('relocated', 1)->first();

    if ($referenceRelocated) {
      $stock = Stock::join('wms_zone_positions', 'wms_stock.zone_position_id', 'wms_zone_positions.id')
        ->where('wms_stock.id', $referenceRelocated->stock_id)
        ->select('wms_zone_positions.code')
        ->first();

      $positionRelocated = $stock->code;
    }

    return ['reference' => $reference, 'positionRelocated' => $positionRelocated];
  }

  /**
   * Método que contiene el proceso algoritmico para reubicar la referencia por documento
   * @author Santiago Muñoz
   */
  public function reubicatePickingAllocationMassive(Request $request)
  {
    $data = $request->all();
    $waveId = array_key_exists('parentId', $data) ? $data['parentId'] : NULL;
    $userId = $request->input('session_user_id');
    $positionDestino = array_key_exists('positionDestino', $data) ? $data['positionDestino'] : NULL;
    $ean13 = array_key_exists('ean13', $data) ? $data['ean13'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;

    DB::beginTransaction();
    try {

      $producto = Product::where('ean', $ean13)->first();
      if (!$producto) {
        throw new RuntimeException('No existe el producto.');
      }

      $position = ZonePosition::where('code', $positionDestino)->first();
      if (!$position) {
        throw new RuntimeException('No existe la ubicación ingresada.');
      }

      $wave = DB::table('wms_enlist_products_waves_users as wepvu')
        ->join('wms_enlist_products_waves as wepv', 'wepvu.enlistproductwave_id', 'wepv.id')
        ->where('wepv.wave_id', $waveId)
        ->where('wepvu.user_id', $userId)
        ->where('wepv.product_id', $producto->id)
        ->selectRaw('wepv.wave_id, wepv.product_id, wepvu.picked_quanty')
        ->first();

      if (!$wave) {
        throw new RuntimeException('La referencia ingresada no pertenece a esta tarea.');
      }

      $packing = EnlistPackingWaves::where('wave_id', $wave->wave_id)->where('product_id', $producto->id)->first();

      $stockAtual = Stock::where('id', $packing->stock_id)->first();
      $stockAtual->decrement('quanty', $wave->picked_quanty);

      $stockNuevoId = 0;
      $stockNuevo = Stock::where('zone_position_id', $position->id)->where('product_id', $producto->id)->first();
      if ($stockNuevo) {
        $stockNuevo->zone_position_id = $position->id;
        $stockNuevo->quanty = $stockNuevo->quanty + $wave->picked_quanty;
        $stockNuevo->quanty_14 = $stockNuevo->quanty_14 + $wave->picked_quanty;
        $stockNuevo->save();
        $stockNuevoId = $stockNuevo->id;
      } else {
        $modelStock = new Stock();
        $modelStock->product_id = $producto->id;
        $modelStock->zone_position_id = $position->id;
        $modelStock->active = 1;
        $modelStock->quanty = $wave->picked_quanty;
        $modelStock->quanty_14 = $wave->picked_quanty;
        $modelStock->save();
        $stockNuevoId = $modelStock->id;
      }

      EnlistPackingWaves::where('id', $packing->id)->update(['stock_id' => $stockNuevoId, 'relocated' => 1]);

      if ($stockAtual->quanty === 0) {
        Stock::where('id', $stockAtual->id)->delete();
      }

      DB::commit();

      return response('Unidad reubicada correctamente', 201);
    } catch (\Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response($e->getMessage(), 500);
    }
  }


  /**
   * Método que contiene el proceso algoritmico para crear la tarea de impirmir rótulos de la ola por pedidos
   * @author Santiago Muñoz
   */
  public function taskPrintEanAllocationMassive(Request $request)
  {
    $data = $request->all();
    $uuid = array_key_exists('uuid', $data) ? $data['uuid'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $wave = Waves::where('uuid', $uuid)->first();

    $settingsObj = new Settings($company_id);
    $chargeUserName = $settingsObj->get('picking_dispatch');
    $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
      $q->where('name', $chargeUserName);
    })->where('active', 1)->first();

    if (empty($user)) {
      return $this->response->error('No se encontró un usuario para asignar la tarea', 409);
    }

    $taskSchedulesW = [
      'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'end_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'name' => 'Imprimir rótulos para la ola por pedidos #' . $wave->id . ' ' . Carbon::now(new DateTimeZone('America/Bogota')),
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::PrintAllocationMassive,
      'status' => ScheduleStatus::Process,
      'user_id' => $user->id,
      'parent_schedule_id' => $wave->id,
      'company_id' => $company_id
    ];
    Schedule::create($taskSchedulesW);
    return response('Tarea generada con éxito', 200);
  }

  /**
   * Método que contiene el proceso para generar la tarea de gestión del packing por pedidos
   * @author Santiago Muñoz
   */
  public function createManagePackingAllocationMassive($waveId, Request $request)
  {
    $data = $request->all();
    $companyId = $request->input('company_id');
    $username = User::where('id', $data['session_user_id'])->first();
    $wave = Waves::find($waveId);

    $taskSchedulesW = [
      'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'end_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      'name' => 'Gestionar packing para la ola por pedidos #' . $wave->id . ' ' . Carbon::now(new DateTimeZone('America/Bogota')),
      'schedule_type' => ScheduleType::Task,
      'schedule_action' => ScheduleAction::ManagePackingAllocationMassive,
      'status' => ScheduleStatus::Process,
      'user_id' => $username->id,
      'parent_schedule_id' => $waveId,
      'company_id' => $companyId
    ];
    Schedule::create($taskSchedulesW);

    return [];
  }

  /**
   * Método que contiene el proceso algoritmico para crear un packing masivo partiendo de una wave
   * @author Santiago Muñoz
   */
  public function createPackingAllocationMassive(Request $request)
  {
    $data = $request->all();
    $params = array_key_exists('params', $data) ? $data['params'] : NULL;
    $documents = array_key_exists('documents', $data) ? $data['documents'] : NULL;
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $wave = Waves::find($params['parentId']);
    DB::beginTransaction();
    try {
      foreach ($params['users'] as $dataUser) {
        $taskSchedules = [
          'start_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
          'end_date' => explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
          'name' => "Realizar packing para la ola por pedidos #" . $wave->id . " " . Carbon::now(new DateTimeZone('America/Bogota')),
          'schedule_type' => ScheduleType::Task,
          'schedule_action' => ScheduleAction::PackingAllocationMassiveAction,
          'status' => ScheduleStatus::Process,
          'user_id' => $dataUser['user']['id'],
          'parent_schedule_id' => $wave->id,
          'company_id' => $company_id
        ];
        Schedule::create($taskSchedules);
      }

      foreach ($documents as $dataDocument) {
        Document::where('id', $dataDocument['documentId'])->update(['status' => 'packing']);
      }
      DB::commit();

      return response('Packing creado correctamente', 200);
    } catch (\Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response($e->getMessage(), 500);
    }
  }

  /**
   * Método que contiene el proceso para consultar la sugerencia tanto de eanes como de unidades a empacar
   * en el packing masivo
   * @author Santiago Muñoz
   */
  public function getDataPackingAllocationMassive($waveId, $ean13, Request $request)
  {
    $userId = $request->input('session_user_id');

    $sugerencia = DB::table('wms_waves as ww')
      ->join('wms_waves_eancodes14 as wwe', 'ww.id', 'wwe.wave_id')
      ->join('wms_ean_codes14 as wec', 'wwe.eancode14_id', 'wec.id')
      ->join('wms_documents as wd', 'wec.document_id', 'wd.id')
      ->join('wms_document_details as wdd', 'wd.id', 'wdd.document_id')
      ->join('wms_products as p', 'wdd.product_id', 'p.id')
      ->join('wms_stock as ws', 'wwe.stock_id', 'ws.id')
      ->join('wms_zone_positions as wzp', 'ws.zone_position_id', 'wzp.id')
      ->where('ww.id', $waveId)
      ->where('p.ean', $ean13)
      ->where('wec.stored', 0)
      ->selectRaw('wzp.code as position, wec.code14 , wd.number as pedido, ww.UUID, IF(wec.stored = 1, "Cerrado", "Abierto") as estadoContenedor, wd.id as documentId, wec.id as code14Id, p.id as productoId')
      ->get();

    foreach ($sugerencia as $pedido) {
      $cantidadPedida = DB::table('wms_waves')
        ->join('wms_documents', function ($join) {
          $join->where(DB::raw("FIND_IN_SET(wms_documents.id, wms_waves.documents)"), "<>", "0");
        })
        ->join('wms_document_details', 'wms_documents.id', 'wms_document_details.document_id')
        ->where('wms_waves.id', $waveId)
        ->where('wms_documents.id', $pedido->documentId)
        ->where('wms_document_details.product_id', $pedido->productoId)
        ->selectRaw('SUM(wms_document_details.quanty) as pedido')
        ->first();

      $cantidadEmpacada = DB::table('wms_ean_codes14 as wec')
        ->join('wms_ean_codes14_detail as wecd', 'wec.id', 'wecd.ean_code14_id')
        ->where('wec.document_id', $pedido->documentId)
        ->where('wecd.product_id', $pedido->productoId)
        ->selectRaw('IFNULL(SUM(wecd.quanty), 0) as empacado')
        ->first();

      $pedido->cantidadPedida = $cantidadPedida->pedido;
      $pedido->cantidadEmpacada = $cantidadEmpacada->empacado;
      $pedido->cantidadRestante = $cantidadPedida->pedido - $cantidadEmpacada->empacado;
    }

    $referencias = DB::table('wms_packing_waves')
      ->join('wms_enlist_products_waves', function ($join) {
        $join->on('wms_packing_waves.wave_id', 'wms_enlist_products_waves.wave_id')
          ->on('wms_packing_waves.product_id', 'wms_enlist_products_waves.product_id');
      })
      ->join('wms_products', 'wms_packing_waves.product_id', 'wms_products.id')
      ->where('wms_packing_waves.wave_id', $waveId)
      ->where('wms_packing_waves.relocated', 1)
      ->whereRaw('wms_packing_waves.quanty - wms_packing_waves.packaged_quanty > 0')
      ->select('wms_products.reference', 'wms_products.ean', 'wms_products.description', 'wms_enlist_products_waves.order_quanty as pedido', 'wms_enlist_products_waves.picked_quanty as mercado', 'wms_packing_waves.packaged_quanty as empacado', DB::raw('wms_packing_waves.quanty - wms_packing_waves.packaged_quanty as restante'))
      ->get();

    $isFinally = EnlistPackingWaves::where('wave_id', $waveId)->whereRaw('quanty <> packaged_quanty')->first();
    if ($isFinally) {
      $finaliza = false;
    } else {
      $finaliza = true;
    }

    return ['sugerencia' => $sugerencia, 'referencias' => $referencias, 'finaliza' => $finaliza];
  }

  /**
   * Método que contiene el proceso para guardar las unicades que se van emapcando en el packing masivo
   * indicando la posición donde se van a ubicar para el respectivo despacho
   * @author Santiago Muñoz
   */
  public function savePackingAllocationMassive(Request $request)
  {
    $data = $request->all();
    $parentId = array_key_exists('parentId', $data) ? $data['parentId'] : NULL;
    $position = array_key_exists('position', $data) ? $data['position'] : NULL;
    $ean14 = array_key_exists('ean14', $data) ? $data['ean14'] : NULL;
    $ean13 = array_key_exists('ean13', $data) ? $data['ean13'] : NULL;
    $finaliza = false;

    DB::beginTransaction();
    try {
      $position = ZonePosition::where('code', $position)->first();
      if (!$position) {
        throw new RuntimeException('No existe la ubicación ingresada.');
      }

      // $dataEan14 = DB::table('wms_waves_eancodes14 as wwe')
      //   ->join('wms_ean_codes14 as wec', 'wwe.eancode14_id', 'wec.id')
      //   ->join('wms_stock as ws', 'wwe.stock_id', 'ws.id')
      //   ->where('wec.code14', $ean14)
      //   ->where('wwe.wave_id', $parentId)
      //   ->where('ws.zone_position_id', $position->id)
      //   ->selectRaw('wec.*')
      //   ->first();

      $dataEan14 = DB::table('wms_waves as ww')
        ->join('wms_documents as wd', function ($join) {
          $join->where(DB::raw("FIND_IN_SET(wd.id, ww.documents)"), "<>", "0");
        })
        ->join('wms_ean_codes14 as wec', 'wd.id', 'wec.document_id')
        ->join('wms_document_details as wdd', 'wd.id', 'wdd.document_id')
        ->leftJoin('wms_eancodes14_packing as we14p', function ($query) {
          $query->on('wdd.document_id', '=', 'we14p.document_id');
          $query->on('wdd.product_id', '=', 'we14p.product_id');
        })
        ->join('wms_stock as ws', 'wec.id', 'ws.code14_id')
        ->where('wec.code14', $ean14)
        ->where('ww.id', $parentId)
        ->where('ws.zone_position_id', $position->id)
        ->where('wec.stored', 0)
        ->selectRaw('wec.*')
        ->first();

      if (!$dataEan14) {
        throw new RuntimeException('El EAN 14 o la posición no existe o no están asociados a esta ola');
      }

      if ($dataEan14->stored) {
        throw new RuntimeException('El EAN 14 ya se encuentra cerrado');
      }

      // $dataProduct = Product::where('ean', $ean13)
      //   ->join('wms_packing_waves', 'wms_products.id', '=', 'wms_packing_waves.product_id')
      //   ->where('wms_packing_waves.wave_id', $parentId)
      //   ->selectRaw('wms_products.*')
      //   ->first();

      $dataProduct = DB::table('wms_waves as ww')
        ->join('wms_documents as wd', function ($join) {
          $join->where(DB::raw("FIND_IN_SET(wd.id, ww.documents)"), "<>", "0");
        })
        ->join('wms_document_details as wdd', 'wd.id', 'wdd.document_id')
        ->join('wms_products', 'wdd.product_id', 'wms_products.id')
        ->where('ww.id', $parentId)
        ->where('wms_products.ean', $ean13)
        ->selectRaw('wms_products.*')
        ->first();

      if (!$dataProduct) {
        throw new RuntimeException('No se encontró un producto asociado al ean ingresado');
      }

      $documentProduct = DocumentDetail::where('document_id', $dataEan14->document_id)->where('product_id', $dataProduct->id)->first();
      if (!$documentProduct) {
        throw new RuntimeException('La referencia ingresada no corresponde al pedido');
      }

      $packing = Eancodes14Packing::where('document_id', $dataEan14->document_id)->where('product_id', $dataProduct->id)->first();
      $documentDetail = DocumentDetail::where('document_id', $dataEan14->document_id)->where('product_id', $dataProduct->id)->first();

      $dataEan14Detail = EanCode14Detail::where('ean_code14_id', $dataEan14->id)->where('product_id', $dataProduct->id)->first();
      if ($dataEan14Detail) {
        $dataEan14Detail->quanty = $dataEan14Detail->quanty + 1;
        $dataEan14Detail->good = $dataEan14Detail->good + 1;
        $dataEan14Detail->save();
      } else {
        EanCode14Detail::create([
          "ean_code14_id" => $dataEan14->id,
          "product_id" => $dataProduct->id,
          "quanty" => 1,
          "good" => 1,
          "document_detail_id" => $documentDetail->id,
        ]);
      }

      $dataDocumentPedido = DB::table('wms_waves')
        ->join('wms_documents', function ($join) {
          $join->where(DB::raw("FIND_IN_SET(wms_documents.id, wms_waves.documents)"), "<>", "0");
        })
        ->join('wms_document_details', 'wms_documents.id', 'wms_document_details.document_id')
        ->where('wms_waves.id', $parentId)
        ->where('wms_documents.id', $dataEan14->document_id)
        ->where('wms_document_details.product_id', $dataProduct->id)
        ->selectRaw('SUM(wms_document_details.quanty) as pedido')
        ->first();

      $dataDocumentEmpacado = DB::table('wms_ean_codes14 as wec')
        ->join('wms_ean_codes14_detail as wecd', 'wec.id', 'wecd.ean_code14_id')
        ->where('wec.id', $dataEan14->id)
        ->where('wec.document_id', $dataEan14->document_id)
        ->where('wecd.product_id', $dataProduct->id)
        ->selectRaw('IFNULL(SUM(wecd.quanty), 0) as empacado')
        ->first();

      // dd($dataDocumentPedido, $dataDocumentEmpacado);

      if ($dataDocumentPedido->pedido < $dataDocumentEmpacado->empacado) {
        throw new RuntimeException('Está empacando más unidades de las pedidas en esta referencia para el pedido asociado');
      }

      $stockAtual = Stock::where('id', $packing->stock_id)->first();
      $stockAtual->decrement('quanty', 1);
      $stockAtual->save();
      if ($stockAtual->quanty == 0) {
        Eancodes14Packing::where('id', $packing->id)->update(['stock_id' => null]);
        $stockAtual->delete();
      }

      $dataTransition = StockTransition::where('product_id', $dataProduct->id)->where('document_detail_id', $documentDetail->id)->where('code_ean14', $dataEan14->id)->first();
      if ($dataTransition) {
        $dataTransition->increment('quanty', 1);
        $dataTransition->increment('quanty_14', 1);
        $dataTransition->save();
      } else {
        StockTransition::create([
          "product_id" => $dataProduct->id,
          "quanty" => 1,
          "action" => "output",
          "document_detail_id" => $documentDetail->id,
          "code_ean14" => $dataEan14->id,
          "quanty_14" => 1
        ]);
      }

      // $isFinally = EnlistPackingWaves::where('wave_id', $parentId)->whereRaw('quanty <> packaged_quanty')->first();
      // if ($isFinally) {
      //   $finaliza = false;
      // } else {
      //   $finaliza = true;
      // }

      DB::commit();
      return response(['finaliza' => $finaliza], 201);
    } catch (Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response(["message" => $e->getMessage()], 500);
    }
  }

  /**
   * Método que contiene el proceso cerrar una caja del packing validando que
   * tenga unidades empacadas y que pertenezca a la ola actual
   * adicional crea la tarea de ubicar pedido
   * @author Santiago Muñoz
   */
  public function closeEan14PackingAllocationMassive(Request $request)
  {
    $data = $request->all();
    $waveId = array_key_exists('parentId', $data) ? $data['parentId'] : NULL;
    $ean14 = array_key_exists('ean14', $data) ? $data['ean14'] : NULL;
    $peso = array_key_exists('peso', $data) ? $data['peso'] : NULL;
    $container = array_key_exists('container', $data) ? $data['container'] : NULL;
    $companyId = $request->input('company_id');

    DB::beginTransaction();
    try {

      $data14 = DB::table('wms_waves as ww')
        ->join('wms_documents as wd', function ($join) {
          $join->where(DB::raw("FIND_IN_SET(wd.id, ww.documents)"), "<>", "0");
        })
        ->join('wms_ean_codes14 as wec', 'wd.id', 'wec.document_id')
        ->where('ww.id', $waveId)
        ->where('code14', $ean14)
        ->select('wec.id', 'wec.document_id')
        ->first();

      if (!$data14) {
        throw new RuntimeException("El EAN 14 no está asociado a esta ola");
      }

      $detail = EanCode14Detail::where('ean_code14_id', $data14->id)->first();

      if (!$detail) {
        throw new RuntimeException("El EAN 14 no puede cerrarse porque no contiene unidades");
      }

      EanCode14::where('id', $data14->id)->where('code14', $ean14)->where('status', 20)->update(['stored' => 1, 'weight' => $peso, 'container_id' => $container]);

      $document = Document::where('id', $data14->document_id)->first();

      if ($document->status == 'pending_cancel' || $document->status == 'cancel') {
        throw new RuntimeException("No se puede crear la tarea de ubicar porque el pedido está en proceso de cancelación");
      }

      $validateTask = Schedule::where('parent_schedule_id', $document->id)->where('schedule_action', 'ReubicarPackingAction')->first();
      if (!$validateTask) {
        Document::where('id', $data14->document_id)->update(['status' => 'transsition']);
        $client = Client::where('id', $document->client)->first();

        $taskSchedules = [
          'name' => "Ubicar pedido de: $client->name para el pedido $document->number",
          'schedule_type' => ScheduleType::Task,
          'schedule_action' => ScheduleAction::ReubicarPackingAction,
          'status' => ScheduleStatus::Process,
          'user_id' => $data['session_user_id'],
          'parent_schedule_id' => $document->id,
          'company_id' => $companyId
        ];
        Schedule::create($taskSchedules);
      }

      DB::commit();
      return response(["message" => "EAN cerrado correctamente"], 200);
    } catch (Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response(["message" => $e->getMessage()], 500);
    }
  }

  /**
   * Método que contiene el proceso para crear una caja adicional en la ola
   * @author Santiago Muñoz
   */
  public function generateEan14AditionalPackingAllocationMassive(Request $request)
  {
    $data = $request->all();
    $company_id = array_key_exists('company_id', $data) ? $data['company_id'] : NULL;
    $waveId = array_key_exists('parentId', $data) ? $data['parentId'] : NULL;
    $document = array_key_exists('document', $data) ? $data['document'] : NULL;
    $container = array_key_exists('container', $data) ? $data['container'] : NULL;

    DB::beginTransaction();
    try {

      $document14 = EanCode14::where('document_id', $document)->first();
      $wave14 = WavesCodes14::where('eancode14_id', $document14->id)->where('wave_id', $waveId)->first();

      $data14 = EanCode14::where('status', 20)->orderBy('id', 'desc')->first();
      $code = $data14 ? $data14->code14 + 1 : '10000000000000';
      $code14 = EanCode14::create([
        'code14' => $code,
        'container_id' => $container,
        'document_id' => $document,
        'company_id' => $company_id,
        'status' => 20
      ]);

      WavesCodes14::create([
        'wave_id' => $waveId,
        'eancode14_id' => $code14->id,
        'stock_id' => $wave14->stock_id
      ]);

      $cantRotulos = EanCode14::where('document_id', $document)->selectRaw('COUNT(id) as cantidad')->first();

      $codes14 = EanCode14::with('document.clientdocument')->find($code14->id);
      DB::commit();
      return response(["message" => $codes14, "cantRotulos" => $cantRotulos->cantidad], 200);
    } catch (Exception $e) {
      DB::rollBack();
      return response(["message" => $e->getMessage()], 500);
    }
  }

  /**
   * Método que contiene el proceso para validar que todas las cajas del packing
   * se encuentren cerradas y con peso
   * @author Santiago Muñoz
   */
  public function validateCloseTaskPackingAllocationMassive($waveId, Request $request)
  {
    try {
      $data14 = DB::table('wms_waves as ww')
        ->join('wms_documents as wd', function ($join) {
          $join->where(DB::raw("FIND_IN_SET(wd.id, ww.documents)"), "<>", "0");
        })
        ->join('wms_ean_codes14 as wec', 'wd.id', 'wec.document_id')
        ->where('ww.id', $waveId)
        ->where('stored', 0)
        ->first();

      if ($data14) {
        throw new RuntimeException("El contenedor $data14->code14 no se encuentra cerrado");
      }
      return response([], 200);
    } catch (Exception $e) {
      DB::rollBack();
      if ($e instanceof RuntimeException) {
        return response(["message" => $e->getMessage()], 409);
      }
      return response(["message" => $e->getMessage()], 500);
    }
  }
}
