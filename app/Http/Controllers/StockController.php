<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\EanCode13;
use App\Models\EanCode14;
use App\Models\EanCode14Detail;
use App\Models\EanCode128;
use App\Models\Pallet;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockTransition;
use App\Models\Charge;
use App\Common\Settings;
use App\Models\ZonePosition;
use App\Models\ZoneConcept;
use App\Models\StructureCode;
use App\Models\Suggestion;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\ScheduleTransform;
use App\Models\ScheduleTransformDetail;
use App\Models\ScheduleTransformResult;
use App\Models\ScheduleTransformResultPackaging;
use App\Models\ScheduleUnjoinDetail;
use App\Models\JoinReferences;
use App\Models\User;
use DB;
use App\Common\Codes;
use App\Common\SchedulesFunctions;
use App\Common\StockFunctions;
use App\Enums\PackagingType;
use App\Enums\ScheduleAction;
use App\Enums\ScheduleType;
use App\Enums\ScheduleStatus;
use App\Enums\PackagingStatus;
use App\Enums\TransformDetailType;
use App\Enums\TransformDetailStatus;
use App\Enums\TypeTransform;
use App\Models\ContainerFeature;
use App\Enums\SettingsKey;
use Log;
use App\Models\MergedPosition;
use App\Models\ScheduleTransition;
use App\Models\StockPickingConfig;
use App\Models\ScheduleTransformValidateAdjust;
use App\Models\StockPickingConfigProduct;
use App\Models\DocumentDetail;
use App\Models\PositionFeature;
use DateTimeZone;
use Carbon\Carbon;

