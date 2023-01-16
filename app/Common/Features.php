<?php

namespace App\Common;

use App\Models\ScheduleDocument;
use App\Models\EanCode128;
use App\Models\Warehouse;
use App\Models\Zone;
use App\Models\ZonePosition;
use App\Models\User;
use App\Enums\ScheduleType;
use App\Enums\ScheduleStatus;
use App\Models\Schedule;
use App\Models\Suggestion;
use App\Common\Settings;
use App\Enums\SettingsKey;
use App\Models\MergedPosition;
use DB;
use App\Models\ProductEan14;
use App\Models\Product;
use App\Models\Stock;
use App\Models\ZoneConcept;
use Exception;
use RuntimeException;

/**
 * Class used to analiza all the features
 */
class Features
{

  function __construct()
  {
  }

  public static function SuggestPositions($scheduleId)
  {
    //Get the pallets
    // $docs = DB::table('wms_schedule_documents as sd')
    //         ->join('wms_ean_codes128 as c128','sd.document_id', '=', 'c128.document_id')
    //         // ->join('wms_pallet as p', 'c128.id', '=', 'p.code128_id')
    //         // ->join('wms_ean_codes14 as c14', 'p.code14_id', '=', 'c14.id')
    //         ->where('schedule_id', $scheduleId)
    //         ->select('sd.document_id', 'c128.code128', 'c128.id as code128_id')
    //         // ->select('sd.document_id', 'c128.code128', 'c14.code14')
    //         ->get();

    //Get the pallets by schedule id
    $docs = EanCode128::whereHas('schedule_document', function ($q) use ($scheduleId) {
      $q->where('schedule_id', $scheduleId);
    })->get();

    //For each pallet we should get the features and get a position for it
    // foreach ($docs as $value) {
    //   //get the pallet descriptions all the code 14
    // }

    return $docs->toArray();

    //Get the pallet from the documents on the receipt

    //Each docuent can have one or more pallets
  }

