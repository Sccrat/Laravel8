<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Machine;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Enums\Status;
use App\Models\MachineFeature;
use App\Models\WarehouseFeature;
use DB;

class MachineController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $type = $request->input('type');
      $cedi = $request->input('cedi');
      $companyId = $request->input('company_id');
      $machines = Machine::with('zone','machine_type', 'warehouse', 'distribution_center', 'person')->where('company_id', $companyId);

      if(isset($type)) {
        $machines = $machines->whereHas('machine_type', function ($query) use ($type, $cedi)
        {
          return $query->where('name', urldecode($type));
        })->where('distribution_center_id', $cedi);
      }

      $machines = $machines->orderBy('name')->get();
        // $machines = Machine::with('zone','machine_type', 'warehouse', 'distribution_center', 'person')->orderBy('name')->get();
        // return $machines->toArray();

        // $machine = Machine::with('warehouse','machine_type')
        // ->where('warehouse_id', $id)
        // ->orderBy('name')
        // ->get();
        return $machines->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\MachineRequest $request)
    {
      $data = $request->all();
      // return $data;

      $this->ValidateFeatures($data['warehouse_id'], $data['machine_features']);

      $machine = Machine::create($data);

      //Add the machine features
      if(array_key_exists('machine_features', $data)) {
        $machine->machine_features()->createMany($data['machine_features']);
      }

      return $this->response->created();
    }

    private function ValidateFeatures($warehouseId, $machineFeatures)
    {
      //Get all the features
      $wFeatures = WarehouseFeature::with('feature')->where('warehouse_id', $warehouseId)->get();
      $match = true;
      //Validate features against warehouse
      foreach ($machineFeatures as $feature) {
        //Search for the feature inside the $wFeatures
        $fId = $feature['feature_id'];
        $wcode = $wFeatures->filter(function ($f) use ($fId) {
          return $f->feature_id == $fId;
        })->first();

        //If there is a match compare the properties
        if(!empty($wcode)) {
          //Eval the code
          $theCode = '(' .$feature['value']. ' ' . $wcode->comparation . ' ' . $wcode->value .')';
          eval("\$match = ".$theCode.";");

          if(!$match) {
            return $this->response->error('feature_invalid|' . $wcode->feature->name . '|' . $wcode->comparation . '|' . $wcode->value, 400);
          }
        }
      }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $machine = Machine::with('zone.warehouse', 'machine_features.feature')->findOrFail($id);
        return $machine->toArray();
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
      $machine = Machine::findOrFail($id);

      if(array_key_exists('machine_features', $data)) {
        $this->ValidateFeatures($machine->warehouse_id, $data['machine_features']);
      }

      $machine->name = array_key_exists('name', $data) ? $data['name'] : $machine->name;
      $machine->responsable_id = array_key_exists('responsable_id', $data) ? $data['responsable_id'] : $machine->responsable_id;
      $machine->code = array_key_exists('code', $data) ? $data['code'] : $machine->code;
      $machine->description = array_key_exists('description', $data) ? $data['description'] : $machine->description;
      $machine->status = array_key_exists('status', $data) ? $data['status'] : $machine->status;
      $machine->zone_id = array_key_exists('zone_id', $data) ? $data['zone_id'] : $machine->zone_id;
      $machine->width = array_key_exists('width', $data) ? $data['width'] : $machine->width;
      $machine->depth = array_key_exists('depth', $data) ? $data['depth'] : $machine->depth;
      $machine->weight = array_key_exists('weight', $data) ? $data['weight'] : $machine->weight;
      $machine->height = array_key_exists('height', $data) ? $data['height'] : $machine->height;
      $machine->warehouse_id = array_key_exists('warehouse_id', $data) ? $data['warehouse_id'] : $machine->warehouse_id;
      $machine->distribution_center_id = array_key_exists('distribution_center_id', $data) ? $data['distribution_center_id'] : $machine->distribution_center_id;
      $machine->machine_type_id = array_key_exists('machine_type_id', $data) ? $data['machine_type_id'] : $machine->machine_type_id;

      $machine->save();

      //Delete the warehouse_features
      if(array_key_exists('machine_features', $data)) {
        MachineFeature::where('machine_id', $id)->delete();
        $machine->machine_features()->createMany($data['machine_features']);
      }

      return $this->response->noContent();
      //return $machine->toArray();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $machine = Machine::findOrFail($id);
        $machine->status = Status::Inactive;
        $machine->save();

        return $this->response->noContent();
    }

    public function getMachinesByZone($id)
    {
      $machine = Machine::with('zone','distribution_center','warehouse','person','machine_type')
      ->with('machine_type')
      ->where('zone_id',$id)
      ->orderBy('name')
      ->get();
      return $machine->toArray();
    }

    public function getMachinesByDistributionCenter($id)
    {
      $machine = Machine::with('distribution_center','person','machine_type')
      ->where('distribution_center_id', $id)
      ->whereNull('warehouse_id')
      ->whereNull('zone_id')
      ->orderBy('name')
      ->get();
      return $machine->toArray();
    }

    public function getMachinesByWarehouse($id)
    {
      $machine = Machine::with('warehouse','distribution_center','machine_type')
      ->where('warehouse_id', $id)
      ->whereNull('zone_id')
      ->orderBy('name')
      ->get();
      return $machine->toArray();
    }


    public function getAllMachinesByWarehouse($id)
    {
      $machine = Machine::with('warehouse','machine_type')
      ->where('warehouse_id', $id)
      ->orderBy('name')
      ->get();
      return $machine->toArray();
    }

    public function getMachinesGroupCharge(Request $request, $id)
    {

      $type = $request->input('type');
      // personal por bodega
      $warehouse = DB::table('wms_machines')
      ->join('wms_machine_types', 'wms_machine_types.id', '=', 'wms_machines.machine_type_id')
      ->join('wms_warehouses', 'wms_warehouses.id', '=', 'wms_machines.warehouse_id')
      ->where('wms_machines.distribution_center_id', $id)
      ->whereNotNull('warehouse_id')
      ->whereNull('zone_id')
      ->select(DB::raw('"warehouse" as type'),'wms_machines.warehouse_id','wms_machines.distribution_center_id','wms_machines.zone_id','machine_type_id','wms_machine_types.name as machine_type_name','wms_warehouses.name as name',DB::raw('COUNT(machine_type_id) as total'))
      ->orderBy('name','machine_type_name')
      ->groupBy('machine_type_id', 'warehouse_id');

      if (isset($type)) {
        $warehouse = $warehouse->where('wms_machines.machine_type_id',$type);
      }

      // personal por zona
      $zone = DB::table('wms_machines')
      ->join('wms_machine_types', 'wms_machine_types.id', '=', 'wms_machines.machine_type_id')
      ->join('wms_zones', 'wms_zones.id', '=', 'wms_machines.zone_id')
      ->where('distribution_center_id', $id)
      ->whereNotNull('wms_machines.warehouse_id')
      ->whereNotNull('wms_machines.zone_id')
      ->select(DB::raw('"zone" as type'),'wms_machines.warehouse_id','wms_machines.distribution_center_id','wms_machines.zone_id','machine_type_id','wms_machine_types.name as machine_type_name','wms_zones.name as name',DB::raw('COUNT(machine_type_id) as total'))
      ->orderBy('name','machine_type_name')
      ->groupBy('machine_type_id', 'warehouse_id', 'zone_id');

      if (isset($type)) {
        $zone = $zone->where('wms_machines.machine_type_id',$type);
      }

      $cedi = DB::table('wms_machines')
      ->join('wms_machine_types', 'wms_machine_types.id', '=', 'wms_machines.machine_type_id')
      ->join('wms_distribution_centers', 'wms_distribution_centers.id', '=', 'wms_machines.distribution_center_id')
      ->where('distribution_center_id', $id)
      ->whereNull('warehouse_id')
      ->whereNull('zone_id')
      ->unionAll($warehouse)->unionAll($zone)
      ->select(DB::raw('"cedi" as type'),'wms_machines.warehouse_id','wms_machines.distribution_center_id','wms_machines.zone_id','machine_type_id','wms_machine_types.name as machine_type_name','wms_distribution_centers.name as name',DB::raw('COUNT(machine_type_id) as total'))
      // ->orderBy('name','machine_type_name')
      ->groupBy('machine_type_id');

      if (isset($type)) {
        $cedi = $cedi->where('wms_machines.machine_type_id',$type);
      }

      $machines = $cedi->get();
      return $machines;
    }
}