class StockController extends BaseController
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
    $ean14 = $request->input('ean14');
    $ean13 = $request->input('ean13');
    $client = $request->input('client_id');
    $position = $request->input('zone_position_id');
    $schedule = $request->input('schedule_id');
    $companyId = $request->input('company_id');
    $codePosition = $request->input('position');
    $category_id = $request->input('category_id');
    $colection = $request->input('colection');
    $short_reference = $request->input('short_reference');
    $concept = $request->input('concept');
    $zone_id = $request->input('zone_id');
    $code14 = $request->input('code14');
    $ean = $request->input('ean');
    $number_document = $request->input('number_document');
    $search = $request->input('search');
    // return$codePosition;
    $stock = DB::table('wms_stock')
      ->leftjoin('wms_products', 'wms_stock.product_id', '=', 'wms_products.id')
      ->leftjoin('wms_product_categories', 'wms_products.category_id', '=', 'wms_product_categories.id')
      // ->leftjoin('wms_product_types', 'wms_product_sub_types.product_type_id', '=', 'wms_product_types.id')
      ->leftjoin('wms_zone_positions', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
      ->leftjoin('wms_zones', 'wms_zone_positions.zone_id', '=', 'wms_zones.id')
      ->leftjoin('wms_warehouses', 'wms_zones.warehouse_id', '=', 'wms_warehouses.id')
      ->leftjoin('wms_ean_codes128', 'wms_stock.code128_id', '=', 'wms_ean_codes128.id')
      // ->leftjoin('wms_document_details', 'wms_stock.document_detail_id', '=', 'wms_document_details.id')
      // ->leftjoin('wms_documents', 'wms_document_details.document_id', '=', 'wms_documents.id')
      ->leftjoin('wms_ean_codes14', 'wms_stock.code_ean14', '=', 'wms_ean_codes14.id')
      ->leftjoin('wms_eancodes14_packing', 'wms_stock.id', '=', 'wms_eancodes14_packing.stock_id')
      ->leftjoin('wms_documents as docpacking', 'wms_eancodes14_packing.document_id', '=', 'docpacking.id')
      ->leftjoin('wms_documents as doc', 'wms_ean_codes14.document_id', '=', 'doc.id')
      ->orderBy('wms_stock.zone_position_id')
      ->select('wms_warehouses.name as bodega', 'wms_products.description as description', 'wms_zone_positions.row', 'wms_zone_positions.module', 'wms_zone_positions.code', 'wms_products.ean', 'wms_products.reference', 'wms_stock.quanty', 'wms_zones.name as zona', 'wms_zones.id as zone_id', 'wms_stock.quanty', 'wms_stock.quanty_14', 'wms_ean_codes14.code14', 'wms_ean_codes14.master', 'wms_products.alt_code as short_reference', 'wms_products.remark as colection', 'doc.facturation_number', 'doc.external_number', DB::raw("IF(doc.number IS NULL OR doc.number = '', docpacking.number, doc.number) as number"));
    // return $stock = $stock->get();
    ///

    // $stock = Stock::with(
    //   'product.product_sub_type.product_type',
    //   'zone_position.zone.warehouse',
    //   'product.client',
    //   'ean128',
    //   'document_detail.document',
    //   'ean14',
    //   'ean13',
    //   'stock_count',
    //   'ean_codes_14_packing.document'
    // )->orderBy('zone_position_id');

    // $stock = $stock->get();
    // $arrStock = $stock->paginate(50);
    // return $arrStock;

    // $stock = $stock->whereHas('zone_position.zone.warehouse.distribution_center', function ($query) use ($companyId) {
    //   $query->where('company_id', $companyId);
    // });

    if (isset($number_document)) {
      $stock = $stock->where('docpacking.number', $number_document);
    }

    if (isset($category_id)) {
      $stock = $stock->where('wms_product_categories.id', $category_id);
    }

    if (isset($colection)) {
      $stock = $stock->where('wms_products.remark', $colection);
    }

    if (isset($ean)) {
      $stock = $stock->where('wms_products.ean', $ean);
    }

    if (isset($zone_id)) {
      $stock = $stock->where('wms_zones.id', $zone_id);
    }

    if (isset($concept)) {
      $stock = $stock->where('wms_zone_positions.concept_id', $concept);
    }

    if (isset($short_reference)) {
      $stock = $stock->where('wms_products.alt_code', $short_reference);
    }


    if (isset($warehouse)) {
      $stock = $stock->where('wms_zones.warehouse_id', $warehouse);
    }

    if (isset($reference)) {
      $stock = $stock->where('wms_products.reference', $reference);
    }

    if (isset($codePosition)) {
      // return 'entro';
      $stock = $stock->where('wms_zone_positions.code', $codePosition);
    }

    if (isset($code14)) {
      // return 'entro';
      $stock = $stock->where('wms_ean_codes14.code14', $code14);
    }



    $stock = $stock->get();
    // $sum = $stock->sum('wms_stock.quanty');



    // $paginate = $stock->paginate(10);

    // $sumpage = $paginate->sum('wms_stock.quanty');

    // $arrStock = $paginate->toArray();
    // $arrStock['total'] = $sum;
    // $arrStock['total_page'] = $sumpage;
    $arrStock = $stock;

    //Check if the counts belongs to the same schedule
    if (isset($schedule)) {
      foreach ($arrStock as &$stocksini) {
        if (array_key_exists('stock_count', $stocksini) && $stocksini['stock_count']['schedule_id'] != $schedule) {
          $stocksini['stock_count'] = null;
        }
      }
    }

    return $arrStock;
  }

  public function indexResume(Request $request)
  {
    $warehouse = $request->input('warehouse_id');
    $reference = $request->input('reference');
    $schedule = $request->input('schedule_id');
    $codePosition = $request->input('position');
    $category_id = $request->input('category_id');
    $colection = $request->input('colection');
    $short_reference = $request->input('short_reference');
    $concept = $request->input('concept');
    $zone_id = $request->input('zone_id');
    $code14 = $request->input('code14');
    $ean = $request->input('ean');
    $number_document = $request->input('number_document');
    $stock = DB::table('wms_stock')
      ->leftjoin('wms_products', 'wms_stock.product_id', '=', 'wms_products.id')
      ->leftjoin('wms_product_categories', 'wms_products.category_id', '=', 'wms_product_categories.id')
      ->leftjoin('wms_zone_positions', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
      ->leftjoin('wms_zones', 'wms_zone_positions.zone_id', '=', 'wms_zones.id')
      ->leftjoin('wms_ean_codes128', 'wms_stock.code128_id', '=', 'wms_ean_codes128.id')
      ->leftjoin('wms_eancodes14_packing', 'wms_stock.id', '=', 'wms_eancodes14_packing.stock_id')
      ->groupBy('wms_products.id', 'wms_zones.id')
      ->orderBy('wms_products.id', 'wms_zones.id')
      ->select(
        'wms_products.description as description',
        'wms_zone_positions.code',
        'wms_products.ean',
        'wms_products.reference',
        DB::raw('SUM(wms_stock.quanty) as quanty'),
        'wms_zones.name as zona',
        'wms_zones.id as zone_id',
        'wms_stock.quanty_14',
        'wms_products.alt_code as short_reference'
      );
    if (isset($number_document)) {
      $stock = $stock->where('docpacking.number', $number_document);
    }

    if (isset($category_id)) {
      $stock = $stock->where('wms_product_categories.id', $category_id);
    }

    if (isset($colection)) {
      $stock = $stock->where('wms_products.remark', $colection);
    }

    if (isset($ean)) {
      $stock = $stock->where('wms_products.ean', $ean);
    }

    if (isset($zone_id)) {
      $stock = $stock->where('wms_zones.id', $zone_id);
    }

    if (isset($concept)) {
      $stock = $stock->where('wms_zone_positions.concept_id', $concept);
    }

    if (isset($short_reference)) {
      $stock = $stock->where('wms_products.alt_code', $short_reference);
    }

    if (isset($warehouse)) {
      $stock = $stock->where('wms_zones.warehouse_id', $warehouse);
    }

    if (isset($reference)) {
      $stock = $stock->where('wms_products.reference', $reference);
    }

    if (isset($codePosition)) {
      $stock = $stock->where('wms_zone_positions.code', $codePosition);
    }

    if (isset($code14)) {
      $stock = $stock->where('wms_ean_codes14.code14', $code14);
    }

    $stock = $stock->get();

    $arrStock = $stock;

    if (isset($schedule)) {
      foreach ($arrStock as &$stocksini) {
        if (array_key_exists('stock_count', $stocksini) && $stocksini['stock_count']['schedule_id'] != $schedule) {
          $stocksini['stock_count'] = null;
        }
      }
    }

    return $arrStock;
  }

  public function getStorageByCode(Request $request)
  {
    $data = $request->all();
    $code128_id = array_key_exists('code128_id', $data) ? $data['code128_id'] : null;
    $code14_id = array_key_exists('code14_id', $data) ? $data['code14_id'] : null;
    $product_id = array_key_exists('product_id', $data) ? $data['product_id'] : null;
    $companyId = $request->input('company_id');
    $settingsObj = new Settings($companyId);
    $position = $settingsObj->get('stock_zone');
    // return $companyId;

    $stock = Stock::with(
      'product',
      'zone_position.zone',
      'ean128',
      'ean14',
      'ean13'
    )->whereHas('zone_position.zone', function ($query) use ($position) {
      $query->where('name', $position);
    });
    if (!empty($code128_id)) {
      $stock->where('code128_id', $code128_id);
    }
    if (!empty($code14_id)) {
      $stock->where('code14_id', $code14_id);
    }
    if (!empty($product_id)) {
      $stock->where('product_id', $product_id);
    }

    return $stock->first();
  }
  public function getTransitionByCode(Request $request)
  {
    $data = $request->all();
    $code128_id = array_key_exists('code128_id', $data) ? $data['code128_id'] : null;
    $code14_id = array_key_exists('code14_id', $data) ? $data['code14_id'] : null;
    $product_id = array_key_exists('product_id', $data) ? $data['product_id'] : null;

    $stock = StockTransition::with(
      'product',
      'zone_position.zone',
      'ean128',
      'ean14',
      'ean13'
    );
    if (!empty($code128_id)) {
      $stock->where('code128_id', $code128_id);
    }
    if (!empty($code14_id)) {
      $stock->where('code14_id', $code14_id);
    }
    if (!empty($product_id)) {
      $stock->where('product_id', $product_id);
    }

    return $stock->first();
  }

  public function getUnity14ByPositioByProduct(Request $request, $id)
  {
    $positioncode = $request['position'];
    $companyId = $request->input('company_id');
    $zone_position = ZonePosition::where('code', $positioncode)
      ->whereHas('zone.warehouse.distribution_center', function ($q) {
        $q->where('company_id', $companyId);
      })
      ->first();
    if (!isset($zone_position)) {
      return $this->response->error('storage_pallet_position_no_found', 404);
    }

    $product = Product::where('code', $id)->first();
    if (!isset($product)) {
      return $this->response->error('relocate_product_no_found', 404);
    }

    $codes14 = DB::table('wms_stock')
      ->join('wms_ean_codes14', 'wms_ean_codes14.id', '=', 'wms_stock.code14_id')
      ->join('wms_containers', 'wms_ean_codes14.container_id', '=', 'wms_containers.id')
      ->join('wms_container_types', 'wms_containers.container_type_id', '=', 'wms_container_types.id')

      ->where('wms_stock.product_id', $product['id'])
      ->where('wms_stock.zone_position_id', $zone_position['id'])
      ->whereNotNull('wms_stock.code14_id')
      ->select(DB::raw('0 as container_pallet_id'), DB::raw('0 as code128_id'), DB::raw('"" as code_pallet'), 'wms_ean_codes14.id as code14_id', 'wms_stock.quanty as quanty_products', 'wms_stock.id', 'wms_ean_codes14.code14 as code', 'wms_containers.name as container');
    // // ->orderBy('wms_pallet.code128_id')
    //
    // $allcodes  = $pallet->get();
    // return $allcodes;

    $pallet = DB::table('wms_pallet')
      ->join('wms_ean_codes14', 'wms_ean_codes14.id', '=', 'wms_pallet.code14_id')
      ->join('wms_ean_codes128', 'wms_ean_codes128.id', '=', 'wms_pallet.code128_id')
      ->join('wms_stock', 'wms_ean_codes128.id', '=', 'wms_stock.code128_id')
      ->join('wms_containers', 'wms_ean_codes14.container_id', '=', 'wms_containers.id')
      ->join('wms_container_types', 'wms_containers.container_type_id', '=', 'wms_container_types.id')
      ->join('wms_containers as container_pallet', 'wms_ean_codes128.container_id', '=', 'container_pallet.id')

      ->where('wms_ean_codes14.code13', $id)
      ->where('wms_stock.zone_position_id', $zone_position['id'])
      ->whereNotNull('wms_pallet.code14_id')
      ->whereNotNull('wms_stock.code128_id')
      ->select(DB::raw('container_pallet.id as container_pallet_id'), DB::raw('wms_ean_codes128.id as code128_id'), DB::raw('wms_ean_codes128.code128 as code_pallet'), 'wms_ean_codes14.id as code14_id', 'wms_ean_codes14.quanty as quanty_products', 'wms_stock.id', 'wms_ean_codes14.code14 as code', 'wms_containers.name as container')->union($codes14)->get();
    // ->orderBy('wms_pallet.code128_id')

    // $allcodes  = $codes14->merge($pallet);
    return $pallet;
  }

  public function convert(Request $request)
  {
    $data = $request->all();
    $companyId =
      $positioncodesoure = $data['positionSource'];
    $codeinput = array_key_exists('codeunity', $data) ? $data['codeunity'] : null;

    //Buscamos la posiciÃ³n de origen
    $findpositionsource = ZonePosition::where('code', $positioncodesoure)
      ->whereHas('zone.warehouse.distribution_center', function ($q) {
        $q->where('company_id', $companyId);
      })
      ->first();

    if (!isset($findpositionsource)) {
      return $this->response->error('storage_pallet_position_source_no_found', 404);
    }

    $product = Product::where('code', $codeinput)->first();

    if (!isset($product)) {
      return $this->response->error('storage_pallet_product_no_found', 404);
    }

    return $this->response->noContent();
  }

  public function relocate(Request $request)
  {
    $data = $request->all();
    $companyId = $data['company_id'];
    $positioncodesoure = $data['positionSource'];
    $packagingType = $data['packaging_type'];
    $codeinput = array_key_exists('codeunity', $data) ? $data['codeunity'] : null;
    $code128source = array_key_exists('code128discount', $data) ? $data['code128discount'] : null;
    $code128add = array_key_exists('code128add', $data) ? $data['code128add'] : null;

    $code14source = array_key_exists('code14discount', $data) ? $data['code14discount'] : null;
    $code14add = array_key_exists('code14add', $data) ? $data['code14add'] : null;

    $positioncodetarget = $data['positionTarget'];
    $reasonCode = $data['reasonCode'];

    //Consultamos el concepto de zona para cambiar el estado a no disponible de la posiciÃ³n
    $zonepconcept = ZoneConcept::where('is_storage', true)->where('active', true)->first();

    if (!isset($zonepconcept)) {
      return $this->response->error('storage_pallet_zone_concept_no_found', 404);
    }


    if ($packagingType == PackagingType::Logistica) {

      $findcode128 = EanCode128::where('code128', $codeinput)->first();
      if (!isset($findcode128)) {
        return $this->response->error('storage_pallet_code128_no_found', 404);
      }
      //Consultamos el pallet(cÃ³digo128)
      // $findpallet = Pallet::where('code128_id', $findcode128['id'])->first();

      DB::transaction(function () use ($zonepconcept, $findcode128, $positioncodesoure, $positioncodetarget, $reasonCode, $companyId) {

        //Buscamos la posiciÃ³n de origen
        $findpositionsource = ZonePosition::where('code', $positioncodesoure)
          ->whereHas('zone.warehouse.distribution_center', function ($q) {
            $q->where('company_id', $companyId);
          })
          ->first();

        if (!isset($findpositionsource)) {
          return $this->response->error('storage_pallet_position_source_no_found', 404);
        }

        //Buscamos la posiciÃ³n en la q se quiere almacenar
        $findpositiontarget = ZonePosition::where('code', $positioncodetarget)
          ->whereHas('zone.warehouse.distribution_center', function ($q) {
            $q->where('company_id', $companyId);
          })
          ->first();

        if (!isset($findpositiontarget)) {
          return $this->response->error('storage_pallet_position_target_no_found', 404);
        }

        //Buscamos el stock para la posiciÃ³n y la unidad y lo eliminamos
        // $positionsstock = Stock::where('code128_id',$findcode128['id'])->where('zone_position_id',$findpositionsource['id'])->where('active',true)->with('product','zone_position')->first();
        // if(!isset($positionsstock))
        // {
        //  return $this->response->error('relocate_pallet_position_no_found', 404);
        // }

        // $positionsstock->reason_code_id = $reasonCode;
        // $positionsstock->active = false;
        // $positionsstock->save();

        //Validamos que la posiciÃ³n se encuentre disponible para almacenar
        if (!$findpositiontarget['active']) {
          return $this->response->error('storage_pallet_position_unavailable', 404);
        }

        $affectedRows =  Stock::where('code128_id', $findcode128['id'])
          ->where('zone_position_id', $findpositionsource['id'])
          ->update(['zone_position_id' => $findpositiontarget['id']]);
        if ($affectedRows <= 0) {
          return $this->response->error('relocate_pallet_position_no_found', 404);
        }

        //Volumen
        // $findcode128 = EanCode128::where('code128', $codeinput)->first();
        $containerId = $findcode128->container_id;
        $cFeatures = ContainerFeature::where('container_id', $containerId)->get();

        $posHelper = ZonePosition::with('zone_position_features.feature')->where('code', $findcode128->code)->first();

        $weight = $findcode128->weight;
        //Compare the Capacidad (kg) againts $weight
        $settingsObj = new Settings($companyId);
        $fCapacity = $settingsObj->get(SettingsKey::FEATURE_CAPACITY);

        $containerId = $findpositiontarget->container_id;
        $cFeatures = ContainerFeature::where('container_id', $containerId)->get();

        //Validate the weight at target
        $findcode128 = EanCode128::where('code128', $positioncodetarget)->first();
        try {
          $positionHelper = ZonePosition::where('code', $findcode128->code)->whereHas('zone_position_features', function ($q) use ($fCapacity, $weight) {
            $q->whereHas('feature', function ($query) use ($fCapacity) {
              $query->where('name', $fCapacity);
            })->where('value', '>=', $weight);
          })->firstOrFail();
        } catch (\Exception $e) {
          //Exceed the weight
          return $this->response->error('storage_pallet_position_weight', 404);
        }

        //Increment the weight and the features (source)
        foreach ($cFeatures as $feature) {
          $value = $feature->value;
          foreach ($posHelper->zone_position_features as $fPos) {
            //Decrement the capacity
            if ($fPos->feature->name == $fCapacity) {
              $fPos->increment('free_value', $weight);
              continue;
            }
          }
        }

        //Decrement the weight and the features (target)
        $posHelper = ZonePosition::with('zone_position_features.feature')->where('code', $findcode128->code)->first();
        foreach ($cFeatures as $feature) {
          $value = $feature->value;
          foreach ($posHelper->zone_position_features as $fPos) {
            //Decrement the capacity
            if ($fPos->feature->name == $fCapacity) {
              $fPos->increment('free_value', $weight);
              continue;
            }
          }
        }


        // TODO : Agregar registro del movimiento en una tabla aparte a la de stock

        $findpositionsource->concept_id = NULL;
        //Cambiamos el estado de la posiciÃ³n para indicar que se encuentra libre
        //TODO : Se comenta mientras se conoce bn la validaciÃ³n de cuando una posiciÃ³n esta totalmente ocupada o libre segÃºn las caracterÃ­zticas
        // $findpositionsource->active = true;
        $findpositionsource->save();



        // $findpositiontarget->concept_id = $zonepconcept['id'];
        //Cambiamos el estado de la posiciÃ³n para indicar que se encuentra ocupada
        //Se comenta mientras se conoce bn la validaciÃ³n de cuando una posiciÃ³n esta totalmente ocupada segÃºn las caracterÃ­zticas|
        // $findpositiontarget->active = false;
        // $findpositiontarget->save();


        // $stock = ['code128_id' =>$findcode128['id'],
        // 'zone_position_id'=>$findpositiontarget['id'],
        // 'product_id'=>$positionsstock['product_id'],
        // 'quanty'=>$positionsstock['quanty']];
        // Stock::create($stock);
      });
    } else  if ($packagingType == PackagingType::Empaque) {
      //Consultamos la unidad de empaque(cÃ³digo14)
      $code14find = Codes::GetCode14ByCode($codeinput);
      if (!isset($code14find[0])) {
        return $this->response->error('storage_pallet_code14_no_found', 404);
      }

      $code14 = get_object_vars($code14find[0]);

      DB::transaction(function () use ($code14, $zonepconcept, $positioncodesoure, $positioncodetarget, $reasonCode, $code128add, $code128source, $companyId) {

        //Buscamos la posiciÃ³n de origen

        $findpositionsource = ZonePosition::where('code', $positioncodesoure)
          ->whereHas('zone.warehouse.distribution_center', function ($q) {
            $q->where('company_id', $companyId);
          })
          ->first();

        if (!isset($findpositionsource)) {
          return $this->response->error('storage_pallet_position_source_no_found', 404);
        }

        //Buscamos la posiciÃ³n en la q se quiere almacenar
        $findpositiontarget = ZonePosition::where('code', $positioncodetarget)
          ->whereHas('zone.warehouse.distribution_center', function ($q) {
            $q->where('company_id', $companyId);
          })
          ->first();

        if (!isset($findpositiontarget)) {
          return $this->response->error('storage_pallet_position_target_no_found', 404);
        }

        if (!$findpositiontarget['active']) {
          return $this->response->error('storage_pallet_position_unavailable', 404);
        }

        $affectedRows =  Stock::where('code14_id', $code14['ean_code14_id'])
          ->where('zone_position_id', $findpositionsource['id'])
          ->update([
            'zone_position_id' => $findpositiontarget['id'],
            'code128_id' => NULL,
          ]);
        if ($affectedRows <= 0) {
          return $this->response->error('relocate_pallet_position_no_found', 404);
        }


        $count = Stock::where('zone_position_id', $findpositionsource['id'])->count();
        if ($count <= 0) {
          $findpositionsource->concept_id = NULL;
          //Cambiamos el estado de la posiciÃ³n para indicar que se encuentra libre
          //TODO : Se comenta mientras se conoce bn la validaciÃ³n de cuando una posiciÃ³n esta totalmente ocupada o libre segÃºn las caracterÃ­zticas
          // $findpositionsource->active = true;
          $findpositionsource->save();
        }

        // $findpositiontarget->concept_id = $zonepconcept['id'];
        //Cambiamos el estado de la posiciÃ³n para indicar que se encuentra ocupada
        //Se comenta mientras se conoce bn la validaciÃ³n de cuando una posiciÃ³n esta totalmente ocupada segÃºn las caracterÃ­zticas|
        // $findpositiontarget->active = false;
        // $findpositiontarget->save();

        //Si se quiere descontar el code14 de un pallet
        /*if ($code128source != null) {
          $findcode128 = EanCode128::where('id', $code128source['code128_id'])->first();
          if(!isset($findcode128))
          {
          return $this->response->error('storage_pallet_code128_source_no_found', 404);
        }
        //Descontamos la unidad del pallet
        $findpallet = Pallet::where('code128_id', $findcode128['id'])->where('code14_id',$code14['ean_code14_id'])->first();
        $findpallet->delete();

        //Preguntamos si se quiere almacenar dentro de un pallet
        if($code128add != null)
        {
        $newpallet = ['code128_id' =>$code128add['code128_id'],
        'code14_id'=>$code14['ean_code14_id']];
        Pallet::create($newpallet);
      }
      else {
      $positionsstock = Stock::where('code128_id',$findcode128['id'])->where('zone_position_id',$findpositionsource['id'])->where('active',true)->with('product','zone_position')->first();
      if(!isset($positionsstock))
      {
      return $this->response->error('relocate_pallet_position_no_found', 404);
    }

    $stock = ['code128_id' =>$findpallet['code128_id'],
    'zone_position_id'=>$findpositiontarget['id'],
    'product_id'=>$positionsstock['product_id'],
    'quanty'=>$positionsstock['quanty']];
    Stock::create($stock);
    }
  }
  else {
  //Descontamos el code14 de stock
  $positionsstock = Stock::where('code14_id',$code14['ean_code14_id'])->where('zone_position_id',$findpositionsource['id'])->where('active',true)->with('product','zone_position')->first();
  if(!isset($positionsstock))
  {
  return $this->response->error('relocate_pallet_position_no_found', 404);
  }
  $positionsstock->reason_code_id = $reasonCode;
  $positionsstock->active = false;
  $positionsstock->save();

  //Preguntamos si se quiere almacenar dentro de un pallet
  if($code128add != null)
  {
  $findcode128target = EanCode128::where('id', $code128add['code128_id'])->first();
  if(!isset($findcode128target))
  {
  return $this->response->error('storage_pallet_code128_target_no_found', 404);
  }

  $newpallet = ['code128_id' =>$findcode128target['id'],
  'code14_id'=>$code14['ean_code14_id']];
  Pallet::create($newpallet);
  }
  else {
  $stock = ['code14_id' =>$code14['ean_code14_id'],
  'zone_position_id'=>$findpositiontarget['id'],
  'product_id'=>$positionsstock['product_id'],
  'quanty'=>$positionsstock['quanty']];
  Stock::create($stock);
  }
  }*/
      });
    } else  if ($packagingType == PackagingType::Producto) {
      //Consultamos el producto que se quiere reubicar
      $product = Product::where('code', $codeinput)->first();

      if (!isset($product)) {
        return $this->response->error('storage_pallet_product_no_found', 404);
      }

      DB::transaction(function () use ($product, $zonepconcept, $positioncodesoure, $positioncodetarget, $reasonCode, $code128add, $code128source, $code14source, $code14add, $companyId) {

        //Buscamos la posiciÃ³n de origen
        $findpositionsource = ZonePosition::where('code', $positioncodesoure)
          ->whereHas('zone.warehouse.distribution_center', function ($q) {
            $q->where('company_id', $companyId);
          })
          ->first();

        if (!isset($findpositionsource)) {
          return $this->response->error('storage_pallet_position_source_no_found', 404);
        }

        //Buscamos la posiciÃ³n en la q se quiere almacenar
        $findpositiontarget = ZonePosition::where('code', $positioncodetarget)
          ->whereHas('zone.warehouse.distribution_center', function ($q) {
            $q->where('company_id', $companyId);
          })
          ->first();

        if (!isset($findpositiontarget)) {
          return $this->response->error('storage_pallet_position_target_no_found', 404);
        }

        //Si se quiere descontar el producto  de una caja code14
        if ($code14source != null) {
          //Preguntamos si la caja14 de la que se quiere descontar estÃ¡ dentro de un pallet
          if ($code14source['code128_id'] > 0) {
            $findcode14discount = EanCode14::where('id', $code14source['code14_id'])->first();
            $findcode14discount->quanty = $findcode14discount['quanty'] - 1;
            $findcode14discount->save();
          } else { //Si se quiere desocntar de una caja directamente del stock solo restamos una unidad
            $positionsstockdiscount = Stock::where('product_id', $product['id'])->where('zone_position_id', $findpositionsource['id'])->where('code14_id', $code14source['code14_id'])->where('active', true)->first();

            $positionsstockdiscount->quanty = $positionsstockdiscount['quanty'] - 1;
            $positionsstockdiscount->save();
          }
        } else {
          //Descontamos el producto del stock
          $positionsstock = Stock::where('product_id', $product['id'])->where('zone_position_id', $findpositionsource['id'])->where('code14_id', null)->where('active', true)->with('product', 'zone_position')->first();
          if (!isset($positionsstock)) {
            return $this->response->error('relocate_pallet_position_no_found', 404);
          }
          $positionsstock->reason_code_id = $reasonCode;
          $positionsstock->active = false;
          $positionsstock->save();
        }

        //Preguntamos si se quiere almacenar dentro de una caja14
        if ($code14add != null) {
          //Si la caja estÃ¡ dentro de un pallet
          if ($code14add['code128_id'] > 0) {
            $findcode14add = EanCode14::where('id', $code14add['code14_id'])->first();
            $findcode14add->quanty = $findcode14add['quanty'] + 1;
            $findcode14add->save();
          } else { //Se almacena el producto en la tabla stock y dentro de una caja14
            $positionsstocknew = Stock::where('product_id', $product['id'])->where('zone_position_id', $findpositionsource['id'])->where('code14_id', $code14add['code14_id'])->where('active', true)->first();
            if (!isset($positionsstocknew)) {
              return $this->response->error('relocate_pallet_position_no_found', 404);
            }
            // $positionsstocknew->reason_code_id = $reasonCode;
            // $positionsstocknew->active = false;
            $positionsstocknew->quanty = $positionsstocknew['quanty'] + 1;
            $positionsstocknew->save();
          }
        } else {
          $stock = [
            'zone_position_id' => $findpositiontarget['id'],
            'product_id' => $product['id'],
            'quanty' => 1
          ];
          Stock::create($stock);
        }
      });
    }

    return $this->response->noContent();
  }


  public function relocateRemove(Request $request)
  {
    $data = $request->all();

    $companyId = $data['company_id'];
    $positionCode = array_key_exists('position', $data) ? $data['position'] : NULL;
    $packagingType = array_key_exists('packaging_type', $data) ? $data['packaging_type'] : NULL;
    $packagingProduct = array_key_exists('packaging', $data) ? $data['packaging'] : NULL;
    $codeInput = array_key_exists('codeunity', $data) ? $data['codeunity'] : NULL;
    $session_user_id = array_key_exists('session_user_id', $data) ? $data['session_user_id'] : NULL;
    $scheduleId = array_key_exists('schedule_id', $data) ? $data['schedule_id'] : NULL;
    $produc_Id = array_key_exists('product_id', $data) ? $data['product_id'] : NULL;
    $is_secondary = array_key_exists('is_secondary', $data) ? $data['is_secondary'] : NULL;
    $code14 = array_key_exists('code14', $data) ? $data['code14'] : NULL;

    $username = $this->getUsernameById($session_user_id);

    $dataRes = [];
    if ($packagingType == PackagingType::Logistica) {
      $findcode128 = EanCode128::where('code128', $codeInput)->first();
      if (!isset($findcode128)) {
        return $this->response->error('storage_pallet_code128_no_found', 404);
      }
      DB::transaction(function () use ($findcode128, $positionCode, &$dataRes, $session_user_id, $username, $scheduleId, $codeInput, $produc_Id, $is_secondary, $code14) {
        //Buscamos la posiciÃ³n de origen
        $findposition = ZonePosition::with('zone_position_features.feature')->where('code', $positionCode)->first();

        if (empty($findposition)) {
          return $this->response->error('storage_pallet_position_source_no_found', 404);
        }

        $findStock = Stock::with(
          'product',
          'zone_position.zone',
          'ean128',
          'ean14',
          'ean13'
        )->where('code128_id', $findcode128['id'])
          ->where('zone_position_id', $findposition['id'])->get()->toArray();

        // Consulta si la posicion se encuentra compuesta por mas posiciones
        $mergedPosition = MergedPosition::where('code128', $findcode128['id'])->first();

        $datatransition = [];
        if (!empty($findStock)) {

          // Se recorre cada caja del pallet y se retira una a una insertando cada caja en transicion y en movimientos

          foreach ($findStock as $key => $value) {
            // Inserta los registros del stock a la tabla de transicion
            $objTransition = [
              'product_id' => $value['product_id'],
              'zone_position_id' => $value['zone_position_id'],
              'code128_id' => $value['code128_id'],
              'code14_id' => $value['code14_id'],
              'quanty' => $value['quanty'],
              // TODO: Agregar enum a la action
              'action' => 'output',
              'concept' => 'relocate',
              'warehouse_id' => $value['zone_position']['zone']['warehouse_id'],
              'user_id' => $session_user_id,
            ];

            if (isset($scheduleId)) {

              $find13 = Stock::whereIn('product_id', $produc_Id)
                ->where('code128_id', $value['code128_id'])
                ->whereHas('zone_position.zone', function ($q) {
                  $q->where('is_secondary', false);
                })
                ->get();

              if (count($find13)) {
                $StockTransition =  StockTransition::create($objTransition);
              } else {
                return $this->response->error('storage_pallet_13_source_no_found', 404);
              }
            } else {
              $StockTransition =  StockTransition::create($objTransition);
            }


            if (isset($scheduleId)) {
              $ScheduleTransition = [
                'transition_id' => $StockTransition->id,
                'schedule_id' => $scheduleId
              ];

              $sheduleTransition =  ScheduleTransition::create($ScheduleTransition);
            }


            // Crea el registro del movimiento
            $stockMovement = [
              'product_id' => $value['product_id'],
              'product_reference' => $value['product']['reference'],
              'product_ean' => $value['product']['ean'],
              'product_quanty' => $value['quanty'],
              'zone_position_code' => $value['zone_position']['code'],
              'code128' => $value['ean128']['code128'],
              'code14' => $value['ean14']['code14'],
              'username' => $username,
              'warehouse_id' => $value['zone_position']['zone']['warehouse_id'],
              // TODO: Agregar enum a la action
              'action' => 'output',
              'concept' => 'relocate'
            ];
            array_push($datatransition, $stockMovement);

            StockMovement::create($stockMovement);
          }


          // Borra los registros del stock
          $stockDeleted = Stock::where('code128_id', $findcode128['id'])
            ->where('zone_position_id', $findposition['id'])->delete();


          // Se debe validar las posiciones que se intervinieron en la transaccion, para verificar si despues del movimiento siguen ocupadas o si ya quedan libres

          // se prepara un arreglo para las tratar las posiciones intervenidas
          $positionsPallet = [];
          // Se ingresa por defecto la posicion inicial o la posicion real ocupada
          array_push($positionsPallet, $findposition);

          // Se evalua si existen posiciones compuestas, y de ser asi se remplaza la posicion original por las posiciones compuestas
          if (!empty($mergedPosition)) {
            if ($mergedPosition->id > 0) {

              $findpositionsMerged = ZonePosition::whereBetween('id', array(
                $mergedPosition->from_position_id,
                $mergedPosition->to_position_id
              ))->get();


              if (!empty($findpositionsMerged)) {
                $positionsPallet = $findpositionsMerged;
                $mergedPosition->delete();
              }
            }
          }

          //Decrease weight
          // $cFeatures = ContainerFeature::where('container_id', $findcode128['container_id'])->get();
          //Compare the Capacidad (kg) againts $weight
          $settingsObj = new Settings($companyId);
          $fCapacity = $settingsObj->get(SettingsKey::FEATURE_CAPACITY);
          $hCapacity = $settingsObj->get(SettingsKey::FEATURE_HEIGHT);

          $totalPositions = count($positionsPallet);
          foreach ($positionsPallet as $key => $position) {
            $weight = $findcode128['weight'] / $totalPositions;
            $height = $findcode128['height'];
            //For each container feature reduce the position feature
            // foreach ($cFeatures as $feature) {
            foreach ($position->zone_position_features as $fPos) {
              //Decrement the capacity
              if ($fPos->feature->name == $fCapacity) {
                $fPos->increment('free_value', $weight);
                // break;
              } else if ($fPos->feature->name == $hCapacity) {
                $fPos->increment('free_value', $height);
                // break;
              }
            }
            // }

            // Habilita la posicion
            $stockByPosition = StockFunctions::findStockByPosition($position['id']);
            $count = count($stockByPosition);
            // $count = Stock::where('zone_position_id', $position['id'])->count();
            if ($count <= 0) {
              //Cambiamos el estado de la posiciÃ³n para indicar que se encuentra libre
              //TODO : Se comenta mientras se conoce bn la validaciÃ³n de cuando una posiciÃ³n esta totalmente ocupada o libre segÃºn las caracterÃ­zticas
              $position->concept_id = NULL;
              $position->save();
            }
          }
        } else {
          return $this->response->error('storage_pallet_stock_not_found', 404);
        }
        $dataRes =  $datatransition;
      });
    } else if ($packagingType == PackagingType::Empaque) {
      $code14find = EanCode14::where('code14', $codeInput)->first();
      if (!isset($code14find)) {
        return $this->response->error('storage_pallet_code14_no_found', 404);
      }
      DB::transaction(function () use ($code14find, $positionCode, &$dataRes, $session_user_id, $username, $scheduleId, $codeInput, $produc_Id, $is_secondary, $code14) {

        //Buscamos la posiciÃ³n de origen
        $findposition = ZonePosition::where('code', $positionCode)
          ->whereHas('zone.warehouse.distribution_center', function ($q) {
            $q->where('company_id', $companyId);
          })
          ->first();

        if (empty($findposition)) {
          return $this->response->error('storage_pallet_code14_no_found', 404);
        }

        $findStock = Stock::with(
          'product',
          'zone_position.zone',
          'ean128',
          'ean14',
          'ean13'
        )->where('code14_id', $code14find['id'])
          ->where('zone_position_id', $findposition['id'])->get()->toArray();

        $datatransition = [];
        if (!empty($findStock)) {

          foreach ($findStock as $key => $value) {

            // Inserta los registros del stock a la tabla de transicion
            $objTransition = [
              'product_id' => $value['product_id'],
              'zone_position_id' => $value['zone_position_id'],
              // 'code128_id'=>$value['code128_id'],
              'code14_id' => $value['code14_id'],
              'quanty' => $value['quanty'],
              // TODO: Agregar enum a la action
              'action' => 'output',
              'concept' => 'relocate',
              'warehouse_id' => $value['zone_position']['zone']['warehouse_id'],
              'user_id' => $session_user_id,
            ];

            if (isset($scheduleId)) {

              $find13 = Stock::whereIn('product_id', $produc_Id)
                ->where('code14_id', $value['code14_id'])
                ->whereHas('zone_position.zone', function ($q) {
                  $q->where('is_secondary', false);
                })
                ->get();


              if (count($find13)) {
                $StockTransition =  StockTransition::create($objTransition);
              } else {
                return $this->response->error('storage_pallet_13_source_no_found', 404);
              }
            } else {
              $StockTransition =  StockTransition::create($objTransition);
            }
            if (isset($scheduleId)) {
              $ScheduleTransition = [
                'transition_id' => $StockTransition->id,
                'schedule_id' => $scheduleId
              ];

              $sheduleTransition =  ScheduleTransition::create($ScheduleTransition);
            }


            // Crea el registro del movimiento
            $stockMovement = [
              'product_id' => $value['product_id'],
              'product_reference' => $value['product']['reference'],
              'product_ean' => $value['product']['ean'],
              'product_quanty' => $value['quanty'],
              'zone_position_code' => $value['zone_position']['code'],
              'code128' => $value['ean128']['code128'],
              'code14' => $value['ean14']['code14'],
              'username' => $username,
              'warehouse_id' => $value['zone_position']['zone']['warehouse_id'],
              // TODO: Agregar enum a la action
              'action' => 'output',
              'concept' => 'relocate'
            ];
            array_push($datatransition, $stockMovement);

            StockMovement::create($stockMovement);
          }


          // Borra los registros del stock
          $stockDeleted = Stock::where('code14_id', $code14find['id'])
            ->where('zone_position_id', $findposition['id'])->delete();
          // Habilita la posicion
          // $count = Stock::where('zone_position_id', $findposition['id'])->count();
          $stockByPosition = StockFunctions::findStockByPosition($findposition['id']);
          $count = count($stockByPosition);
          if ($count <= 0) {
            //Cambiamos el estado de la posiciÃ³n para indicar que se encuentra libre
            //TODO : Se comenta mientras
            $findposition->concept_id = NULL;
            $findposition->save();
          }
        } else {
          return $this->response->error('storage_pallet_stock_not_found', 404);
        }
        $dataRes =  $datatransition;
      });
    } else if ($packagingType == PackagingType::Producto) {

      $product = Product::where('ean', $codeInput)->first();

      if (!isset($product)) {
        return $this->response->error('storage_pallet_product_no_found', 404);
      }
      DB::transaction(function () use ($product, $positionCode, $packagingProduct, &$dataRes, $session_user_id, $username, $scheduleId, $codeInput, $produc_Id, $is_secondary, $code14, $companyId) {

        //Buscamos la posiciÃ³n de origen
        $findposition = ZonePosition::where('code', $positionCode)
          ->whereHas('zone.warehouse.distribution_center', function ($q) {
            $q->where('company_id', $companyId);
          })
          ->first();

        if (empty($findposition)) {
          return $this->response->error('storage_pallet_product_no_found', 404);
        }

        $code14find = EanCode14::where('code14', $packagingProduct)->first();
        if (empty($code14find)) {
          return $this->response->error('storage_packaging_product_no_found', 404);
        }
        $findStock = Stock::with(
          'product',
          'zone_position.zone',
          'ean128',
          'ean14',
          'ean13'
        )->where('product_id', $product['id'])
          ->where('code14_id', $code14find['id'])
          ->where('quanty', '>', 0)
          ->where('zone_position_id', $findposition['id'])
          ->first();

        $datatransition = [];
        if (!empty($findStock)) {

          $findStock->quanty -= 1;


          // Inserta los registros del stock a la tabla de transicion
          $objTransition = [
            'product_id' => $findStock['product_id'],
            'zone_position_id' => $findStock['zone_position_id'],
            // 'code128_id'=>$findStock['code128_id'],
            // 'code14_id'=>$findStock['code14_id'],
            'quanty' => 1,
            // TODO: Agregar enum a la action
            'action' => 'output',
            'concept' => 'relocate',
            'warehouse_id' => $findStock['zone_position']['zone']['warehouse_id'],
            'user_id' => $session_user_id,
          ];

          // Crea el registro del movimiento
          $stockMovement = [
            'product_id' => $findStock['product_id'],
            'product_reference' => $findStock['product']['reference'],
            'product_ean' => $findStock['product']['ean'],
            'product_quanty' => 1,
            'zone_position_code' => $findStock['zone_position']['code'],
            'code128' => $findStock['ean128']['code128'],
            'code14' => $findStock['ean14']['code14'],
            'username' => $username,
            'warehouse_id' => $findStock['zone_position']['zone']['warehouse_id'],
            // TODO: Agregar enum a la action
            'action' => 'output',
            'concept' => 'relocate'
          ];

          StockMovement::create($stockMovement);
          // //StockTransition::create($objTransition);
          // if(isset($scheduleId))
          // {
          //
          //     $find13 = Stock::whereIn('product_id', $produc_Id)
          //               ->where('code14_id', $value['code14_id'])
          //               ->whereHas('zone_position.zone', function ($q) {
          //                 $q->where('is_secondary', false);
          //               })
          //               ->get();
          //
          //     if(count($find13))
          //     {
          $StockTransition =  StockTransition::create($objTransition);
          //     }
          //     else
          //     {
          //       return $this->response->error('storage_pallet_13_source_no_found', 404);
          //     }
          // }
          // else
          // {
          //   $StockTransition =  StockTransition::create($objTransition);
          // }

          // if(isset($scheduleId)) {
          //   $ScheduleTransition = [
          //     'transition_id' => $StockTransition->id,
          //     'schedule_id' => $scheduleId
          //   ];
          //
          //   $sheduleTransition=  ScheduleTransition::create($ScheduleTransition);
          // }


          // if ($findStock->quanty == 0) {
          //   // TODO : Borrar registro de la caja si se acaban los productos
          //   $findStock->delete();
          // }else{
          //   $findStock->save();
          // }

          $stockByPosition = StockFunctions::findStockByPosition($findposition['id']);
          $count = count($stockByPosition);
          if ($count <= 0) {
            //Cambiamos el estado de la posiciÃ³n para indicar que se encuentra libre
            //TODO : Se comenta mientras
            $findposition->concept_id = NULL;
            $findposition->save();
          }
        } else {
          return $this->response->error('storage_pallet_product_no_found', 404);
        }

        $dataRes =  $findStock;
      });
    }



    // return $dataRes;
    return $this->response->noContent();
  }
  public function relocateStored(Request $request)
  {
    $data = $request->all();

    $companyId = $data['company_id'];
    $positionCode = array_key_exists('position', $data) ? $data['position'] : NULL;
    $positionsCode = array_key_exists('positions', $data) ? $data['positions'] : NULL;
    $packagingType = array_key_exists('packaging_type', $data) ? $data['packaging_type'] : NULL;
    $codeInput = array_key_exists('codeunity', $data) ? $data['codeunity'] : NULL;
    $scheduleId = array_key_exists('schedule_id', $data) ? $data['schedule_id'] : NULL;
    $produc_Id = array_key_exists('product_id', $data) ? $data['product_id'] : NULL;
    $code14 = array_key_exists('code14', $data) ? $data['code14'] : NULL;
    $warehouseId = array_key_exists('warehouse_id', $data) ? $data['warehouse_id'] : NULL;

    $session_user_id = array_key_exists('session_user_id', $data) ? $data['session_user_id'] : NULL;

    $username = '';
    if (!empty($session_user_id)) {
      $user = User::find($session_user_id);
      if (!empty($user)) {
        $username = $user->username;
      }
    }

    $zonepconcept = ZoneConcept::where('is_storage', true)->where('active', true)->first();

    if (!isset($zonepconcept)) {
      return $this->response->error('storage_pallet_zone_concept_no_found', 404);
    }
    if ($packagingType == PackagingType::Logistica) {
      $findcode128 = EanCode128::where('code128', $codeInput)->first();
      if (!isset($findcode128)) {
        return $this->response->error('storage_pallet_code128_no_found', 404);
      }
      DB::transaction(function () use ($findcode128, $positionsCode, $zonepconcept, $username, $produc_Id, $scheduleId, $code14, $warehouseId, $codeInput, $companyId) {



        //Get the container feaures
        $containerId = $findcode128->container_id;
        $cFeatures = ContainerFeature::where('container_id', $containerId)->get();
        //Compare the Capacidad (kg) againts $weight
        $settingsObj = new Settings($companyId);
        $fCapacity = $settingsObj->get(SettingsKey::FEATURE_CAPACITY);
        $hCapacity = $settingsObj->get(SettingsKey::FEATURE_HEIGHT);


        //Declarations for the model MergedPositions
        $fullCode = '';
        $minId = 0;
        $maxId = 0;



        //Check if the position are multiple
        $totalPositions = count($positionsCode);


        foreach ($positionsCode as $pos) {
          $weight = $findcode128->weight / $totalPositions;
          $height = $findcode128->height;
          //Fabian marin
          //Validar el peso del pallet contra el de la posicion
          try {
            $positionHelper = ZonePosition::where('code', $pos['codePosition'])->whereHas('zone_position_features', function ($q) use ($fCapacity, $weight) {
              $q->whereHas('feature', function ($query) use ($fCapacity) {
                $query->where('name', $fCapacity);
              })->where('free_value', '>=', $weight);
            })->whereHas('zone_position_features', function ($q) use ($hCapacity, $height) {
              $q->whereHas('feature', function ($query) use ($hCapacity) {
                $query->where('name', $hCapacity);
              })->where('free_value', '>=', $height);
            })->firstOrFail();
          } catch (\Exception $e) {
            //Exceed the weight
            return $this->response->error('storage_pallet_position_weight', 404);
          }

          $posHelper = ZonePosition::with('zone_position_features.feature')->where('code', $pos['codePosition'])->first();

          //For each container feature reduce the position feature
          // foreach ($cFeatures as $feature) {
          // $value = $feature->value;
          foreach ($posHelper->zone_position_features as $fPos) {
            //Decrement the capacity
            if ($fPos->feature->name == $fCapacity) {
              $fPos->decrement('free_value', $weight);
              // break;
            } else if ($fPos->feature->name == $hCapacity) {
              $fPos->decrement('free_value', $height);
              // break;
            }

            // //Eval the code
            // if($feature->feature_id == $fPos->feature_id) {
            //   $theCode = '(' .$value. ' ' . $fPos->comparation . ' ' . $fPos->value .')';
            //   eval("\$match = ".$theCode.";");
            //
            //   //If match then reduce
            //   if($match) {
            //     $fPos->decrement('free_value', $value);
            //   }
            // }
          }
          // }

          $code = $pos['codePosition'];
          $fullCode .= $code;
          $findposition = ZonePosition::where('code', $code)
            ->whereHas('zone.warehouse.distribution_center', function ($q) {
              $q->where('company_id', $companyId);
            })
            ->first();

          $posId = $findposition->id;

          //Check from and to positions (range)
          if ($minId == 0) {
            $minId = $posId;
            $maxId = $posId;
          } else {
            if ($posId < $minId) {
              $minId = $posId;
            } else if ($posId > $maxId) {
              $maxId = $posId;
            }
          }
        }

        if ($totalPositions > 1) {
          //Store on the merged positions

          //Validate it positions are next to each other
          if ($maxId != ($minId + $totalPositions) - 1) {
            $this->response->error('storage_position_merged_error', 404);
          }

          //Set the merged position
          $mergeCode = [
            'code' => $fullCode,
            'from_position_id' => $minId,
            'to_position_id' => $maxId,
            'code128' => $findcode128->id
          ];

          //Create the merged position
          MergedPosition::create($mergeCode);

          //Cambiamos el estado de la posiciÃ³n para indicar que se encuentra ocupada
          // $findposition->concept_id = $zonepconcept['id'];
          ZonePosition::whereBetween('id', [$minId, $maxId]);
          // ->update(['concept_id' => $zonepconcept['id']])
        }

        //Buscamos la posiciÃ³n en la q se quiere almacenar
        if ($minId > 0) {
          $findposition = ZonePosition::find($minId);
        } else {
          $findposition = ZonePosition::with('zone')->where('code', $positionsCode[0]['codePosition'])->first();
        }


        if (empty($findposition)) {
          return $this->response->error('storage_pallet_position_source_no_found', 404);
        }
        //Validamos que la posiciÃ³n se encuentre disponible para almacenar
        if (!$findposition['active']) {
          return $this->response->error('storage_pallet_position_unavailable', 404);
        }


        $findTransition = StockTransition::with(
          'product',
          'zone_position.zone',
          'ean128',
          'ean14',
          'ean13'
        )->where('code128_id', $findcode128['id'])->get()->toArray();

        if (!empty($findTransition)) {
          foreach ($findTransition as $key => $value) {

            $objStock = [
              'product_id' => $value['product_id'],
              'zone_position_id' => $findposition['id'],
              'code128_id' => $value['code128_id'],
              'code14_id' => $value['code14_id'],
              'quanty' => $value['quanty'],
              'active' => 1,
            ];
            if (isset($scheduleId)) {
              $find13 = Stock::whereIn('product_id', $produc_Id)
                ->where('code128_id', $value['code128_id'])
                ->whereHas('zone_position.zone', function ($q) {
                  $q->where('is_secondary', false);
                })
                ->get();
              if (!count($find13)) {
                return $this->response->error('storage_pallet_13_source_no_found', 404);
              } else {
                Stock::create($objStock);
              }
            } else {
              Stock::create($objStock);
            }
            // Crea el registro del movimiento
            $stockMovement = [
              'product_id' => $value['product_id'],
              'product_reference' => $value['product']['reference'],
              'product_ean' => $value['product']['ean'],
              'product_quanty' => $value['quanty'],
              'zone_position_code' => $findposition['code'],
              'code128' => $value['ean128']['code128'],
              'code14' => $value['ean14']['code14'],
              'username' => $username,
              'warehouse_id' => $value['zone_position']['zone']['warehouse_id'],
              // TODO: Agregar enum a la action
              'action' => 'income',
              'concept' => 'relocate'
            ];

            StockMovement::create($stockMovement);
          }
          // $findposition->concept_id = $zonepconcept['id'];
          $findposition->save();

          $transitionDeleted = StockTransition::with(
            'product',
            'zone_position.zone',
            'ean128',
            'ean14',
            'ean13'
          )->where('code128_id', $findcode128['id'])->delete();
        } else {
          return $this->response->error('storage_pallet_code128_no_found', 404);
        }
      });
    } else if ($packagingType == PackagingType::Empaque) {
      // BUscar y validar la caja contra la tabla de transicion
      $code14find = EanCode14::where('code14', $codeInput)->first();
      if (!isset($code14find)) {
        return $this->response->error('storage_pallet_code14_no_found', 404);
      }
      $dataRes = '';
      DB::transaction(function () use ($code14find, $positionCode, &$dataRes, $zonepconcept, $username, $produc_Id, $scheduleId, $code14, $warehouseId, $codeInput, $companyId) {

        //Buscamos la posiciÃ³n de origen
        $findposition = ZonePosition::where('code', $positionCode)
          ->whereHas('zone.warehouse.distribution_center', function ($q) {
            $q->where('company_id', $companyId);
          })
          ->first();

        if (empty($findposition)) {
          return $this->response->error('storage_pallet_position_target_no_found', 404);
        }
        //Validamos que la posiciÃ³n se encuentre disponible para almacenar
        if (!$findposition['active']) {
          return $this->response->error('storage_pallet_position_unavailable', 404);
        }
        $findTransition = StockTransition::with(
          'product',
          'zone_position.zone',
          'ean128',
          'ean14',
          'ean13'
        )->where('code14_id', $code14find['id'])->get()->toArray();

        if (!empty($findTransition)) {
          $isMine = false;
          $created = false;
          foreach ($findTransition as $key => $value) {

            // Insertar la informacion en Stock con la nueva informacion de posicion

            $objStock = [
              'product_id' => $value['product_id'],
              'zone_position_id' => $findposition->id,
              'code128_id' => $value['code128_id'],
              'code14_id' => $value['code14_id'],
              'quanty' => $value['quanty'],
              'active' => 1,
            ];

            if (isset($scheduleId)) {


              if (in_array($value['product_id'], $produc_Id)) {
                $objStock['code14_id'] = null;

                $picking = Stock::where('zone_position_id', $findposition->id)->where('product_id', $value['product_id'])->first();

                if (!empty($picking)) {
                  $picking->increment('quanty', $value['quanty']);
                } else {
                  Stock::create($objStock);
                }
                // Crea el registro del movimiento
                $stockMovement = [
                  'product_id' => $value['product_id'],
                  'product_reference' => $value['product']['reference'],
                  'product_ean' => $value['product']['ean'],
                  'product_quanty' => $value['quanty'],
                  'zone_position_code' => $findposition['code'],
                  'code128' => $value['ean128']['code128'],
                  'code14' => $value['ean14']['code14'],
                  'username' => $username,
                  'warehouse_id' => $value['warehouse_id'],
                  // TODO: Agregar enum a la action
                  'action' => 'income',
                  'concept' => 'relocate'
                ];

                StockMovement::create($stockMovement);

                $transitionDeleted = StockTransition::with(
                  'product',
                  'zone_position.zone',
                  'ean128',
                  'ean14',
                  'ean13'
                )->where('code14_id', $code14find['id'])
                  ->where('product_id', $value['product_id'])
                  ->delete();

                $isMine = true;
              } else {
                if (!$created) {
                  $created = true;
                  $settingsObj = new Settings();
                  $chargeUserName = $settingsObj->get('stock_group');
                  $user = User::whereHas('person.group', function ($q) use ($chargeUserName) {
                    $q->where('name', $chargeUserName);
                  })->whereHas('person', function ($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                  })->first();

                  if (empty($user)) {
                    return $this->response->error('user_not_found', 404);
                  }


                  $taskSchedules = [
                    'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
                    'name' => 'Almacenar referencias sobrantes reabastecimiento picking:' . $codeInput,
                    'schedule_type' => ScheduleType::Restock,
                    'schedule_action' => ScheduleAction::ToStock,
                    'status' => ScheduleStatus::Process,
                    'user_id' => $user->id
                  ];

                  $schedule = Schedule::create($taskSchedules);
                }
              }
            } else {
              Stock::create($objStock);

              // Crea el registro del movimiento
              $stockMovement = [
                'product_id' => $value['product_id'],
                'product_reference' => $value['product']['reference'],
                'product_ean' => $value['product']['ean'],
                'product_quanty' => $value['quanty'],
                'zone_position_code' => $findposition['code'],
                'code128' => $value['ean128']['code128'],
                'code14' => $value['ean14']['code14'],
                'username' => $username,
                'warehouse_id' => $value['warehouse_id'],
                // TODO: Agregar enum a la action
                'action' => 'income',
                'concept' => 'relocate'
              ];

              StockMovement::create($stockMovement);

              $transitionDeleted = StockTransition::with(
                'product',
                'zone_position.zone',
                'ean128',
                'ean14',
                'ean13'
              )->where('code14_id', $code14find['id'])->delete();
            }
          }

          if (isset($scheduleId)) {
            if (!$isMine) {
              return $this->response->error('storage_pallet_13_source_no_found', 404);
            }
          }

          // $findposition->concept_id = $zonepconcept['id'];
          $findposition->save();
        } else {
          return $this->response->error('storage_pallet_code14_no_found', 404);
        }
        $dataRes =  $findTransition;
      });
    } else if ($packagingType == PackagingType::Producto) {
      $product = Product::where('ean', $codeInput)->first();
      if (!isset($product)) {
        return $this->response->error('storage_pallet_product_no_found', 404);
      }
      DB::transaction(function () use ($product, $positionCode, $zonepconcept, $username, $produc_Id, $scheduleId, $code14, $warehouseId, $companyId) {

        //Buscamos la posiciÃ³n de origen
        $findposition = ZonePosition::where('code', $positionCode)
          ->whereHas('zone.warehouse.distribution_center', function ($q) {
            $q->where('company_id', $companyId);
          })
          ->first();

        if (empty($findposition)) {
          return $this->response->error('storage_pallet_position_target_no_found', 404);
        }
        //Validamos que la posiciÃ³n se encuentre disponible para almacenar
        if (!$findposition['active']) {
          return $this->response->error('storage_pallet_position_unavailable', 404);
        }

        $findTransition = StockTransition::with(
          'product',
          'zone_position.zone',
          'ean128',
          'ean14',
          'ean13'
        )->where('product_id', $product['id'])->first();

        $datatransition = [];
        if (!empty($findTransition)) {


          $objStock = [
            'product_id' => $value['product_id'],
            'zone_position_id' => $findposition['id'],
            'code128_id' => $value['code128_id'],
            'code14_id' => $value['code14_id'],
            'quanty' => $value['quanty'],
            'active' => 1,
          ];


          // Crea el registro del movimiento
          $stockMovement = [
            'product_id' => $findTransition['product_id'],
            'product_reference' => $findTransition['product']['reference'],
            'product_ean' => $findTransition['product']['ean'],
            'product_quanty' => $findTransition['quanty'],
            'zone_position_code' => $findTransition['zone_position']['code'],
            'code128' => $findTransition['ean128']['code128'],
            'code14' => $findTransition['ean14']['code14'],
            'username' => $username,
            'warehouse_id' => $findTransition['warehouse_id'],
            // TODO: Agregar enum a la action
            'action' => 'income',
            'concept' => 'relocate'
          ];

          StockMovement::create($stockMovement);



          if (isset($scheduleId)) {
            $find13 = Stock::whereIn('product_id', $produc_Id)
              ->where('code128_id', $value['code128_id'])
              ->whereHas('zone_position.zone', function ($q) {
                $q->where('is_secondary', false);
              })
              ->get();
            if (!count($find13)) {
              return $this->response->error('storage_pallet_13_source_no_found', 404);
            } else {
              Stock::create($objStock);
            }
          } else {
            Stock::create($objStock);
          }

          // $findposition->concept_id = $zonepconcept['id'];
          $findposition->save();

          $findTransition->delete();
        } else {
          return $this->response->error('storage_pallet_product_no_found', 404);
        }
      });
    }

    return $this->response->noContent();
  }




  public function transformRequest(Request $request)
  {
    $data = $request->all();
    $product    = $data['product'];
    $reference  = $product['reference'];
    $start_date = $data['start_date'];
    $end_date   = $data['end_date'];

    $unjoin   = array_key_exists('unjoin', $data) ? true : false;


    /*
                    $findStock = Stock::with(
                    'product','zone_position.zone.warehouse'
                    ,'ean128','ean14','ean13'
                  );

                  if(isset($reference)) {
                  $findStock = $findStock->whereHas('product', function ($q) use ($reference) {
                  $q->where('reference', $reference);
                });
              }


              $findStock  = $findStock->get()->toArray();
              if (empty($findStock)) {
              return $this->response->error('storage_pallet_product_no_found', 404);
            }


            // $findCode14 = EanCode14::with('stock','product','documentDetail.document.scheduleDocument.schedule.schedule_receipt');
            // $findCode14 = $findCode14->whereHas('product', function ($q) use ($reference) {
            //   $q->where('reference', $reference);
            // });

            // $findCode14  = $findCode14->get()->toArray();

            // Despues de consultar los elementos existentes con la referencia buscada se identifica cada una de las bodegas implicadas
            $warehouses = array();

            foreach ($findStock as $keyStock => $valueStock) {
            $warehouse_id = $valueStock['zone_position']['zone']['warehouse_id'];
            $warehouseObj = $valueStock['zone_position']['zone']['warehouse'];
            if (!array_key_exists($warehouse_id, $warehouses)) {
            $warehouseObj['stockInfo'][] = $valueStock;
            $warehouses[$warehouse_id] = $warehouseObj;
          }else{
          $warehouses[$warehouse_id]['stockInfo'][] = $valueStock;
        }
      }

      if (empty($warehouses)) {
      return $this->response->error('storage_pallet_warehouse_unit_no_found', 404);
    }




    // Obtenemos el parametro de configuracion del cargo lider de la bodega
    $settingsObj = new Settings();


    // TODO : usar un ennum
    $chargeUserName = $settingsObj->get('leader_charge');

    if (empty($chargeUserName)) {
    return $this->response->error('not_found_charge_warehouse', 404);
  }

  $users = array();

  foreach ($warehouses as $keyWare => $warehouse) {
  // INICIO - consulta para obtener el jefe de la bodega para realizar la notificacion y asignacion de la tarea

  // Capturamos la bodega de la cual encontraremos su lider
  $warehouse_id = $warehouse['id'];
  // Consultamos el cargo configurado como lider de bodega y obtenemos los usuarios relacionados al dicho cargo
  $chargeUser = Charge::with(
  array(
  'personal'=>function ($q) use ($warehouse_id){
  // hacemos el filtro para solo trauer los lideres de la bodga seleccionada
  $q->where('warehouse_id',$warehouse_id);
  // Y que dicha persona tenga un usuario del sistema relacionado ya que de alli sacamos el correo para hacer la notificacion para posteriormente realizar la tarea
  $q->has('user');
},
'personal.user'
)
)->where('name',$chargeUserName)->first();
if (empty($chargeUser)) {
return $this->response->error('no_found_leader_warehouse', 404);
}



// Obtenemos la primera persona que cumpla con las condicinoes de tener el cargo lider, pertencer a la bodega seleccionada y que tenga un usuario relacionado
$user = $chargeUser->personal->first();
if (empty($user)) {
return $this->response->error('no_found_leader_warehouse', 404);
}

$user['stockInfo'] = $warehouse['stockInfo'];
array_push($users, $user);
// FIN
}
*/


    $users = SchedulesFunctions::getLeadersWarehouseFromReference($reference, $this);

    if (!empty($users)) {
      if ($unjoin) {
        $taskName = "Desunir la referencia " . $reference;
        $typeTransform = TypeTransform::Unjoin;
      } else {
        $taskName = "Transformar la referencia " . $reference;
        $typeTransform = TypeTransform::Transform;
      }


      foreach ($users as $leader) {
        $user = $leader['user'];
        $taskSchedules = [
          'start_date' => $start_date,
          'end_date' => $end_date,
          'name' => $taskName,
          'schedule_type' => ScheduleType::Transform,
          'schedule_action' => ScheduleAction::Assign,
          'status' => ScheduleStatus::Process,
          'notified' => false,
          'user_id' => $user['id']
        ];



        //Send the notification
        $email = $user['email'];
        $name = $leader['name'] . ' ' . $leader['last_name'];
        //TODO: uncomment for production
        // Mail::queue('emails.task', ['task' => $taskName], function ($mail) use ($email, $name) {
        //     $mail->from('alertas@wms.com', 'Wms alertas');
        //     $mail->to($email, $name)->subject('Nueva Tarea');
        // });


        $schedule = Schedule::create($taskSchedules);
        $scheduleTransform = $schedule->schedule_transform()->create(array(
          'warehouse_id' => $leader['warehouse_id'],
          'type_transform' => $typeTransform,
        ));

        $stockDetails = array();
        foreach ($leader['stockInfo'] as $stockInfo) {
          $stockDetail = [
            'product_id' => $stockInfo['product_id'],
            'position_id' => $stockInfo['zone_position_id'],
            'code128_id' => $stockInfo['code128_id'],
            'code14_id' => $stockInfo['code14_id'],
            'quanty' => $stockInfo['quanty'],
            'detail_type' => TransformDetailType::Storage,
            'detail_status' => TransformDetailStatus::Pendding
          ];
          $stockDetails[] = $stockDetail;
        }
        $scheduleTransform->scheduleTransformDetail()->createMany($stockDetails);
      }
    }

    return $users;
  }
  public function transformRemove_old(Request $request)
  {
    $data = $request->all();
    $positionCode = array_key_exists('position', $data) ? $data['position'] : NULL;
    $packagingType = array_key_exists('packaging_type', $data) ? $data['packaging_type'] : NULL;
    $codeInput = array_key_exists('codeunity', $data) ? $data['codeunity'] : NULL;
    $companyId = $data['company_id'];

    if ($packagingType == PackagingType::Empaque) {
      $code14find = EanCode14::where('code14', $codeInput)->first();
      if (!isset($code14find)) {
        return $this->response->error('storage_pallet_code14_no_found', 404);
      }
      DB::transaction(function () use ($code14find, $positionCode, $companyId) {

        //Buscamos la posiciÃ³n de origen
        $findposition = ZonePosition::where('code', $positionCode)
          ->whereHas('zone.warehouse.distribution_center', function ($q) {
            $q->where('company_id', $companyId);
          })
          ->first();

        if (empty($findposition)) {
          return $this->response->error('storage_pallet_code14_no_found', 404);
        }

        $findStock = Stock::with(
          'product',
          'zone_position.zone',
          'ean128',
          'ean14',
          'ean13'
        )->where('code14_id', $code14find['id'])
          ->where('zone_position_id', $findposition['id'])->get()->toArray();

        $datatransition = [];
        if (!empty($findStock)) {

          foreach ($findStock as $key => $value) {

            // Inserta los registros del stock a la tabla de transicion
            $objTransition = [
              'product_id' => $value['product_id'],
              'zone_position_id' => $value['zone_position_id'],
              'code128_id' => $value['code128_id'],
              'code14_id' => $value['code14_id'],
              'quanty' => $value['quanty'],
              // TODO: Agregar enum a la action
              'action' => 'output',
              'concept' => 'transform',
              'warehouse_id' => $value['zone_position']['zone']['warehouse_id'],
            ];

            StockTransition::create($objTransition);


            // Crea el registro del movimiento
            $stockMovement = [
              'product_id' => $value['product_id'],
              'product_reference' => $value['product']['reference'],
              'product_ean' => $value['product']['ean'],
              'product_quanty' => $value['quanty'],
              'zone_position_code' => $value['zone_position']['code'],
              'code128' => $value['ean128']['code128'],
              'code14' => $value['ean14']['code14'],
              'username' => 'admin',
              'warehouse_id' => $value['zone_position']['zone']['warehouse_id'],
              // TODO: Agregar enum a la action
              'action' => 'output',
              'concept' => 'transform'
            ];
            array_push($datatransition, $stockMovement);

            StockMovement::create($stockMovement);
          }


          // Borra los registros del stock
          $stockDeleted = Stock::where('code14_id', $code14find['id'])
            ->where('zone_position_id', $findposition['id'])->delete();

          $count = Stock::where('zone_position_id', $findposition['id'])->count();
          if ($count <= 0) {
            //Cambiamos el estado de la posiciÃ³n para indicar que se encuentra libre
            //TODO : Se comenta mientras
            // Habilita la posicion
            $findposition->concept_id = NULL;
            $findposition->save();
          }
        } else {
          return $this->response->error('storage_pallet_code14_no_found', 404);
        }
      });
    }

    return $this->response->noContent();
  }
  public function transformRemove(Request $request)
  {
    $dataReq = $request->all();
    $data = $dataReq['parentDetails'];
    $childtaskId = $dataReq['taskId'];
    $responseData = [];

    $session_user_id = array_key_exists('session_user_id', $dataReq) ? $dataReq['session_user_id'] : NULL;

    $username = $this->getUsernameById($session_user_id);

    if (!empty($data)) {
      DB::transaction(function () use ($data, &$responseData, $childtaskId, $username) {
        foreach ($data as $detailtransform) {
          // Buscamos la caja en el inventario si es de tipo almacenamiento
          $findposition = ZonePosition::find($detailtransform['position_id']);

          if (empty($findposition)) {
            return $this->response->error('storage_pallet_code14_no_found', 404);
          }

          $findStock = Stock::with(
            'product',
            'zone_position.zone',
            'ean128',
            'ean14',
            'ean13'
          )->where('code14_id', $detailtransform['code14_id'])
            ->where('zone_position_id', $findposition['id'])->get()->toArray();

          // Insertamos la informacion en tabla de transicion
          if (!empty($findStock)) {

            foreach ($findStock as $key => $value) {

              // Inserta los registros del stock a la tabla de transicion
              $objTransition = [
                'product_id' => $value['product_id'],
                'zone_position_id' => $value['zone_position_id'],
                'code128_id' => $value['code128_id'],
                'code14_id' => $value['code14_id'],
                'quanty' => $value['quanty'],
                // TODO: Agregar enum a la action
                'action' => 'output',
                'concept' => 'transform',
                'warehouse_id' => $value['zone_position']['zone']['warehouse_id'],
              ];

              StockTransition::create($objTransition);


              // Crea el registro del movimiento
              $stockMovement = [
                'product_id' => $value['product_id'],
                'product_reference' => $value['product']['reference'],
                'product_ean' => $value['product']['ean'],
                'product_quanty' => $value['quanty'],
                'zone_position_code' => $value['zone_position']['code'],
                'code128' => $value['ean128']['code128'],
                'code14' => $value['ean14']['code14'],
                'username' => $username,
                'warehouse_id' => $value['zone_position']['zone']['warehouse_id'],
                // TODO: Agregar enum a la action
                'action' => 'output',
                'concept' => 'transform'
              ];
              // array_push($responseData, $stockMovement);

              // Generamos registro de movimiento de salida
              StockMovement::create($stockMovement);
            }


            // Borra los registros del stock
            $stockDeleted = Stock::where('code14_id', $detailtransform['code14_id'])
              ->where('zone_position_id', $findposition['id'])->delete();

            $count = Stock::where('zone_position_id', $findposition['id'])->count();
            if ($count <= 0) {
              //Cambiamos el estado de la posiciÃ³n para indicar que se encuentra libre
              //TODO : Se comenta mientras
              // Habilita la posicion
              $findposition->concept_id = NULL;
              $findposition->save();
            }
          } else {
            return $this->response->error('storage_pallet_code14_no_found', 404);
          }


          // Actualizamos el detalle de la tarea
          $datailModel = ScheduleTransformDetail::find($detailtransform['id']);
          $datailModel->detail_status = $detailtransform['detail_status'];
          $datailModel->save();

          array_push($responseData, $datailModel);
        }
      });
    }
    return $responseData;
  }

  public function transformStored_old(Request $request)
  {
    $data = $request->all();
    $listCodes = array_key_exists('listCodes', $data) ? $data['listCodes'] : NULL;
    $ean14Transform = array_key_exists('objStorage', $data) ? $data['objStorage'] : NULL;
    $html = '';
    DB::transaction(function () use ($listCodes, $ean14Transform, &$html) {
      $code14Parent = EanCode14::findOrFail($ean14Transform['code14_id']);
      $structurecodes = Codes::GetStructureCode(PackagingType::Empaque);
      $breackCount = 0;
      $i = 0;
      $count = count($listCodes);
      foreach ($listCodes as $key => $code) {
        $i++;
        $breackCount++;
        $newstructurecode14 = '7704121' . $code['container']['id'];
        //Recorremos la estructura y generamos la estrucutra de los cÃ³digos IA
        foreach ($structurecodes as $structure) {
          $ia_code = $structure['ia_code'];
          $code_ia = $ia_code['code_ia'];
          //Validamos si el cÃ³digo IA debe tomar datos de alguna tabla
          if ((!empty($ia_code['table']) && empty($ia_code['field'])) || (!empty($ia_code['field']) && empty($ia_code['table']))) {
            return $this->response->error('No se pueden generar los cÃ³digos, porque el cÃ³digo GTIN' . $code_ia . 'estÃ¡ mal configurado', 404);
          } else if (!empty($ia_code['table']) && !empty($ia_code['field'])) {
            $table = $ia_code['table'];
            $field = $ia_code['field'];

            $results = DB::select(DB::raw('SELECT ' . $field . ' FROM ' . $table . ' WHERE id = ' . $document_detail_id . ''));
            if (is_null($results)) {
              return $this->response->error('No se pueden generar los cÃ³digos, porque el cÃ³digo GTIN' . $code_ia . 'estÃ¡ mal configurado', 404);
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
          'code13' => $code['ean'],
          // 'document_detail_id'=>$document_detail_id,
          'container_id' => $code['container']['id'],
          'quanty' => $code['quanty'],
          'code_parent_id' => $code14Parent->id
        ];
        $newsavedcode14 = $newstructurecode14;
        $savedcode14 = EanCode14::create($new14);
        $newsavedcode14 = $savedcode14['code14'] . $savedcode14['id'];
        $savedcode14->code14 = $newsavedcode14;
        $savedcode14->save();


        $html .= '<div class="row" style="border: solid 1px black;margin:10px;">';
        $html .= '<div style="border:solid 1px black;min-height:30px" class="col-xs-12 text-center cii-title">C.I IBLU S.A.S </div>';
        $html .= '<div style="border:solid 1px black;" class="col-xs-12"><barcode type="code128b" string="' . $newsavedcode14 . '"options="options"></barcode></div>';
        $html .= '<div style="border:solid 1px black;" class="col-xs-12"> ';
        $html .= '<div class="row" style="border-bottom: 1px solid black;font-weight: bold;"> <div class="col-xs-6" >Cod. Barras</div><div class="col-xs-2 text-right" style="border-left: 1px solid black;border-right: 1px solid black;font-weight: bold;">Cantidad</div><div class="col-xs-2 text-center" style="border-right: 1px solid black;font-weight: bold;">Referencia</div><div class="col-xs-2 text-center"style="font-weight: bold;">DescripciÃ³n</div></div>';
        $html .= '<div class="row barcode-row">  <div class="col-xs-6" style="padding: 10px;"><barcode type="code128b" string="' . $code['ean'] . '"options="options"></barcode></div><div class="col-xs-2 text-right text-code" style="border-left: 1px solid black;border-right: 1px solid black;">' . $code['quanty'] . '</div><div class="col-xs-2 text-center text-code" style="border-right: 1px solid black;">' . $code['reference'] . '</div><div class="col-xs-2 text-center text-code ">' . $code['description'] . '</div></div>';
        $html .= '</div>';
        $html .= '</div>';
        // Imprime un salto de pagina cuando se han impreso 3 codigos y no es el utimo codigo de la ultima referencia
        if ($breackCount == 3 && ($i < (count($count) - 1))) {
          $html .= "<div class='breack-page'></div>";
          $breackCount = 0;
        }
      }

      $transition = StockTransition::where('code14_id', $ean14Transform['code14_id'])->delete();
    });


    return $html;
    // return $this->response->noContent();
  }

  public function transformStored(Request $request)
  {
    $data = $request->all();
    $listRerences = array_key_exists('listRerences', $data) ? $data['listRerences'] : NULL;
    $codeParent = array_key_exists('parent', $data) ? $data['parent'] : NULL;


    $code14Parent = EanCode14::findOrFail($codeParent['code14_id']);
    $html = '';
    $structurecodes = Codes::GetStructureCode(PackagingType::Empaque);
    $i = 0;
    $breackCount = 0;
    $count = count($listRerences);

    $datailModel = ScheduleTransformDetail::find($codeParent['id']);

    foreach ($listRerences as $key => $code) {
      $i++;
      $breackCount++;
      $newstructurecode14 = '7704121' . $code['container']['id'];
      //Recorremos la estructura y generamos la estrucutra de los cÃ³digos IA
      foreach ($structurecodes as $structure) {
        $ia_code = $structure['ia_code'];
        $code_ia = $ia_code['code_ia'];

        //Validamos si el cÃ³digo IA debe tomar datos de alguna tabla
        if ((!empty($ia_code['table']) && empty($ia_code['field'])) || (!empty($ia_code['field']) && empty($ia_code['table']))) {
          return $this->response->error('No se pueden generar los cÃ³digos, porque el cÃ³digo GTIN' . $code_ia . 'estÃ¡ mal configurado', 404);
        } //Si Existe tabla y campo para el parametro se hace la consulta
        else if (!empty($ia_code['table']) && !empty($ia_code['field'])) {
          $table = $ia_code['table'];
          $field = $ia_code['field'];
          // TODO : falta definir como pbtener la clave del la tabla a o el campo a obtener
          $key   = 'ID_PARAMETRO';
          $results = DB::select(DB::raw('SELECT ' . $field . ' FROM ' . $table . ' WHERE id = ' . $key . ''));
          if (is_null($results)) {
            return $this->response->error('No se pueden generar los cÃ³digos, porque el cÃ³digo GTIN' . $code_ia . 'estÃ¡ mal configurado', 404);
          } else {
            $array = json_decode(json_encode($results[0]), True);

            $newstructurecode14 .= '(' . $code_ia . ')' . $array['number'];
          }
        } //SI no hay tabla se deja el valor por defecto planteado en la tabla
        else {
          $newstructurecode14 .= '(' . $ia_code['code_ia'] . ')';
        }
      }

      $new14 = [
        'code14' => $newstructurecode14,
        'code13' => $code['product']['ean'],
        // 'document_detail_id'=>$document_detail_id,
        'quanty' => $code['quanty'],
        'container_id' => $code['container']['id'],
        'code_parent_id' => $codeParent['code14_id'],
        'product_id' => $code['product']['id'],
      ];

      $savedcode14 = EanCode14::create($new14);
      $newsavedcode14 = $savedcode14['code14'] . $savedcode14['id'];
      $savedcode14->code14 = $newsavedcode14;
      $savedcode14->save();

      // TODO: Agregar enum en los estados y concentos de las transiciones
      $objTransition = [
        'product_id' => $code['product']['id'],
        // 'zone_position_id'
        // 'code128_id'
        'code14_id' => $savedcode14['id'],
        'quanty' => $code['quanty'],
        // TODO: Agregar enum a la action
        'action' => 'income',
        'concept' => 'transform',
        'warehouse_id' => $datailModel->scheduleTransform->warehouse_id,
      ];

      StockTransition::create($objTransition);
      /*
      */
      $html .= Codes::getEan14Html($savedcode14['id']);
      $html .= "<div class='breack-page'></div>";
    }

    $transition = StockTransition::where('code14_id', $codeParent['code14_id'])->delete();

    // Actualizamos el detalle de la tarea
    $datailModel->detail_status = $codeParent['detail_status'];
    $datailModel->save();

    return $html;
  }

  public function closeTransformAction($id)
  {
    //Conuslto la tarea/accion que se esta cerrando (Remover, Transformar, Almacenar)
    $schedule = Schedule::find($id);
    $response = [];
    // Se marca como cerrada
    $schedule->status = ScheduleStatus::Closed;
    // Si tiene tarea padre y se guardo correctamente se valida y se actualiza el padrre
    if (!empty($schedule->parent_schedule_id) && $schedule->save()) {
      // Se consulta el registro de estados de la tarea padre
      $transform = $schedule->parent_schedule->schedule_transform;
      if (!empty($transform->id)) {

        if ($schedule->schedule_action == ScheduleAction::Remove) {
          $transform->remove_status = true;
        }
        if ($schedule->schedule_action == ScheduleAction::Transform) {
          $transform->transform_status = true;
        }
        /**
         * Al momento que se genera el pallet, asumimos que ya se guardÃ³
         * porque se genera una tarea de almacenamiento con sugerencia.
         */
        if ($schedule->schedule_action == ScheduleAction::GeneratePallet) {
          $transform->store_status = true;
        }
        if ($transform->transform_status && $transform->remove_status && $transform->store_status) {

          $transform->status = true;
          $schedule->parent_schedule->status = ScheduleStatus::Closed;
          $schedule->parent_schedule->save();
        }

        $transform->save();
      }
      $response = $transform;
    }
    return $response;
  }

  public function transformResult(Request $request)
  {
    $data = $request->all();

    DB::transaction(function () use ($data) {
      $sessionUserId = $data['session_user_id'];
      $idTransform = $data['transform_id'];
      $idSchedule = $data['transform_taks_id'];
      $transformResult = $data['schedule_transform_result'];
      $transformed = $data['transformed'];

      $transformModel = ScheduleTransform::findOrFail($idTransform);
      $transformModel->scheduleTransformResult()->createMany($transformResult);

      // Quito la caja (EAN14) original de stock transiciÃ³n porque se va a convertir en unidades (EAN13) en transiciÃ³n.
      StockTransition::where('product_id', '=', $transformed['product_id'])
        ->where('code14_id', '=', $transformed['code14_id'])
        ->where('concept', '=', 'transform')
        ->delete();

      // Quito la caja (EAN14) del pallet (EAN128)
      Pallet::where('code14_id', '=', $transformed['code14_id'])->delete();

      // Chequeo que el pallet (EAN128) aÃºn tenga cajas (EAN14).
      $ean128 = EanCode128::has('ean14')
        ->where('id', '=', $transformed['code128_id']);

      // Si ya no tiene cajas (EAN14), lo borro.
      if (empty($ean128)) {
        EanCode128::findOrFail($transformed['code128_id'])->delete();
      }

      $transformedToInsert = [];

      foreach ($transformResult as $tResult) {
        // Busco si el product_id ya existe en transiciÃ³n bajo el concepto de "transform"
        $transformedOnTransition = StockTransition::where('concept', '=', 'transform')
          ->where('product_id', '=', $tResult['product_id'])
          ->get()
          ->first();

        // Si existe, le sumo el quanty
        if (!empty($transformedOnTransition)) {
          $transformedOnTransition->quanty += $tResult['quanty'];

          $transformedOnTransition->save();
        }
        // Sino, lo agrego.
        else {
          $newTransition = [
            'product_id' => $tResult['product_id'],
            'zone_position_id' => $transformed['zone_position_id'],
            'quanty' => $tResult['quanty'],
            // TODO: Agregar enum a la action
            'action' => 'output',
            'concept' => 'transform',
            'warehouse_id' => $transformed['warehouse_id'],
            'user_id' => $sessionUserId,
          ];

          array_push($transformedToInsert, $newTransition);
        }
      }

      // Si hay productos a insertar en transiciÃ³n, lo hago todo de una.
      if (!empty($transformedToInsert)) {
        StockTransition::insert($transformedToInsert);
      }
    });

    return $this->response->created();
  }

  public function transformResultPackaging(Request $request)
  {
    $data = $request->all();
    $response = NULL;

    DB::transaction(function () use ($data, &$response) {
      $newPackaging = $data['schedule_transform_result_packaging'];

      // Busco la referencia (esa que NO tiene EAN14) que estÃ¡ en transiciÃ³n para sustraerle la cantidad que se va a empacar
      $transformedOnTransition = StockTransition::where('concept', 'transform')
        ->where('product_id', $newPackaging['product_id'])
        ->where('code14_id', NULL)
        ->get()
        ->first();

      // Valido que la cantidad a sustraer exista.
      if ($transformedOnTransition->quanty < $newPackaging['quanty']) {
        return $this->response->error('alert_quanty_to_pack_exceeded', 500);
      }

      // Guardo estos datos para generar el nuevo cÃ³digo EAN14 en transiciÃ³n.
      $userId = $data['session_user_id'];
      $transformedZonePositionId = $transformedOnTransition->zone_position_id;

      // Si la cantidad a sustraer es igual a lo que hay, => borro la ref de transiciÃ³n.
      if ($transformedOnTransition->quanty == $newPackaging['quanty']) {
        $transformedOnTransition->delete();
      }
      // Si todavÃ­a quedan unidades, le quito la cantidad empacada.
      else {
        $transformedOnTransition->quanty -= $newPackaging['quanty'];
        $transformedOnTransition->save();
      }

      // Genero el nuevo empaque.
      $idTransform = $data['transform_id'];
      $transformModel = ScheduleTransform::findOrFail($idTransform);
      $newPackagingId = $transformModel->scheduleTransformResultPackaging()->create($newPackaging)->id;

      // Genero, y respondo el EAN14 de una vez. Esta funciÃ³n, tambiÃ©n, pone la caja en transiciÃ³n.
      $response =  $this->printCodePackaging($newPackagingId, $userId, $transformedZonePositionId);
    });

    return $response;
  }
  public function updateTransformResultPackaging(Request $request, $id)
  {
    $data = $request->all();

    $packaging = ScheduleTransformResultPackaging::findOrFail($id);

    $packaging->quanty = empty($data['quanty']) ? $packaging->quanty : $data['quanty'];

    if ($packaging->ean14_id > 0) {
      // Actualiza el quanty en wms_ean_codes14
      $packaging->ean14->quanty = $packaging->quanty;
      $packaging->ean14->save();

      // Actualizo el quanty en wms_ean_codes14_detail
      $packaging->ean14->detail()->update(['quanty' => $packaging->quanty]);

      // Actualizo el quanty en wms_stock_transition
      StockTransition::where('code14_id', $packaging->ean14_id)
        ->update(['quanty' => $packaging->quanty]);
    }

    $packaging->save();

    return $packaging;
  }

  public function updateManyTransformResultPackaging(Request $request)
  {
    $data = $request->all();
    unset($data['session_user_id']);

    $return = [];
    DB::transaction(function () use ($data, &$return) {
      if (count($data) > 0) {
        foreach ($data as $key => $value) {

          $packaging = ScheduleTransformResultPackaging::findOrFail($value['id']);

          $packaging->status = empty($value['status']) ? $packaging->status : $value['status'];

          $return_data = $packaging->save();
          array_push($return, $return_data);
        }
      }
    });
    return $return;
  }

  public function printCodePackaging($id, $userId, $zonePositionId)
  {
    $packaging = ScheduleTransformResultPackaging::findOrFail($id);
    $packaging->have_code = true;
    $arrRes = [];
    DB::transaction(function () use ($packaging, &$arrRes, $userId, $zonePositionId) {

      $newstructurecode14 = '7704121';
      $new14 = [
        'code14' => $newstructurecode14,
        // 'code13'=> $packaging->product->ean,
        // 'document_detail_id'=>$document_detail_id,
        'quanty' => $packaging->quanty,
        'container_id' => $packaging->container_id,
        // 'code_parent_id'=>$codeParent['code14_id'],
        // 'product_id'=>$packaging->product_id,
      ];

      $savedcode14 = EanCode14::create($new14);
      $newsavedcode14 = $savedcode14['code14'] . $savedcode14['id'];
      $savedcode14->code14 = $newsavedcode14;
      $savedcode14->save();

      //Vget the
      $code14Detail = [
        'ean_code14_id' => $savedcode14['id'],
        'product_id' => $packaging->product_id
      ];
      EanCode14Detail::create($code14Detail);

      // TODO: Agregar enum en los estados y concentos de las transiciones
      $objTransition = [
        'product_id' => $packaging->product->id,
        'zone_position_id' => $zonePositionId,
        // 'code128_id'
        'code14_id' => $savedcode14['id'],
        'quanty' => $packaging->quanty,
        // TODO: Agregar enum a la action
        'action' => 'income',
        'concept' => 'transform',
        'user_id' => $userId
      ];

      StockTransition::create($objTransition);

      $packaging->ean14_id = $savedcode14->id;

      // TODO : Eliminar cajas de transicion cuando se generen las nuevas (codigo de referencia no funcional)
      // $transition = StockTransition::where('code14_id',$codeParent['code14_id'])->delete(); (codigo de referencia no funcional)
      $packaging->scheduleTransformPackagingCount()->create([
        'schedule_id' => $packaging->schedule_id,
        'count_index' => 1,
        'count_quanty' => $packaging->quanty
      ]);


      $packaging->save();

      $arrRes['html'] = Codes::getEan14Html($savedcode14['id']);
      // $arrRes['html'] = Codes::getHtmlFromCode($id);
      $arrRes['obj'] = $packaging;
    });

    return $arrRes;
  }

  public function saveCountPackaging(Request $request, $id)
  {

    $packaging = ScheduleTransformResultPackaging::findOrFail($id);

    $data = $request->all();

    $id_count = array_key_exists('id_count', $data) ? $data['id_count'] : NULL;
    $existCount = $packaging->scheduleTransformPackagingCount()->find($id_count);


    if ($existCount) {
      $existCount->count_quanty = $data['count_quanty'];
      $existCount->save();
      $countCreated = $existCount;
    } else {
      $countIndexs =  $packaging->scheduleTransformPackagingCount()->count();
      $indexNext = (int)$countIndexs + 1;
      $countCreated = $packaging->scheduleTransformPackagingCount()->create([
        'schedule_id' => $data['schedule_id'],
        'count_index' => $indexNext,
        'count_quanty' => $data['count_quanty']
      ]);
    }

    return $countCreated;
  }




  public function joinReferences(Request $request)
  {
    $data = $request->all();
    $references_source = array_key_exists('references_source', $data) ? $data['references_source'] : NULL;
    $reference_target = array_key_exists('reference_target', $data) ? $data['reference_target'] : NULL;

    $session_user_id = array_key_exists('session_user_id', $dataReq) ? $dataReq['session_user_id'] : NULL;

    $username = $this->getUsernameById($session_user_id);
    $reference_in = array();

    foreach ($references_source as $key => $value) {
      $reference_in[] = $value['id'];
    }


    $findStock = Stock::with(
      'product',
      'zone_position.zone.warehouse',
      'ean128',
      'ean14',
      'ean13'
    );

    if (!empty($reference_in)) {
      $findStock = $findStock->whereIn('product_id', $reference_in);
    }


    $findStock  = $findStock->get();
    if (empty($findStock)) {
      return $this->response->error('storage_pallet_product_no_found', 404);
    }


    foreach ($findStock as $keyStock => $stock) {

      // Crea el registro del movimiento
      $stockMovementOutput = [
        'product_id' => $stock['product_id'],
        'product_reference' => $stock['product']['reference'],
        'product_ean' => $stock['product']['ean'],
        'product_quanty' => $stock['quanty'],
        'zone_position_code' => $stock['zone_position']['code'],
        'code128' => $stock['ean128']['code128'],
        'code14' => $stock['ean14']['code14'],
        'username' => $username,
        'warehouse_id' => $stock['zone_position']['zone']['warehouse_id'],
        // TODO: Agregar enum a la action
        'action' => 'output',
        'concept' => 'join',
        'created_at' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
        'updated_at' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      ];


      // Crea el registro del movimiento
      $stockMovementIncome = [
        'product_id' => $reference_target['id'],
        'product_reference' => $reference_target['reference'],
        'product_ean' => $reference_target['ean'],
        'product_quanty' => $stock['quanty'],
        'zone_position_code' => $stock['zone_position']['code'],
        'code128' => $stock['ean128']['code128'],
        'code14' => $stock['ean14']['code14'],
        'username' => $username,
        'warehouse_id' => $stock['zone_position']['zone']['warehouse_id'],
        // TODO: Agregar enum a la action
        'action' => 'income',
        'concept' => 'join',
        'created_at' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
        'updated_at' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
      ];

      $stockMovements = array();
      $stockMovements[] = $stockMovementOutput;
      $stockMovements[] = $stockMovementIncome;
      // Generamos registro de movimiento de salida
      StockMovement::insert($stockMovements);

      $stock->product_id = $reference_target['id'];
      $stock->save();
    }

    foreach ($reference_in as $product_id_source) {
      $join = [
        'product_id_target' => $reference_target['id'],
        'product_id_source' => $product_id_source,
      ];

      JoinReferences::create($join);
    }



    return $findStock;
  }


  public function unjoinRequest(Request $request)
  {
    $data = $request->all();
    $product    = $data['product'];
    $reference  = $product['reference'];
    $start_date = $data['start_date'];
    $end_date   = $data['end_date'];


    $users = SchedulesFunctions::getLeadersWarehouseFromReference($reference, $this);

    if (!empty($users)) {

      $taskName = "Desunir la referencia " . $reference;


      foreach ($users as $leader) {
        $user = $leader['user'];
        $taskSchedules = [
          'start_date' => $start_date,
          'end_date' => $end_date,
          'name' => $taskName,
          'schedule_type' => ScheduleType::Unjoin,
          'schedule_action' => ScheduleAction::Assign,
          'status' => ScheduleStatus::Process,
          'notified' => false,
          'user_id' => $user['id']
        ];



        //Send the notification
        $email = $user['email'];
        $name = $leader['name'] . ' ' . $leader['last_name'];
        //TODO: uncomment for production
        // Mail::queue('emails.task', ['task' => $taskName], function ($mail) use ($email, $name) {
        //     $mail->from('alertas@wms.com', 'Wms alertas');
        //     $mail->to($email, $name)->subject('Nueva Tarea');
        // });


        $schedule = Schedule::create($taskSchedules);
        $scheduleUnjoin = $schedule->schedule_unjoin()->create(array(
          'warehouse_id' => $leader['warehouse_id']
        ));

        $stockDetails = array();
        foreach ($leader['stockInfo'] as $stockInfo) {
          $stockDetail = [
            'product_id' => $stockInfo['product_id'],
            'position_id' => $stockInfo['zone_position_id'],
            'code128_id' => $stockInfo['code128_id'],
            'code14_id' => $stockInfo['code14_id'],
            'quanty' => $stockInfo['quanty'],
            'detail_status' => TransformDetailStatus::Pendding
          ];
          $stockDetails[] = $stockDetail;
        }
        $scheduleUnjoin->scheduleUnjoinDetail()->createMany($stockDetails);
      }
    }

    return $users;
  }
  public function unjoinRemove(Request $request)
  {
    $dataReq = $request->all();
    $data = $dataReq['parentDetails'];
    $childtaskId = $dataReq['taskId'];
    $responseData = [];

    $session_user_id = array_key_exists('session_user_id', $dataReq) ? $dataReq['session_user_id'] : NULL;

    $username = $this->getUsernameById($session_user_id);


    if (!empty($data)) {
      DB::transaction(function () use ($data, &$responseData, $childtaskId, $username) {
        foreach ($data as $detailtransform) {
          // Buscamos la caja en el inventario si es de tipo almacenamiento
          $findposition = ZonePosition::find($detailtransform['position_id']);

          if (empty($findposition)) {
            return $this->response->error('storage_pallet_code14_no_found', 404);
          }

          $findStock = Stock::with(
            'product',
            'zone_position.zone',
            'ean128',
            'ean14',
            'ean13'
          )->where('code14_id', $detailtransform['code14_id'])
            ->where('zone_position_id', $findposition['id'])->get()->toArray();

          // Insertamos la informacion en tabla de transicion
          if (!empty($findStock)) {

            foreach ($findStock as $key => $value) {

              // Inserta los registros del stock a la tabla de transicion
              $objTransition = [
                'product_id' => $value['product_id'],
                'zone_position_id' => $value['zone_position_id'],
                'code128_id' => $value['code128_id'],
                'code14_id' => $value['code14_id'],
                'quanty' => $value['quanty'],
                // TODO: Agregar enum a la action
                'action' => 'output',
                'concept' => 'unjoin'
              ];

              StockTransition::create($objTransition);


              // Crea el registro del movimiento
              $stockMovement = [
                'product_id' => $value['product_id'],
                'product_reference' => $value['product']['reference'],
                'product_ean' => $value['product']['ean'],
                'product_quanty' => $value['quanty'],
                'zone_position_code' => $value['zone_position']['code'],
                'code128' => $value['ean128']['code128'],
                'code14' => $value['ean14']['code14'],
                'username' => $username,
                'warehouse_id' => $value['zone_position']['zone']['warehouse_id'],
                // TODO: Agregar enum a la action
                'action' => 'output',
                'concept' => 'unjoin'
              ];
              // array_push($responseData, $stockMovement);

              // Generamos registro de movimiento de salida
              StockMovement::create($stockMovement);
            }


            // Borra los registros del stock
            $stockDeleted = Stock::where('code14_id', $detailtransform['code14_id'])
              ->where('zone_position_id', $findposition['id'])->delete();

            $count = Stock::where('zone_position_id', $findposition['id'])->count();
            if ($count <= 0) {
              //Cambiamos el estado de la posiciÃ³n para indicar que se encuentra libre
              //TODO : Se comenta mientras
              // Habilita la posicion
              $findposition->concept_id = NULL;
              $findposition->save();
            }
          } else {
            return $this->response->error('storage_pallet_code14_no_found', 404);
          }


          // Actualizamos el detalle de la tarea
          $datailModel = ScheduleUnjoinDetail::find($detailtransform['id']);
          $datailModel->detail_status = $detailtransform['detail_status'];
          $datailModel->save();

          array_push($responseData, $datailModel);
        }
      });
    }
    return $responseData;
  }
  public function unjoinStored(Request $request)
  {
    $data = $request->all();
    $listRerences = array_key_exists('listRerences', $data) ? $data['listRerences'] : NULL;
    $codeParent = array_key_exists('parent', $data) ? $data['parent'] : NULL;


    $code14Parent = EanCode14::findOrFail($codeParent['code14_id']);
    $html = '';
    $structurecodes = Codes::GetStructureCode(PackagingType::Empaque);
    $i = 0;
    $breackCount = 0;
    $count = count($listRerences);
    foreach ($listRerences as $key => $code) {
      $i++;
      $breackCount++;
      $newstructurecode14 = '7704121' . $code['container']['id'];
      //Recorremos la estructura y generamos la estrucutra de los cÃ³digos IA
      foreach ($structurecodes as $structure) {
        $ia_code = $structure['ia_code'];
        $code_ia = $ia_code['code_ia'];

        //Validamos si el cÃ³digo IA debe tomar datos de alguna tabla
        if ((!empty($ia_code['table']) && empty($ia_code['field'])) || (!empty($ia_code['field']) && empty($ia_code['table']))) {
          return $this->response->error('No se pueden generar los cÃ³digos, porque el cÃ³digo GTIN' . $code_ia . 'estÃ¡ mal configurado', 404);
        } //Si Existe tabla y campo para el parametro se hace la consulta
        else if (!empty($ia_code['table']) && !empty($ia_code['field'])) {
          $table = $ia_code['table'];
          $field = $ia_code['field'];
          // TODO : falta definir como pbtener la clave del la tabla a o el campo a obtener
          $key   = 'ID_PARAMETRO';
          $results = DB::select(DB::raw('SELECT ' . $field . ' FROM ' . $table . ' WHERE id = ' . $key . ''));
          if (is_null($results)) {
            return $this->response->error('No se pueden generar los cÃ³digos, porque el cÃ³digo GTIN' . $code_ia . 'estÃ¡ mal configurado', 404);
          } else {
            $array = json_decode(json_encode($results[0]), True);

            $newstructurecode14 .= '(' . $code_ia . ')' . $array['number'];
          }
        } //SI no hay tabla se deja el valor por defecto planteado en la tabla
        else {
          $newstructurecode14 .= '(' . $ia_code['code_ia'] . ')';
        }
      }

      $new14 = [
        'code14' => $newstructurecode14,
        'code13' => $code['ean'],
        // 'document_detail_id'=>$document_detail_id,
        'quanty' => $code['quanty'],
        'container_id' => $code['container']['id'],
        'code_parent_id' => $codeParent['code14_id'],
        'product_id' => $code['id'],
      ];

      $savedcode14 = EanCode14::create($new14);
      $newsavedcode14 = $savedcode14['code14'] . $savedcode14['id'];
      $savedcode14->code14 = $newsavedcode14;
      $savedcode14->save();

      // TODO: Agregar enum en los estados y concentos de las transiciones
      $objTransition = [
        'product_id' => $code['id'],
        // 'zone_position_id'
        // 'code128_id'
        'code14_id' => $savedcode14['id'],
        'quanty' => $code['quanty'],
        // TODO: Agregar enum a la action
        'action' => 'income',
        'concept' => 'unjoin'
      ];

      StockTransition::create($objTransition);
      /*
    */
      $html .= Codes::getEan14Html($savedcode14['id']);
      if ($breackCount == 3) {
        $html .= "<div class='breack-page'></div>";
        $breackCount = 0;
      }
    }

    $transition = StockTransition::where('code14_id', $codeParent['code14_id'])->delete();

    // Actualizamos el detalle de la tarea
    $datailModel = ScheduleUnjoinDetail::find($codeParent['id']);
    $datailModel->detail_status = $codeParent['detail_status'];
    $datailModel->save();

    return $html;
  }
  public function closeUnjoinAction($id)
  {
    //Conuslto la tarea/accion que se esta cerrando (Remover, Transformar, Almacenar)
    $schedule = Schedule::find($id);
    $response = [];
    // Se marca como cerrada
    $schedule->status = ScheduleStatus::Closed;
    // Si tiene tarea padre y se guardo correctamente se valida y se actualiza el padrre
    if (!empty($schedule->parent_schedule_id) && $schedule->save()) {
      // Se consulta el registro de estados de la tarea padre
      $unjoin = $schedule->parent_schedule->schedule_unjoin;
      if (!empty($unjoin->id)) {

        if ($schedule->schedule_action == ScheduleAction::Remove) {
          $unjoin->remove_status = true;
        }
        if ($schedule->schedule_action == ScheduleAction::Unjoin) {
          $unjoin->unjoin_status = true;
        }
        if ($schedule->schedule_action == ScheduleAction::Store) {
          $unjoin->store_status = true;
        }
        if ($unjoin->unjoin_status && $unjoin->remove_status && $unjoin->store_status) {

          $unjoin->status = true;
          $unjoinDetailFirst = $unjoin->scheduleUnjoinDetail()->first();
          $this->inactiveJoinReference($unjoinDetailFirst->product_id);
          $schedule->parent_schedule->status = ScheduleStatus::Closed;
          $schedule->parent_schedule->save();
        }

        $unjoin->save();
      }
      $response = $unjoin;
    }
    return $response;
  }

  public function inactiveJoinReference($product_id_target)
  {
    $joins = JoinReferences::where('product_id_target', $product_id_target)->get();
    foreach ($joins as $key => $join) {
      $join->active = false;
      $join->save();
    }
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
    $companyId = $data['company_id'];
    $positioncode = $data['positions'];
    $packagingType = $data['packaging_type'];
    $codeinput = array_key_exists('codeunity', $data) ? $data['codeunity'] : null;
    $quanty = $data['quanty'];
    $product_id = array_key_exists('Product', $data) ? $data['Product']['id'] : null;

    //Consultamos el concepto de zona para cambiar el estado a no disponible de la posiciÃ³n
    $zonepconcept = ZoneConcept::where('is_storage', true)->where('active', true)->first();

    $username = $this->getUsernameById($data['session_user_id']);

    if (!isset($zonepconcept)) {
      return $this->response->error('storage_pallet_zone_concept_no_found', 404);
    }
    if ($packagingType == PackagingType::Logistica) {
      $findcode128 = EanCode128::where('code128', $codeinput)->first();

      if (!isset($findcode128)) {
        return $this->response->error('storage_pallet_code128_no_found', 404);
      }

      $stockExist = Stock::where('code128_id', $findcode128['id'])->first();
      if ($stockExist) {
        $samePosition = true;
        foreach ($positioncode as $pos) {
          //Check if the position belongs to the company
          $findposition = ZonePosition::where('code', $pos['codePosition'])
            ->whereHas('zone.warehouse.distribution_center', function ($q) use ($companyId) {
              $q->where('company_id', $companyId);
            })
            ->first();

          if (empty($findposition)) {
            return  $this->response->error('storage_pallet_position_no_found', 404);
          }

          $stockPosExist = Stock::where('code128_id', $findcode128['id'])
            ->where('zone_position_id', $findposition->id)->first();
          if (!$stockPosExist) {
            $samePosition = false;
          }
        }
        if ($samePosition) {
          return $this->response->error('storage_pallet_code128_exist', 404);
        } else {
          return $this->response->error('storage_pallet_code128_relocate', 320);
        }
      }

      // $findpallet = Pallet::with('ean14_detail.product', 'product_ean14.product')->where('code128_id', $findcode128['id'])->get()->toArray();
      $findpallet2 = Pallet::where('code128_id', $findcode128['id'])->get()->toArray();

      $findposition = ZonePosition::with('zone')->where('code', $positioncode[0]['codePosition'])->first();
      if (!isset($findposition)) {
        return $this->response->error('storage_pallet_position_no_found', 404);
      }

      $findposition->save();
      //Se almacena el pallet y la posiciÃ³n
      foreach ($findpallet2 as $value) {

        $detailReferences = EanCode14Detail::find($value['document_detail_id']);

        $stockExisting = Stock::where('zone_position_id', $findposition['id'])->where('product_id', $detailReferences->product_id)->first();

        if ($stockExisting) {
          $quanty = $stockExisting->quanty + $value['quanty'];
          Stock::where('id', $stockExisting->id)->update(['quanty' => $quanty]);
        } else {
          $stock = [
            'product_id' => $detailReferences->product_id,
            'zone_position_id' => $findposition['id'],
            'code128_id' => $findcode128['id'],
            'code_ean14' => $value['code_ean14'],
            'quanty' => $value['quanty'],
            'document_detail_id' => $value['id'],
            'quanty_14' => $value['quanty']

          ];
          Stock::create($stock);
        }
        // $zoneFeature = PositionFeature::where('zone_position_id', $findposition['id'])->first();
        // $zoneFeature->free_value = ($zoneFeature->free_value - $value['quanty']);
        // $zoneFeature->save();


        // PositionFeature::where('zone_position_id', $findposition['id'])->update(['stored' => 1]);

        // foreach ($detailReferences as $ref) {



        $config = EanCode128::where('id', $findcode128['id'])->update(['stored' => 1]);

        // if ($detailReferences['quanty_received_pallet'] <= 0) {
        //   $config = DocumentDetail::where('product_id', $detailReferences['product_id'])->where('id', $detailReferences['id'])->update(['stored' => 1]);
        // }
        // $stockMovement = [
        //   'product_id'=>$ref['product_id'],
        //   'product_reference'=>$ref['product']['reference'],
        //   'product_ean'=>$ref['product']['ean'],
        //   'product_quanty'=>$ref['quanty'],
        //   'zone_position_code'=>$findposition['code'],
        //   'code128'=>$findcode128['code128'],
        //   // 'code14'=>$value['code14']['code14'],
        //   'username'=>$username,
        //   'warehouse_id'=>$findposition['zone']['warehouse_id'],
        //   // TODO: Agregar enum a la action
        //   'action'=>'income',
        //   'concept'=>'storage'
        // ];
        // return $findpallet;
        // StockMovement::create($stockMovement);
        // }
        // $this->updateEan14AsStored($value['code14']['id']);
      }
      // });
    } else  if ($packagingType == PackagingType::Empaque) {
      //Consultamos la unidad de empaque(cÃ³digo14)
      $code14 = Codes::GetCode14ByCode($codeinput);
      if (count($code14) == 0) {
        return $this->response->error('storage_pallet_code14_no_found', 404);
      }

      $ean14_id = $code14[0]->ean_code14_id;
      $stockExist = Stock::where('code14_id', $ean14_id)->first();
      if ($stockExist) {
        $samePosition = true;
        foreach ($positioncode as $pos) {
          $findposition = ZonePosition::where('code', $pos['codePosition'])
            ->whereHas('zone.warehouse.distribution_center', function ($q) {
              $q->where('company_id', $companyId);
            })
            ->first();

          if (empty($findposition)) {
            return  $this->response->error('storage_pallet_position_no_found', 404);
          }

          $stockPosExist = Stock::where('code14_id', $ean14_id)
            ->where('zone_position_id', $findposition->id)->first();
          if (!$stockPosExist) {
            $samePosition = false;
          }
        }

        if ($samePosition) {
          return $this->response->error('storage_pallet_code14_exist', 404);
        } else {
          return $this->response->error('storage_pallet_code14_relocate', 320);
        }
      }

      DB::transaction(function () use ($zonepconcept, $code14, $positioncode, $username, $ean14_id) {


        foreach ($positioncode as $pos) {

          //Buscamos la posiciÃ³n en la q se quiere almacenar
          $findposition = ZonePosition::with('zone')->where('code', $pos['codePosition'])->first();

          $findposition = ZonePosition::with('zone')->where('code', $pos['codePosition'])
            ->whereHas('zone.warehouse.distribution_center', function ($q) {
              $q->where('company_id', $companyId);
            })
            ->first();

          if (!isset($findposition)) {
            return $this->response->error('storage_pallet_position_no_found', 404);
          }

          //Validamos que la posiciÃ³n se encuentre disponible para almacenar
          if (!$findposition['active']) {
            return $this->response->error('storage_pallet_position_unavailable', 404);
          }

          //Cambiamos el estado de la posiciÃ³n para indicar que se encuentra ocupada
          //Se comenta mientras se conoce bn la validaciÃ³n de cuando una posiciÃ³n esta totalmente ocupada segÃºn las caracterÃ­zticas
          // $findposition->concept_id = $zonepconcept['id'];
          // TODO : Inactivar la posicion SOLO cuando se llene la capacidad de la posicion
          // la capacidad se determina con las caracteristicas asignadas : Ancho, Peso ... ect
          // $findposition->active = false;
          $findposition->save();

          $conceptMovement = $this->validEan14Transition($ean14_id, "storage");
          $this->validEan14CountReferece($ean14_id);
          foreach ($code14 as $key => $value) {
            $code14find = get_object_vars($value);

            // TODO : Agregar enum para el concept de los movimientos

            //Se almacena el pallet y la posiciÃ³n
            $stock = [
              'code14_id' => $code14find['ean_code14_id'],
              'zone_position_id' => $findposition['id'],
              'product_id' => $code14find['product_id'],
              'quanty' => $code14find['quanty']
            ];

            $stockMovement = [
              'product_id' => $code14find['product_id'],
              'product_reference' => $code14find['reference'],
              'product_ean' => $code14find['ean13'],
              'product_quanty' => $code14find['quanty'],
              'zone_position_code' => $findposition['code'],
              'code128' => NULL,
              'code14' => $code14find['code14'],
              'username' => $username,
              'warehouse_id' => $findposition['zone']['warehouse_id'],
              // TODO: Agregar enum a la action
              'action' => 'income',
              'concept' => $conceptMovement
            ];

            Stock::create($stock);
            StockMovement::create($stockMovement);
          }

          $this->updateEan14AsStored($ean14_id);
        }
      });
    } else  if ($packagingType == PackagingType::Producto) {
      DB::transaction(function () use ($zonepconcept, $product_id, $codeinput, $quanty, $positioncode, $username, $companyId) {

        foreach ($positioncode as $pos) {
          //Buscamos la posiciÃ³n en la q se quiere almacenar
          $findposition = ZonePosition::with('zone')->where('code', $pos['codePosition'])
            ->whereHas('zone.warehouse.distribution_center', function ($q) {
              $q->where('company_id', $companyId);
            })
            ->first();

          if (!isset($findposition)) {
            return $this->response->error('storage_pallet_position_no_found', 404);
          }

          //Validamos que la posiciÃ³n se encuentre disponible para almacenar
          if (!$findposition['active']) {
            return $this->response->error('storage_pallet_position_unavailable', 404);
          }

          //Cambiamos el estado de la posiciÃ³n para indicar que se encuentra ocupada
          //Se comenta mientras se conoce bn la validaciÃ³n de cuando una posiciÃ³n esta totalmente ocupada segÃºn las caracterÃ­zticas
          // $findposition->concept_id = $zonepconcept['id'];
          // TODO : Inactivar la posicion SOLO cuando se llene la capacidad de la posicion
          // la capacidad se determina con las caracteristicas asignadas : Ancho, Peso ... ect
          // $findposition->active = false;
          $findposition->save();

          if ($codeinput != null) {
            $product = Product::where('ean', $codeinput)->first();

            if (!isset($product)) {
              return $this->response->error('storage_pallet_product_no_found', 404);
            }
            $product_id = $product['id'];
          }

          //Se almacena el pallet y la posiciÃ³n
          $stock = [
            'zone_position_id' => $findposition['id'],
            'product_id' => $product_id,
            'quanty' => $quanty
          ];

          $stockMovement = [
            'product_id' => $product_id,
            'product_reference' => $product['reference'],
            'product_ean' => $product['ean'],
            'product_quanty' => $quanty,
            'zone_position_code' => $findposition['code'],
            'code128' => NULL,
            'code14' => NULL,
            'username' => $username,
            'warehouse_id' => $findposition['zone']['warehouse_id'],
            // TODO: Agregar enum a la action
            'action' => 'income',
            'concept' => 'storage'
          ];

          Stock::create($stock);
          StockMovement::create($stockMovement);
        }
      });
    }


    $affectedRows = Suggestion::where('code', $codeinput)->update(['stored' => 1]);

    return $this->response->noContent();
  }

  // Valida si un codigo ean14 esta en transicion al momento de almacenarse y lo borra de dicho lugar
  // Devuelve el concepto que tenia en dicha transicion para que se almacene con ese mismo
  // recive un concepto por defecto en caso de no encontrar transicion relaiconada
  // Ademas verifica su el ean 14 esta relacionado a algun proceso de Transformacion o de Desunificacion
  // De ser asi verifica si esta es la ultima caja en almacenarse y si lo es entonces cierra dicha tarea
  public function validEan14Transition($ean14Id, $concept)
  {
    $newConcept = $concept;
    // Se busca si el codigo 14 que se pretende almacenar existe en transicion
    $stockTransitionExist = StockTransition::where('code14_id', $ean14Id)->first();

    if (!empty($stockTransitionExist)) {
      $newConcept = $stockTransitionExist->concept;

      $ean14 = EanCode14::findOrFail($ean14Id);


      // $parentCode = $ean14->parent_code;
      if ($ean14) {

        if ($newConcept == 'transform') {

          $packaginTransform = $ean14->scheduleTransformResultPackaging;
          if ($packaginTransform) {

            if ($packaginTransform->status !== PackagingStatus::Approved) {
              return $this->response->error('transform_ean_not_approved', 404);
            }
            $scheduleTransform = $packaginTransform->scheduleTransform;


            if ($scheduleTransform) {
              $schedule = $scheduleTransform->schedule;
              if ($schedule) {
                $detailList = $scheduleTransform->scheduleTransformDetail()->get();
                $isAllDetailsTransform = true;
                $codesGenerated = [];
                $codesToGenerated = [];

                $packagingList = $scheduleTransform->scheduleTransformResultPackaging()->get();

                foreach ($detailList as $detail) {
                  if ($detail->detail_status != TransformDetailStatus::Transformed) {
                    $isAllDetailsTransform = false;
                    break;

                    // }else{
                    //   foreach ($detail->ean14->child_codes()->with('stock')->get()->toArray() as $codeGenerated) {
                    //     if (!empty($codeGenerated['stock'])) {
                    //       array_push($codesGenerated,$codeGenerated );
                    //     }
                    //     array_push($codesToGenerated,$codeGenerated );
                    //   }
                  }
                }

                foreach ($packagingList as $package) {
                  if (!empty($package->status == PackagingStatus::Stored)) {
                    array_push($codesGenerated, $package);
                  }
                  array_push($codesToGenerated, $package);
                }

                if ($isAllDetailsTransform) {
                  $countCodeGenerated = count($codesGenerated);
                  // le restamos uno a la cantidad de codigos total ya que se resta el codigo que esta proximo a insertarse
                  $countCodeToGenerate = count($codesToGenerated) - 1;

                  if ($countCodeGenerated == $countCodeToGenerate) {
                    // Cerrar la tarea de almancear
                    $findSchedulesStore = $schedule->child_schedules()->where('schedule_action', ScheduleAction::Store)->get()->toArray();
                    foreach ($findSchedulesStore as $key => $value) {

                      $this->closeTransformAction($value['id']);
                    }
                  }
                }
                $packaginTransform->status = PackagingStatus::Stored;
                $packaginTransform->save();
              }
            }
          }
        } else if ($newConcept == 'unjoin') {
          $detailunjoin = $parentCode->unjoinDetail;
          if ($detailunjoin) {
            $scheduleUnjoin = $detailunjoin->scheduleUnjoin;
            if ($scheduleUnjoin) {
              $schedule = $scheduleUnjoin->schedule;
              if ($schedule) {
                $detailList = $scheduleUnjoin->ScheduleUnjoinDetail()->get();
                $isAllDetailsUnjoin = true;
                $codesGenerated = [];
                $codesToGenerated = [];
                foreach ($detailList as $detail) {
                  if ($detail->detail_status != TransformDetailStatus::Unjoin) {
                    $isAllDetailsUnjoin = false;
                    break;
                  } else {
                    foreach ($detail->ean14->child_codes()->with('stock')->get()->toArray() as $codeGenerated) {
                      if (!empty($codeGenerated['stock'])) {
                        array_push($codesGenerated, $codeGenerated);
                      }
                      array_push($codesToGenerated, $codeGenerated);
                    }
                  }
                }
                if ($isAllDetailsUnjoin) {
                  $countCodeGenerated = count($codesGenerated);
                  // le restamos uno a la cantidad de codigos total ya que se resta el codigo que esta proximo a insertarse
                  $countCodeToGenerate = count($codesToGenerated) - 1;

                  if ($countCodeGenerated == $countCodeToGenerate) {
                    // Cerrar la tarea de almancear
                    $findSchedulesStore = $schedule->child_schedules()->where('schedule_action', ScheduleAction::Store)->get()->toArray();
                    foreach ($findSchedulesStore as $key => $value) {

                      $this->closeUnjoinAction($value['id']);
                    }
                  }
                }
              }
            }
          }
        }
      }

      $stockTransitionExist->delete();
    }
    return $newConcept;
  }


  public function validEan14CountReferece($ean14Id)
  {
    $ean14 = EanCode14::findOrFail($ean14Id);

    if (!empty($ean14->countDocument->documentDetail)) {
      if ($ean14->countDocument->documentDetail->count_status != '1') {
        return $this->response->error('code_with_active_count', 404);
      }
    }
  }

  // Actualiza el estado de un ean 14 a almacenado
  // se usa al almacenar un ean14 y un 128
  public function updateEan14AsStored($id)
  {
    $ean14 = EanCode14::findOrFail($id);
    $ean14->stored = true;
    $ean14->save();
  }

  public function getTransition(Request $request)
  {

    $data = $request->all();
    $user_id = array_key_exists('user_id', $data) ? $data['user_id'] : null;
    $session_user_id = array_key_exists('session_user_id', $data) ? $data['session_user_id'] : null;


    $transition = StockTransition::orderBy('id', 'desc')->with('product', 'document_detail', 'ean128', 'ean14.document_detail.document', 'ean14.code14_packing');

    if (!empty($user_id)) {
      $transition->where('user_id', $user_id);
    }
    if (!empty($session_user_id)) {
      $transition->where('user_id', $session_user_id);
    }
    return $transition->get()->toArray();
  }

  public function getTransitionBySchedule(Request $request)
  {
    $data = $request->all();
    $schedule_id = $data['task'];
    /*$transition =ScheduleTransition::orderBy('id','desc')->with('stock_transition','schedule')->where('schedule_id',$schedule_id)->get();
    return $transition->toArray();*/

    $transition = StockTransition::orderBy('id', 'desc')
      ->with('product', 'ean128', 'ean14.detail', 'schedule_transition')
      ->whereHas('schedule_transition', function ($query) use ($schedule_id) {
        $query->where('schedule_id', $schedule_id);
      })
      ->get();
    return $transition->toArray();
  }

  public function getProductByIdSchedule(Request $request)
  {
    $data = $request->all();
    $schedule_id = $data['task'];
    //$Zone_position_id= $data['zone_position_id'];
    /*$transition =Product::orderBy('id','desc')->with('stock')->where('schedule_id',$schedule_id)->get();
    return $transition->toArray();*/

    $config = StockPickingConfig::with('stock_picking_config_product.product.stock.zone_position.zone', 'stock_picking_config_product.product.stock.ean14', 'stock_picking_config_product.stock_transition')->where('schedule_id', $schedule_id)->first();

    // $products = Stock::orderBy('id','desc')
    // ->with('product','ean128','ean14','ean13','stock_picking_config')
    // ->whereHas('stock_picking_config', function ($query) use($schedule_id) {
    //   $query->where('schedule_id',$schedule_id);
    // })
    // ->get();
    return $config->toArray();
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
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
  }


  public function getUsernameById($id)
  {
    $username = '';
    if (!empty($id)) {
      $user = User::find($id);
      if (!empty($user)) {
        $username = $user->username;
      }
    }
    return $username;
  }

  public function saveValidateAdjustSchedule(Request $request)
  {
    $data = $request->all();
    $scheduleTransformId = $data['schedule_transform_id'];
    $scheduleTransformAdjustValidate = $data['schedule_transform_adjust_validate'];

    $scheduleTypeValidate = ScheduleType::Validate;
    $scheduleStatusProcess = ScheduleStatus::Process;

    /*
      Busco si la tarea padre de transformar tiene una tarea de validaciÃ³n asignada con las siguientes condiciones:
      schedule_type = validate_adjust
      status = process
    */
    $schedule = ScheduleTransformValidateAdjust::whereHas('schedule', function ($q) use ($scheduleTypeValidate, $scheduleStatusProcess) {
      $q->where('schedule_type', $scheduleTypeValidate)->where('status', $scheduleStatusProcess);
    })
      ->whereHas('schedule_transform_result_packaging', function ($q) use ($scheduleTransformId) {
        $q->where('schedule_transform_id', $scheduleTransformId);
      })
      ->first();

    // Si no existe => creo una tarea nueva.
    if (empty($schedule)) {
      $newSchedule = $data['schedule'];
      $cediCharge = SchedulesFunctions::getCediBossByWarehouse($newSchedule['warehouse_id'], $this);
      $newSchedule['user_id'] = $cediCharge->user->id;

      $idSchedule = Schedule::create($newSchedule)->id;
    }
    // Si existe => me quedo con el ID.
    else {
      $idSchedule = $schedule['schedule_id'];
    }

    // Le pego el schedule_id a las refs a validar.
    foreach ($scheduleTransformAdjustValidate as &$val) {
      $val['schedule_id'] = $idSchedule;
    }

    // Relaciono las referencias a la tarea principal
    ScheduleTransformValidateAdjust::insert($scheduleTransformAdjustValidate);

    return $this->response->created();
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function stockPosition(Request $request)
  {
    $warehouse = $request->input('warehouse_id');
    $reference = $request->input('reference');
    $codePosition = $request->input('position');
    $zone_id = $request->input('zone_id');
    $stock = DB::table('wms_stock')
      ->leftjoin('wms_products', 'wms_stock.product_id', '=', 'wms_products.id')
      ->leftjoin('wms_product_categories', 'wms_products.category_id', '=', 'wms_product_categories.id')
      ->leftjoin('wms_zone_positions', 'wms_stock.zone_position_id', '=', 'wms_zone_positions.id')
      ->leftjoin('wms_zones', 'wms_zone_positions.zone_id', '=', 'wms_zones.id')
      ->leftjoin('wms_warehouses', 'wms_zones.warehouse_id', '=', 'wms_warehouses.id')
      ->leftjoin('wms_ean_codes128', 'wms_stock.code128_id', '=', 'wms_ean_codes128.id')
      ->leftjoin('wms_ean_codes14', 'wms_stock.code_ean14', '=', 'wms_ean_codes14.id')
      ->leftjoin('wms_eancodes14_packing', 'wms_stock.id', '=', 'wms_eancodes14_packing.stock_id')
      ->leftjoin('wms_documents as docpacking', 'wms_eancodes14_packing.document_id', '=', 'docpacking.id')
      ->leftjoin('wms_documents as doc', 'wms_ean_codes14.document_id', '=', 'doc.id')
      ->orderBy('wms_stock.zone_position_id')
      ->select(
        'wms_warehouses.name as bodega',
        'wms_products.description as description',
        'wms_zone_positions.row',
        'wms_zone_positions.module',
        'wms_zone_positions.code',
        'wms_products.ean',
        'wms_products.reference',
        'wms_stock.quanty',
        'wms_zones.name as zona',
        'wms_zones.id as zone_id',
        'wms_stock.quanty',
        'wms_stock.quanty_14',
        'wms_ean_codes14.code14',
        'wms_ean_codes14.master',
        'wms_products.alt_code as short_reference',
        'wms_products.remark as colection',
        'doc.facturation_number',
        'doc.external_number',
        DB::raw("IF(doc.number IS NULL OR doc.number = '', docpacking.number, doc.number) as number")
      );

    if (isset($zone_id)) {
      $stock = $stock->where('wms_zones.id', $zone_id);
    }

    if (isset($warehouse)) {
      $stock = $stock->where('wms_zones.warehouse_id', $warehouse);
    }

    if (isset($reference)) {
      $stock = $stock->where('wms_products.reference', $reference);
    }

    if (isset($codePosition)) {
      $stock = $stock->where('wms_zone_positions.code', $codePosition);
    }


    $stock = $stock->get();

    return $stock;
  }
}