  public static function suggestPalletPosition($code128, $companyId)
  {
    //$code128 = $request->input('code_128');
    $code128helper = EanCode128::with('container.container_features.feature', 'schedule_document.schedule.schedule_receipt')->where('code128', $code128)->where('canceled', false);
    $docs = $code128helper->first();

    // return $this->response->error('ve'.$code128helper, 404);

    $settingsObj = new Settings($companyId);
    //TODO: enum
    $widthFeature = $settingsObj->get(SettingsKey::FEATURE_WIDTH);
    //Ancho (mts)
    //Get the pallet width
    $code128helper = $code128helper->whereHas('container.container_features.feature', function ($q) use ($widthFeature) {
      // TODO: use enum or settings key
      $q->where('name', $widthFeature);
    })->firstOrFail();

    //Compare the Capacidad (kg) againts $weight
    $fCapacity = $settingsObj->get(SettingsKey::FEATURE_CAPACITY);
    $fHeight = $settingsObj->get(SettingsKey::FEATURE_HEIGHT);

    //Get the position of ancho (mts)
    $palletWidth = $code128helper->toArray();
    $containerFeatures = $palletWidth['container']['container_features'];
    $widthPallet = [];
    $fearureId = 0;
    foreach ($containerFeatures as $f) {
      if ($f['feature']['name'] == $widthFeature) {
        $widthPallet = $f['value'];
        $fearureId = $f['feature_id'];
        break;
      }
      // return $f['feature'];
    }

    //Get the warehouse
    $receipt = $docs->schedule_document->schedule->schedule_receipt;
    //Get the weight and the warehouse of the pallet
    $weight = (float)$docs->weight;
    //$weight = (int)$docs->weight;
    //Get the height
    $height = (float)$docs->height;
    $warehouse = $receipt->warehouse_id;



    $zoneWarehouse = ZonePosition::with('zone_position_features.feature')->whereHas('zone', function ($q) use ($warehouse) {
      $q->where('warehouse_id', $warehouse)
        ->whereHas('zone_type', function ($query) {
          $query->where('is_storage', true);
        });
    });

    $zWarehouse = ZonePosition::whereHas('zone', function ($q) use ($warehouse) {
      $q->where('warehouse_id', $warehouse)
        ->whereHas('zone_type', function ($query) {
          $query->where('is_storage', true);
        });
    })->first();


    // $zWarehouse = $zWarehouse->first();
    $widthHelper = DB::table('wms_zone_positions as zp')
      ->join('wms_position_features as pf', 'zp.id', '=', 'pf.zone_position_id')
      ->where('pf.feature_id', $fearureId)
      ->where('zp.zone_id', $zWarehouse->zone_id);

    //Check if can be stored in a sinlge position
    $zonesWidth = $widthHelper->where('value', '>=', $widthPallet)->get();


    // return $zWarehouse;

    // return $zonesWidth;
    // $zonesWidth = $zoneWarehouse->whereHas('zone_position_features', function ($q) use ($widthPallet, $fearureId) {
    //           $q->where('value', '>=', $widthPallet)->where('feature_id', $fearureId);
    //         })->get();

    $needsMultiple = count($zonesWidth) ? false : true;
    $suggestionName = '';

    $_this = new self;

    // return ($needsMultiple) ? 'true' : 'false';
    if ($needsMultiple) {

      //Get the most common position width
      $zonesWeight = DB::table('wms_zone_positions as zp')
        ->join('wms_position_features as pf', 'zp.id', '=', 'pf.zone_position_id')
        ->where('pf.feature_id', $fearureId)
        ->where('zp.zone_id', $zWarehouse->zone_id)
        ->select('pf.value', DB::raw('count(*) as total'))
        ->groupBy('pf.value')
        ->orderBy('total', 'desc')
        ->get();



      $commonWidt = $zonesWeight[0]->value; //$zWarehouse->zone_id;

      //Check how many positions we need
      $totalPositions = ceil($widthPallet / $commonWidt);

      $docs->positions = $totalPositions;
      // $docs->save();

      //Get the positions
      $suggestion = [];
      $suggestionName = 'Almacenar ' . $code128 . ' en ' . $totalPositions . ' posiciones';
      $first = true;
      $nextId = 0;
      $nextlevel = 0;
      $nextRow = 0;
      $i = 0;
      $compWeight = $weight / $totalPositions;

      $zone = $_this->GetSinglePosition($zoneWarehouse, $fCapacity, $compWeight, $fHeight, $height)->get();



      if ($zone->isEmpty()) {
        throw new Exception('not_found_position');
      }

      //Loop the available positions
      foreach ($zone as $z) {
        //Check if we already have all the required positions
        if ($i == $totalPositions) {
          break;
        }
        //Check if the first is the A position
        if ($first) {
          if ($z->position != 'A') {
            continue;
          }
          //Set helper variable to check the next loop
          $first = false;
          $nextId = $z->id;
          $nextlevel = $z->level;
          $nextRow = $z->row;

          //Push the new suggestion
          $suggestion[] = [
            'code' => $code128,
            'zone_position_id' => $z->id,
            'stored' => false
          ];
          //Set the task name
          //$suggestionName .= $z->description . ' , ';
          //Increment counter
          $i++;
          $suggestionName .= '<hr><strong>Posición ' . $i . '</strong><br><strong>Fila:</strong> ' . $z->row . '<br> <strong>Nivel:</strong> ' . $z->level . '<br> <strong>Módulo:</strong> ' . $z->module . '<br> <strong>Posición:</strong> ' . $z->code;
        } else {
          $nextId++;
          //Check if the postion is the next one
          if ($nextId == $z->id && $nextlevel == $z->level && $nextRow == $z->row) {
            //Push the new suggestion
            $suggestion[] = [
              'code' => $code128,
              'zone_position_id' => $z->id,
              'stored' => false
            ];
            //Set the task name
            //  $suggestionName .= $z->description . ' , ';
            //Increment counter
            $i++;
            $suggestionName .= '<hr><strong>Posición ' . $i . '</strong><br><strong>Fila:</strong> ' . $z->row . '<br> <strong>Nivel:</strong> ' . $z->level . '<br> <strong>Módulo:</strong> ' . $z->module . '<br> <strong>Posición:</strong> ' . $z->code;
            $nextId = $z->id;
            $nextlevel = $z->level;
            $nextRow = $z->row;
          } else {
            //Restart the suggestions and task names
            $suggestion = [];
            $suggestionName = '';
            $i = 0;
            $first = true;
            continue;
          }
        }
      }

      // return $suggestionName;

      Suggestion::insert($suggestion);

      //Create the merged position
    } else {
      //Get the storage zone of the warehouse
      // return 5;
      $zone = $_this->GetSinglePosition($zoneWarehouse, $fCapacity, $weight, $fHeight, $height)->firstOrFail();;

      //Insert the suggestion
      $suggestion = [
        'code' => $code128,
        'zone_position_id' => $zone->id,
        'stored' => false
      ];

      // return $suggestion;

      Suggestion::create($suggestion);

      //$suggestionName = 'Almacenar ' . $code128 . ' en ' . $zone->description;
      $suggestionName = 'Almacenar ' . $code128 . ' <hr>  <strong>Fila:</strong> ' . $zone->row . '<br> <strong>Nivel:</strong> ' . $zone->level . '<br> <strong>Módulo:</strong> ' . $zone->module . '<br> <strong>Posición:</strong> ' . $zone->code;
    }
    //Insert the task
    //Get the responsible user
    $user = User::where('personal_id', $receipt->responsible_id)->firstOrFail();
    $task = [
      'name' => $suggestionName,
      'schedule_type' => ScheduleType::Store,
      'status' => ScheduleStatus::Process,
      'user_id' => $user->id,
      'company_id' => $companyId
    ];

    //Update the new task

    $schedule = Schedule::create($task);

    //Delete the old task
    $oldSchedule = null;
    if (isset($docs->schedule_id)) {
      $oldSchedule = $docs->schedule_id;
    }

    $docs->schedule_id = $schedule->id;
    $docs->save();

    if (isset($oldSchedule)) {
      Schedule::where('id', $oldSchedule)->delete();
    }
  }

