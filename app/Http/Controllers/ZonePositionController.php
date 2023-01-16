<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\ZonePosition;
use App\Models\Zone;
use App\Common\Settings;
use App\Models\PositionFeature;
use App\Enums\SizeKey;
use App\Models\Reserve;
use DB;
use App\Models\Stock;
use App\Models\StockPickingConfig;
use App\Models\StockPickingConfigProduct;
use App\Models\ScheduleCountPosition;
use App\Common\Codes;
use App\Models\ZoneConcept;

class ZonePositionController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $zonePositions = ZonePosition::get();
        return $zonePositions->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Add multiple positions

        //Get the body of the request
        $data = $request->all();
        $companyId = $data['company_id'];

        $positions = ZonePosition::where('zone_id', $data['zone_id'])
        ->get();

        $arr1 = $positions->toArray();

        $zone = Zone::with('warehouse.distribution_center')->findOrFail($data['zone_id']);

        //Get the codes sizes
        $settingsObj = new Settings($companyId);
        $dcSize = $settingsObj->get(SizeKey::DISTRIBUTION_CENTER);
        $wSize = $settingsObj->get(SizeKey::WAREHOUSE);
        $zSize = $settingsObj->get(SizeKey::ZONE);
        $mSize = $settingsObj->get(SizeKey::MODULE);
        $rSize = $settingsObj->get(SizeKey::ROW);
        $lSize = $settingsObj->get(SizeKey::LEVEL);


        $distCode = $zone->warehouse->distribution_center->code;
        if(is_numeric($distCode)) {
          $distCode = sprintf('%0' . $dcSize . 'd', $distCode);
        }

        $warehouseCode = $zone->warehouse->code;
        if(is_numeric($warehouseCode)) {
            $warehouseCode = sprintf('%0' . $wSize . 'd', $warehouseCode);
        }


        $zoneCode = $zone->code;
        if(is_numeric($zoneCode)) {
          $zoneCode = sprintf('%0' . $zSize . 'd', $zoneCode);
        }

        $levelSet = [];
        //Check if have configuration
        $rows = $data['row_to'];
        $levels = $data['level_to'];
        $modules = $data['module_to'];

        $abc = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $positions = strpos($abc, $data['position_to']) + 1;
        // $positions = $zone->positions;
        $code = '';
        for ($r=$data['row_from']; $r <= $rows; $r++) {
          for ($l=$data['level_from']; $l <= $levels; $l++) {
            for ($m=$data['module_from']; $m <= $modules; $m++) {
              for ($p=0; $p < $positions ; $p++) {
                $pos = $abc[$p];
                if($zone->is_secondary) {
                  $code = $distCode . ' ' . $warehouseCode . ' ' . $zoneCode . ' ' . sprintf('%0' . $rSize . 'd', $r) . ' ' . sprintf('%0' . $mSize . 'd', $m) . ' ' . sprintf('%0' . $lSize . 'd', $l) . ' ' . $pos;
                } else {
                  $code = $distCode . ' ' . $warehouseCode . ' ' . sprintf('%0' . $rSize . 'd', $r) . ' ' . sprintf('%0' . $mSize . 'd', $m) . ' ' . sprintf('%0' . $lSize . 'd', $l) . ' ' . $pos;
                }
                // $code = $distCode . ' ' . $warehouseCode . ' ' . sprintf('%0' . $rSize . 'd', $r) . ' ' . sprintf('%0' . $mSize . 'd', $m) . ' ' . sprintf('%0' . $lSize . 'd', $l) . ' ' . $pos;
                $levelSet[] = [
                  'row' => $r,
                  'level' => $l,
                  'module' => $m,
                  'position' => $pos,
                  'zone_id' => $zone->id,
                  'description' => $code,
                  'code' => str_replace(' ', '', $code)
                ];
              }
            }
          }
        }

        $arrdiff = array_udiff($levelSet, $arr1, function ($a, $b)
        {
          return strcmp($a['code'], $b['code']);
        });

        if(array_key_exists('zone_position_features', $data)) {
          foreach ($arrdiff as $row) {
            $zonePosition = ZonePosition::create($row);
            $zonePosition->zone_position_features()->createMany($data['zone_position_features']);

          }
        }

        $update = false;
        //Update
        if($data['row_to'] > $zone->rows) {
          $zone->rows = $data['row_to'];
          $update = true;
        }

        if($data['module_to'] > $zone->modules) {
          $zone->modules = $data['module_to'];
          $update = true;
        }

        if($data['level_to'] > $zone->levels) {
          $zone->levels = $data['level_to'];
          $update = true;
        }

        if($update) {
          $zone->save();
        }

        //Save the new positions
        //ZonePosition::insert($arrdiff);
        return $this->response->created();
        //return $arrdiff->toArray();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $zonePosition =  ZonePosition::with('zone_position_features.feature')->findOrFail($id);
        return $zonePosition->toArray();
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
      $position = ZonePosition::findOrFail($id);

      $settingsObj = new Settings($data['company_id']);
      $good = $settingsObj->get('good');
      $concept = ZoneConcept::where('name',$good)->first();
      $position->active = array_key_exists('active', $data) ? $data['active'] : $position->active;
      $position->concept_id = $concept->id;
      $position->save();

      //Delete the warehouse_features
      if(array_key_exists('zone_position_features', $data)) {
        //TODO: add the left value
        PositionFeature::where('zone_position_id', $id)->delete();
        $position->zone_position_features()->createMany($data['zone_position_features']);
      }

      return $this->response->noContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     //delete delete-picking-config/{id}?$zone_position_id
    public function destroy(Request $request,$id)
    {
      $reasonCodesId = $request->input('reason_codes_id');
      // $reasonCodesId= $data['reason_codes_id'];
        try {
          $pickingConfig = StockPickingConfig::has('zone_position.stocks', '<', 1)->findOrFail($id);
          $pickingConfig->status = false;
          $pickingConfig->reason_codes_id = $reasonCodesId;
          $pickingConfig->save();
          return $this->response->noContent();
        } catch (\Exception $e) {
          return $this->response->error('found_stock', 404);
        }
    }

    public function getZonePositionByZoneId($id)
    {
      $position = ZonePosition::where('zone_id',$id)->get();
      return $position->toArray();
    }

    public function generateCodePosition(Request $request)
    {

      $data = $request->all();
      //fila,modulo,nivel,posicion
      // $zone_id = $data['zone_id'];
      $zone_id = array_key_exists('zone_id', $data) ? $data['zone_id'] : null;
      $fila = array_key_exists('fila', $data) ? $data['fila'] : null;
      $modulo = array_key_exists('modulo', $data) ? $data['modulo'] : null;
      $nivel = array_key_exists('nivel', $data) ? $data['nivel'] : null;
      $posicion = array_key_exists('posicion', $data) ? $data['posicion'] : null;

      // $zone_position_id = $data['zone_position_id'];
      //Consultamos la estrucutra para el tipo de embalaje
      // $structurecodes = Codes::GetStructureCode($packaging_type);
      if ($fila && !$modulo && !$nivel && !$posicion) {
        $structure = ZonePosition::where('zone_id',$zone_id)->where('row',$fila)->get();
      }elseif ($fila && !$modulo && $nivel && !$posicion) {
        $structure = ZonePosition::where('zone_id',$zone_id)->where('row',$fila)->where('level',$nivel)->get();
      }elseif ($fila && $modulo && $nivel && !$posicion) {
        $structure = ZonePosition::where('zone_id',$zone_id)->where('module',$modulo)->where('level',$nivel)->where('row',$fila)->get();
      }elseif ($fila && $modulo && $nivel && $posicion) {
        $structure = ZonePosition::where('zone_id',$zone_id)->where('row',$fila)->where('module',$modulo)->where('level',$nivel)->where('position',$posicion)->first();
      }else {
        $structure = ZonePosition::where('zone_id',$zone_id)->get();
      }
      // return $structure;
      $html = '';
      //Esto falta cogerlo dinámicamente
      DB::transaction(function () use(&$html,$structure, $zone_id,$fila , $modulo , $nivel , $posicion) {

                //Recorremos la estructura y generamos la estrucutra de los códigos IA
                if ($fila && !$modulo && !$nivel && !$posicion) {
                  foreach ($structure as $value) {

                    $newstructurecode = $value['code'];
                    $level = $value['level'];
                    $module = $value['module'];
                    $row = $value['row'];
                    $position = $value['position'];
                    $html .= Codes::getPositionHtml($newstructurecode,$level,$module,$row,$position);
                  }
                }elseif ($fila && !$modulo && $nivel && !$posicion) {
                  foreach ($structure as $value) {

                    $newstructurecode = $value['code'];
                    $level = $value['level'];
                    $module = $value['module'];
                    $row = $value['row'];
                    $position = $value['position'];
                    $html .= Codes::getPositionHtml($newstructurecode,$level,$module,$row,$position);
                  }
                }elseif ($fila && $modulo && $nivel && !$posicion) {
                  foreach ($structure as $value) {

                    $newstructurecode = $value['code'];
                    $level = $value['level'];
                    $module = $value['module'];
                    $row = $value['row'];
                    $position = $value['position'];
                    $html .= Codes::getPositionHtml($newstructurecode,$level,$module,$row,$position);
                  }
                }elseif ($fila && $modulo && $nivel && $posicion) {
                  $newstructurecode = $structure->code;
                  $level = $structure->level;
                  $module = $structure->module;
                  $row = $structure->row;
                  $position = $structure->position;
                  $html .= Codes::getPositionHtml($newstructurecode,$level,$module,$row,$position);
                  // $html .= "<div style='page-break-after: always;' class='breack-page'></div>";
                }else {
                  foreach ($structure as $value) {

                    $newstructurecode = $value['code'];
                    $level = $value['level'];
                    $module = $value['module'];
                    $row = $value['row'];
                    $position = $value['position'];
                    $html .= Codes::getPositionHtml($newstructurecode,$level,$module,$row,$position);
                    // $html .= "<div style='page-break-after: always;' class='breack-page'></div>";
                  }
                }






      });

      if (empty($html)) {
        return $this->response->error('No se generó ningun codigo', 404);
      }

      return  $html;

    }

    public function deleteRange(Request $request, $id)
    {
      $data = $request->all();

      //Check if the range has stock
      $stocks = Stock::with('zone_position')->whereHas('zone_position', function ($q) use ($id, $data)
      {
        $q->where('zone_id', $id)
        ->whereBetween('row', [$data['row_from'], $data['row_to']])
        ->whereBetween('module', [$data['module_from'], $data['module_to']])
        ->whereBetween('level', [$data['level_from'], $data['level_to']])
        ->whereBetween('position', [$data['position_from'], $data['position_to']]);
      })->groupBy('zone_position_id')->get();

      if($stocks->isEmpty()) {
        ZonePosition::where('zone_id', $id)
        ->whereBetween('row', [$data['row_from'], $data['row_to']])
        ->whereBetween('module', [$data['module_from'], $data['module_to']])
        ->whereBetween('level', [$data['level_from'], $data['level_to']])
        ->whereBetween('position', [$data['position_from'], $data['position_to']])
        ->delete();

        return $this->response->noContent();
      } else {
        return $stocks->toArray();
      }
    }

    public function inactivateRange(Request $request, $id)
    {
      $data = $request->all();

      //Check if the range has stock
      $stocks = Stock::with('zone_position')->whereHas('zone_position', function ($q) use ($id, $data)
      {
        $q->where('zone_id', $id)
        ->whereBetween('row', [$data['row_from'], $data['row_to']])
        ->whereBetween('module', [$data['module_from'], $data['module_to']])
        ->whereBetween('level', [$data['level_from'], $data['level_to']])
        ->whereBetween('position', [$data['position_from'], $data['position_to']]);
      })->groupBy('zone_position_id')->get();

      if($stocks->isEmpty() || $data['active']) {

        ZonePosition::where('zone_id', $id)
        ->whereBetween('row', [$data['row_from'], $data['row_to']])
        ->whereBetween('module', [$data['module_from'], $data['module_to']])
        ->whereBetween('level', [$data['level_from'], $data['level_to']])
        ->whereBetween('position', [$data['position_from'], $data['position_to']])
        ->update(['active' => $data['active'], 'concept_id' => $data['concept_id']]);

        return $this->response->noContent();
      } else {
        ZonePosition::where('zone_id', $id)
        ->whereBetween('row', [$data['row_from'], $data['row_to']])
        ->whereBetween('module', [$data['module_from'], $data['module_to']])
        ->whereBetween('level', [$data['level_from'], $data['level_to']])
        ->whereBetween('position', [$data['position_from'], $data['position_to']])
        ->update(['active' => $data['active'], 'concept_id' => $data['concept_id']]);
        
        return $this->response->noContent();
      }

    }

    public function volumeRange(Request $request, $id)
    {
      $data = $request->all();

      $positionsDelete = DB::table('wms_position_features')
                    ->join('wms_zone_positions', 'wms_position_features.zone_position_id', '=', 'wms_zone_positions.id')
                    ->where('wms_zone_positions.zone_id', $id)
                    ->whereBetween('row', [$data['row_from'], $data['row_to']])
                    ->whereBetween('module', [$data['module_from'], $data['module_to']])
                    ->whereBetween('level', [$data['level_from'], $data['level_to']])
                    ->whereBetween('position', [$data['position_from'], $data['position_to']])
                    ->delete();

      $posInsert = DB::table('wms_zone_positions')
                    ->where('zone_id', $id)
                    ->whereBetween('row', [$data['row_from'], $data['row_to']])
                    ->whereBetween('module', [$data['module_from'], $data['module_to']])
                    ->whereBetween('level', [$data['level_from'], $data['level_to']])
                    ->whereBetween('position', [$data['position_from'], $data['position_to']])
                    ->get();

      if(array_key_exists('zone_position_features', $data)) {
        foreach ($posInsert as $row) {
          $zonePositionId = $row->id;
          PositionFeature::where('zone_position_id', $zonePositionId)->delete();
          $position = ZonePosition::findOrFail($zonePositionId);
          $position->zone_position_features()->createMany($data['zone_position_features']);
        }
      }

      //return $posInsert;
      //Delete the warehouse_features


      // ZonePosition::where('zone_id', $id)
      // ->whereBetween('row', [$data['row_from'], $data['row_to']])
      // ->whereBetween('module', [$data['module_from'], $data['module_to']])
      // ->whereBetween('level', [$data['level_from'], $data['level_to']])
      // ->whereBetween('position', [$data['position_from'], $data['position_to']])
      // ->update(['width' => $data['width'], 'height' => $data['height'], 'depth' => $data['depth'], 'weight' => $data['weight']]);
      return $this->response->noContent();
    }

    public function getPositionByCode($code)
    {
      $position = ZonePosition::with('zone.warehouse.distribution_center', 'zone.zone_positions.concept','zone.zone_type')->where('code', $code)->first();
      return $position->toArray();
    }

    public function updateFeatures(Request $request)
    {
      $data = $request->all();
      $value = $data['value'];
      $id = $data['id_feature'];
      $position = PositionFeature::with('zone_position')->where('id', $id)->update(['value' => $value]);

    }

    public function getZoneFeatures($id)
    {
      $zone_features = ZonePosition::with('zone_position_features')->where('id', $id)->first();
      return $zone_features->toArray();
    }

    public function reserveClientPositions(Request $request, $id)
    {
      $data = $request->all();

      $rType = $data['reserve_type'];
      $reserveId = sprintf('%d as reserve_id', $data['reserve_id']);
      $reserveType = sprintf('\'%s\' as reserve_type', $rType);

      $positions = DB::table('wms_zone_positions')->where('zone_id', $id)
      ->whereBetween('row', [$data['row_from'], $data['row_to']])
      ->whereBetween('module', [$data['module_from'], $data['module_to']])
      ->whereBetween('level', [$data['level_from'], $data['level_to']])
      ->whereBetween('position', [$data['position_from'], $data['position_to']])
      ->select('id as position_id', DB::raw($reserveId), DB::raw($reserveType))
      ->whereNotIn('id', function ($q) use($rType) {
        $q->select('position_id')->from('wms_reserves')->where('reserve_type', $rType);
      })
      ->get();
      //->update(['active' => $data['active'], 'concept_id' => $data['concept_id']]);

      //Prepare the data
      foreach ($positions as $pos) {
        $posInsert[] = [
          'position_id' => $pos->position_id,
          'reserved_id' => $pos->reserve_id,
          'reserve_type' => $pos->reserve_type
        ];
      }

      //Insert the array
      if(isset($posInsert)) {
        DB::table('wms_reserves')->insert($posInsert);
      }

      // return $positions;
      return $this->response->noContent();
    }

    public function suggestPositions(Request $request)
    {
      $data = $request->all();

      //Get the content of the pallet
      $positions = ZonePosition::with('zone.warehouse.distribution_center')
                    ->where('zone_id', $data['zone_id'])
                    ->where('active', true)
                    ->whereNotIn('id', function ($q) {
                      $q->select('zone_position_id')->from('wms_suggestions')->where('stored', false);
                    })
                    ->take(10)
                    ->orderBy('row')
                    ->orderBy('level')
                    ->orderBy('module')
                    ->orderBy('position')
                    ->get();
      //Sum the common features

      //Get the features that fit wuth the container features

      //Order the features by client and product type

      //Select all the positions of the zone but order by the suggestion

      return $positions->toArray();
    }
    public function getPositionByScheduleId($id)
    {
       $count = ScheduleCountPosition::with('zone_position')->where('schedule_id', $id)->first();
       return $count->toArray();
    }

    public function getStockByPosition($id)
    {
       $count = Stock::with('product')->where('zone_position_id', $id)->get();
       return $count->toArray();
    }

    public function getPickingConfig($id)
    {
       $config = StockPickingConfig::with('stock_picking_config_product.product.stock')->where('zone_position_id', $id)->where('status', true)->first();
       return $config->toArray();
    }



    public function getPositionsByCode(Request $request)
    {
      $data = $request->all();
      $position = ZonePosition::with('zone.warehouse.distribution_center', 'zone.zone_positions.concept')
      ->whereIn('code', $data['codes'])
      ->get();
      return $position->toArray();
    }

    public function getPositionByZoneId(Request $request)
    {
      $data = $request->all();
      $ids = array_key_exists('ids', $data) ? $data['ids'] : null;
      $position = ZonePosition::from('wms_zone_positions as p')
      ->join('wms_zones as z', 'p.zone_id', 'z.id')
      ->leftjoin('wms_stock as st', 'st.zone_position_id', 'p.id')
      ->whereIn('zone_id',$ids)
      ->where('st.id',null)
      ->select('p.code','z.name')
      ->get();
      return $position->toArray();
    }
}
