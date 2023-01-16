<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\ZonePosition;
use App\Models\Warehouse;
use App\Common\Settings;
use App\Models\ZoneFeature;
use App\Models\MergedPosition;
use App\Enums\SizeKey;
use DB;
use App\Models\Stock;

class ZoneController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $companyId = $request->input('company_id');

      $zones = Zone::with('warehouse.distribution_center', 'zone_type')->whereHas('warehouse.distribution_center', function ($q) use ($companyId)
      {
        $q->where('company_id', $companyId);
      })->get();
      return $zones->toArray();
    }

    public function getAllZonesStorage(Request $request)
    {
      $companyId = $request->input('company_id');
      $data = $request->all();

      if (!empty($data['warehouse'])) {
          $zones = Zone::with('warehouse.distribution_center', 'zone_type');
          $zones->whereHas('zone_type',function ($q)
          {
            $q->where('is_storage', true);
          })->whereHas('warehouse.distribution_center', function ($q) use ($companyId)
          {
            $q->where('company_id', $companyId);
          });

          return $zones->get()->toArray();
      }

      $zones = DB::table('wms_zone_types')
      ->join('wms_zones', 'wms_zones.zone_type_id', '=', 'wms_zone_types.id')
      ->where('wms_zone_types.is_storage', true)
      ->select('*')
      ->orderBy('wms_zones.name');
      $allzones  = $zones->get();
      return $allzones;



    }



    public function getAllZonesPicking(Request $request)
    {
      $companyId = $request->input('company_id');
        // Obtenemos el parametro de configuracion del cargo lider de la bodega
        $settingsObj = new Settings($companyId);

        $pickingTypeName = $settingsObj->get('picking_type');

        if (empty($pickingTypeName)) {
          return [];
        }



        $zones = Zone::with('warehouse.distribution_center', 'zone_type');
        $zones->whereHas('zone_type',function ($q) use ($pickingTypeName)
        {
            $q->where('name', $pickingTypeName);
        });

        return $zones->get()->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\ZoneRequest $request)
    {
      $companyId = $request->input('company_id');
      $data = $request->all();

      //Validar que sea la única principal
      if(!$data['is_secondary']) {
        $zonezini = Zone::where('warehouse_id', $data['warehouse_id'])->where('is_secondary', false)->count();

        if($zonezini > 1) {
          return $this->response->errorBadRequest('zone_multiple_principal');
        }
      }

      $zone = Zone::create($data);

      if(array_key_exists('zone_features', $data)) {
        $zone->zone_features()->createMany($data['zone_features']);
      }

      //If the zone is storage create the positions
      //Get all the is storage
      if(array_key_exists('rows', $data) && array_key_exists('levels', $data) && array_key_exists('modules', $data) && array_key_exists('positions', $data)) {
        $warehouse = Warehouse::with('distribution_center')->findOrFail($data['warehouse_id']);

        //Get the codes sizes
        $settingsObj = new Settings($companyId);
        $wSize = $settingsObj->get(SizeKey::WAREHOUSE);
        $dcSize = $settingsObj->get(SizeKey::DISTRIBUTION_CENTER);
        $zSize = $settingsObj->get(SizeKey::ZONE);
        $mSize = $settingsObj->get(SizeKey::MODULE);
        $rSize = $settingsObj->get(SizeKey::ROW);
        $lSize = $settingsObj->get(SizeKey::LEVEL);

        $distCode = $warehouse->distribution_center->code;
        if(is_numeric($distCode)) {
          $distCode = sprintf('%0' . $dcSize . 'd', $distCode);
        }

        $warehouseCode = $warehouse->code;
        if(is_numeric($warehouseCode)) {
          $warehouseCode = sprintf('%0' . $wSize . 'd', $warehouseCode);
        }

        $zoneCode = $data['code'];
        if(is_numeric($zoneCode)) {
          $zoneCode = sprintf('%0' . $zSize . 'd', $zoneCode);
        }
        $levelSet = [];
        //Check if have configuration
        $rows = $zone->rows;
        $levels = $zone->levels;
        $modules = $zone->modules;
        $positions = $zone->positions;
        $abc = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $code = '';

        for ($r=1; $r <= $rows; $r++) {
          for ($l=1; $l <= $levels; $l++) {
            for ($m=1; $m <= $modules; $m++) {
              for ($p=0; $p < $positions ; $p++) {
                $pos = $abc[$p];
                if($zone->is_secondary) {
                  $code = $distCode . ' ' . $warehouseCode . ' ' . $zoneCode . ' ' . sprintf('%0' . $rSize . 'd', $r) . ' ' . sprintf('%0' . $mSize . 'd', $m) . ' ' . sprintf('%0' . $lSize . 'd', $l) . ' ' . $pos;
                } else {
                  $code = $distCode . ' ' . $warehouseCode . ' ' . sprintf('%0' . $rSize . 'd', $r) . ' ' . sprintf('%0' . $mSize . 'd', $m) . ' ' . sprintf('%0' . $lSize . 'd', $l) . ' ' . $pos;
                }
                $levelSet = [
                  'row' => $r,
                  'level' => $l,
                  'module' => $m,
                  'position' => $pos,
                  'zone_id' => $zone->id,
                  'description' => $code,
                  'code' => str_replace(' ', '', $code)
                ];
                //Create for each level a single insert And add the features
                $zonePosition = ZonePosition::create($levelSet);
                if(array_key_exists('zone_position_features', $data)) {
                  $zonePosition->zone_position_features()->createMany($data['zone_position_features']);
                }
              }
            }
          }
        }
      }

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
      $zone = Zone::with('warehouse.distribution_center', 'zone_positions.concept', 'zone_features.feature')->findOrFail($id);
      return $zone->toArray();
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
      $zone = Zone::findOrFail($id);

      $zone->name = array_key_exists('name', $data) ? $data['name'] : $zone->name;
      $zone->weight = array_key_exists('weight', $data) ? $data['weight'] : $zone->weight;
      $zone->width = array_key_exists('width', $data) ? $data['width'] : $zone->width;
      $zone->height = array_key_exists('height', $data) ? $data['height'] : $zone->height;
      $zone->depth = array_key_exists('depth', $data) ? $data['depth'] : $zone->depth;
      $zone->rows = array_key_exists('rows', $data) ? $data['rows'] : $zone->rows;
      //$zone->active = array_key_exists('active', $data) ? $data['active'] : $zone->active;
      $zone->levels = array_key_exists('levels', $data) ? $data['levels'] : $zone->levels;
      $zone->modules = array_key_exists('modules', $data) ? $data['modules'] : $zone->modules;
      $zone->positions = array_key_exists('positions', $data) ? $data['positions'] : $zone->positions;
      $zone->is_damaged = array_key_exists('is_damaged', $data) ? $data['is_damaged'] : $zone->is_damaged;

      if(array_key_exists('active', $data) && $data['active'] != $zone->active) {
        if(!$data['active']) {
          //Check if is possible to inactivate
          //check  if the distribution center has something on the stock
          $stock = Stock::whereHas('zone_position', function ($q) use ($id)
          {
            $q->where('zone_id', $id);
          })->first();

          if(is_null($stock)) {
            //The center can be inactivated
            $zone->active = $data['active'];
          } else {
            //Response 409 conflict
            return $this->response->error('zone_cant_inactivate', 409);
          }
        } else {
          $zone->active = $data['active'];
        }
      }

      $zone->save();

      //Delete the warehouse_features
      if(array_key_exists('zone_features', $data)) {
        ZoneFeature::where('zone_id', $id)->delete();
        $zone->zone_features()->createMany($data['zone_features']);
      }

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
      $zone = Zone::findOrFail($id);

      $stock = Stock::whereHas('zone_position', function ($q) use ($id)
      {
        $q->where('zone_id', $id);
      })->first();

      if(is_null($stock)) {
        //The center can be inactivated
        $zone->delete();
      } else {
        //Response 409 conflict
        return $this->response->error('zone_cant_delete', 409);
      }


      $zone->delete();

      return $this->response->noContent();
    }

    public function getZonesByWarehouse($id)
    {
      $zones = Zone::where('warehouse_id', $id)->get();
      return $zones->toArray();
    }

    public function getPositionsByWarehouse(Request $request, $warehouseId, $zoneId)
    {
      $companyId = $request->input('company_id');
      $zone = Zone::with('warehouse.distribution_center', 'zone_positions.concept')->where('warehouse_id', $warehouseId)->whereHas('zone_type', function ($q)
      {
        return $q->where('is_storage', true);
      })->whereHas('warehouse.distribution_center', function ($q) use ($companyId)
      {
        $q->where('company_id', $companyId);
      });

      if($zoneId != 0) {
        $zone->where('id', $zoneId);
      }

      $result = $zone->get();

      $result = $result->toArray();

      foreach ($result as $keyZone => $zone) {
        foreach ($zone['zone_positions'] as $key => $value) {
            $merge = MergedPosition::with('zone_position_from','zone_position_to')->where('from_position_id', '<=',$value['id'])->
                                                    where('to_position_id', '>=' ,$value['id'])->
                                                    orderBy('to_position_id', 'desc')->first();
            $result[$keyZone]['zone_positions'][$key]['merge_position'] = $merge;
        }
      }
      return $result;
    }
}