  public static function GetSinglePosition($zoneWarehouse, $fCapacity, $weight, $fHeight,  $height)
  {
    return $zoneWarehouse->where('active', true)->whereHas('zone_position_features', function ($q) use ($fCapacity, $weight) {
      $q->whereHas('feature', function ($query) use ($fCapacity) {
        //TODO:: Capacidad to settings
        $query->where('name', $fCapacity);
        //TODO  comparator from capacity
      })->where('free_value', '>=', $weight);
    })->whereHas('zone_position_features', function ($q) use ($fHeight, $height) {
      $q->whereHas('feature', function ($query) use ($fHeight) {
        //TODO:: Capacidad to settings
        $query->where('name', $fHeight);
        //TODO  comparator from capacity
      })->where('free_value', '>=', $height);
    })->whereNotIn('id', function ($q) {
      $q->select(DB::Raw('ifnull(`zone_position_id`,0)'))->from('wms_stock');
    })->whereNotIn('id', function ($q) {
      $q->select(DB::Raw('ifnull(`zone_position_id`,0)'))->from('wms_suggestions');
    });
  }

  public static function suggestStoragePosition($code128, $companyId, $reference, $document_id, $status, $warehouse_id, $userModel = null)
  {

    $settingsObj = new Settings($companyId);
    $good = $settingsObj->get('good');
    $seconds = $settingsObj->get('seconds');
    $sin_conf = $settingsObj->get('sin_conf');
    $code128helper = EanCode128::with('container.container_features.feature', 'schedule_document.schedule.schedule_receipt')->where('code128', $code128)->where('canceled', false);

    $consult = Product::where('id', $reference)->first();

    $document = DB::table('wms_ean_codes128')
      ->join('wms_suggestions', 'wms_suggestions.code', '=', 'wms_ean_codes128.code128')
      ->select('wms_suggestions.zone_position_id')
      ->get()->toArray();

    $exclude = count($document) > 0 ? array_column($document, "zone_position_id") : [];

    $search_position = ZonePosition::whereHas('zone', function ($q) use ($warehouse_id) {
      $q->where('warehouse_id', $warehouse_id);
    })->where('concept_id', null)->get();
    $array = [];
    foreach ($search_position as $value) {
      $array[] = $value['id'];
    }
    $validate_stock = Stock::whereIn('zone_position_id', $array)->where('product_id', $consult->id)->first();
    if ($validate_stock) {
      $search_position = ZonePosition::find($validate_stock->zone_position_id);
      $suggestion = [
        'code' => $code128,
        'zone_position_id' => $validate_stock->zone_position_id,
        'stored' => false
      ];
      $suggestionName = 'Almacenar: ' . $code128 . ' <hr><strong>Fila:</strong> ' . $search_position->row . '<br> <strong>Nivel:</strong> ' . $search_position->level . '<br> <strong>Móduo:</strong> ' . $search_position->module . '<br> <strong>Position:</strong> ' . $search_position->code;
    } else {
      if ($status === 'good') {
        $concept = ZoneConcept::where('name', $good)->first();
        $search_position = ZonePosition::whereHas('zone', function ($q) use ($warehouse_id) {
          $q->where('warehouse_id', $warehouse_id);
        })->where('concept_id', $concept->id)->get();
        $array = [];
        foreach ($search_position as $value) {
          $array[] = $value['id'];
        }
        $validate_stock = Stock::whereIn('zone_position_id', $array)->where('product_id', $consult->id)->first();
        if ($validate_stock) {
          $search_position = ZonePosition::find($validate_stock->zone_position_id);
          $suggestion = [
            'code' => $code128,
            'zone_position_id' => $validate_stock->zone_position_id,
            'stored' => false
          ];
          $suggestionName = 'Almacenar: ' . $code128 . ' <hr><strong>Fila:</strong> ' . $search_position->row . '<br> <strong>Nivel:</strong> ' . $search_position->level . '<br> <strong>Móduo:</strong> ' . $search_position->module . '<br> <strong>Position:</strong> ' . $search_position->code;
        } else {
          $search_position_null = ZonePosition::whereHas('zone', function ($q) use ($warehouse_id) {
            $q->where('warehouse_id', $warehouse_id);
          })->where('concept_id', $concept->id)
            ->whereNotIn('id', $exclude)
            ->first();
          $suggestion = [
            'code' => $code128,
            'zone_position_id' => $search_position_null->id,
            'stored' => false
          ];
          $suggestionName = 'Almacenar: ' . $code128 . ' <hr><strong>Fila:</strong> ' . $search_position_null->row . '<br> <strong>Nivel:</strong> ' . $search_position_null->level . '<br> <strong>Móduo:</strong> ' . $search_position_null->module . '<br> <strong>Posición:</strong> ' . $search_position_null->code;
        }
      } elseif ($status === 'seconds') {
        $concept = ZoneConcept::where('name', $seconds)->first();

        $search_position = ZonePosition::whereHas('zone', function ($q) use ($warehouse_id) {
          $q->where('warehouse_id', $warehouse_id);
        })->where('concept_id', $concept->id)->get();

        $array = [];
        foreach ($search_position as $value) {
          $array[] = $value['id'];
        }

        $validate_stock = Stock::whereIn('zone_position_id', $array)->where('product_id', $consult->id)->first();

        if ($validate_stock) {
          $search_position = ZonePosition::find($validate_stock->zone_position_id);
          $suggestion = [
            'code' => $code128,
            'zone_position_id' => $validate_stock->zone_position_id,
            'stored' => false
          ];
          $suggestionName = 'Almacenar: ' . $code128 . ' <hr><strong>Fila:</strong> ' . $search_position->row . '<br> <strong>Nivel:</strong> ' . $search_position->level . '<br> <strong>Móduo:</strong> ' . $search_position->module . '<br> <strong>Position:</strong> ' . $search_position->code;
        } else {
          $search_position_null = ZonePosition::whereHas('zone', function ($q) use ($warehouse_id) {
            $q->where('warehouse_id', $warehouse_id);
          })->where('concept_id', $concept->id)
            ->whereNotIn('id', $exclude)
            ->first();

          $suggestion = [
            'code' => $code128,
            'zone_position_id' => $search_position_null->id,
            'stored' => false
          ];
          $suggestionName = 'Almacenar: ' . $code128 . ' <hr><strong>Fila:</strong> ' . $search_position_null->row . '<br> <strong>Nivel:</strong> ' . $search_position_null->level . '<br> <strong>Móduo:</strong> ' . $search_position_null->module . '<br> <strong>Posición:</strong> ' . $search_position_null->code;
        }
      } else {
        $concept = ZoneConcept::where('name', $sin_conf)->first();

        $search_position = ZonePosition::whereHas('zone', function ($q) use ($warehouse_id) {
          $q->where('warehouse_id', $warehouse_id);
        })->where('concept_id', $concept->id)->get();

        $array = [];
        foreach ($search_position as $value) {
          $array[] = $value['id'];
        }

        $validate_stock = Stock::whereIn('zone_position_id', $array)->where('product_id', $consult->id)->first();

        if ($validate_stock) {
          $search_position = ZonePosition::find($validate_stock->zone_position_id);
          $suggestion = [
            'code' => $code128,
            'zone_position_id' => $validate_stock->zone_position_id,
            'stored' => false
          ];
          $suggestionName = 'Almacenar: ' . $code128 . ' <hr><strong>Fila:</strong> ' . $search_position->row . '<br> <strong>Nivel:</strong> ' . $search_position->level . '<br> <strong>Móduo:</strong> ' . $search_position->module . '<br> <strong>Position:</strong> ' . $search_position->code;
        } else {
          $search_position_null = ZonePosition::whereHas('zone', function ($q) use ($warehouse_id) {
            $q->where('warehouse_id', $warehouse_id);
          })->where('concept_id', $concept->id)
          // ->leftjoin("wms_position_features as zpf","zpf.zone_position_id","=","wms_zone_positions.id")
          // ->where("zpf.free_value",">", 0)
          ->select("wms_zone_positions.*")
            // ->whereNotIn('id', $exclude)
            ->first();
          if (empty($search_position_null)) {
            throw new RuntimeException('No se encontro una posicicion para el producto sin confeccionar');
          }

          $suggestion = [
            'code' => $code128,
            'zone_position_id' => $search_position_null->id,
            'stored' => false
          ];
          $suggestionName = 'Almacenar: ' . $code128 . ' <hr><strong>Fila:</strong> ' . $search_position_null->row . '<br> <strong>Nivel:</strong> ' . $search_position_null->level . '<br> <strong>Móduo:</strong> ' . $search_position_null->module . '<br> <strong>Posición:</strong> ' . $search_position_null->code;
        }
      }
    }
    Suggestion::create($suggestion);
    $chargeUserName = $settingsObj->get('cedi_charge');
    // $user = User::whereHas('person.charge', function ($q) use ($chargeUserName) {
    //   $q->where('name', $chargeUserName);
    // })->first();
    // if (empty($user)) {
    //   throw new Exception('not_found_position');
    // }
    $task = [
      'name' => $suggestionName,
      'schedule_type' => ScheduleType::Store,
      'status' => ScheduleStatus::Process,
      'user_id' => $userModel->id,
      'company_id' => $companyId,
      'parent_schedule_id' => $document_id
    ];
    Schedule::create($task);
  }
}
