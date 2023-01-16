<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\WarehouseFeature;
use App\Models\Stock;
use App\Models\User;

class WarehouseController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $companyId = $request->input('company_id');
        $warehouses = Warehouse::with('distribution_center', 'zones.zone_type')->whereHas('distribution_center', function ($q) use ($companyId)
        {
          $q->where('company_id', $companyId);
        })->orderBy('name')->get();
        return $warehouses->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\WarehouseRequest $request)
    {
      $data = $request->all();

      // return $data;
      $warehouse = Warehouse::create($data);

      if(array_key_exists('warehouse_features', $data)) {
        $warehouse->warehouse_features()->createMany($data['warehouse_features']);
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
      $warehouse = Warehouse::with('distribution_center.city', 'warehouse_features.feature')->findOrFail($id);
      return $warehouse->toArray();
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
      $warehouse = Warehouse::findOrFail($id);

      $warehouse->name = array_key_exists('name', $data) ? $data['name'] : $warehouse->name;
      $warehouse->address = array_key_exists('address', $data) ? $data['address'] : $warehouse->address;
      $warehouse->width = array_key_exists('width', $data) ? $data['width'] : $warehouse->width;
      $warehouse->depth = array_key_exists('depth', $data) ? $data['depth'] : $warehouse->depth;
      $warehouse->height = array_key_exists('height', $data) ? $data['height'] : $warehouse->height;
      //$warehouse->active = array_key_exists('active', $data) ? $data['active'] : $warehouse->active;

      if(array_key_exists('active', $data) && $data['active'] != $warehouse->active) {
        if(!$data['active']) {
          //Check if is possible to inactivate
          //check  if the distribution center has something on the stock
          $stock = Stock::whereHas('zone_position.zone', function ($q) use ($id)
          {
            $q->where('warehouse_id', $id);
          })->first();

          if(is_null($stock)) {
            //The center can be inactivated
            $warehouse->active = $data['active'];
          } else {
            //Response 409 conflict
            return $this->response->error('warehouse_cant_inactivate', 409);
          }
        } else {
          $warehouse->active = $data['active'];
        }
      }

      //Delete the warehouse_features
      if(array_key_exists('warehouse_features', $data)) {
        WarehouseFeature::where('warehouse_id', $id)->delete();
        $warehouse->warehouse_features()->createMany($data['warehouse_features']);
      }

      $warehouse->save();

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
      $machine = Warehouse::findOrFail($id);

      //check  if the distribution center has something on the stock
      $stock = Stock::whereHas('zone_position.zone', function ($q) use ($id)
      {
        $q->where('warehouse_id', $id);
      })->first();

      if(is_null($stock)) {
        //The center can be inactivated
        $machine->delete();
      } else {
        //Response 409 conflict
        return $this->response->error('warehouse_cant_delete', 409);
      }

      return $this->response->noContent();
    }

    public function getWarehousesByUserId(Request $request)
    {
        $data = $request->all();

        $user = User::with('person')->where('id',$data['session_user_id'])->where('company_id',$data['company_id'])->first();
        // return $user;
        // if (!$user->person) {
        //   return "mira eso";
        // }
        if (!$user->person || !$user->person->warehouse_id) {
           $warehouses = Warehouse::with('distribution_center', 'zones.zone_type')->orderBy('name')->get();
          return $warehouses->toArray();
        }else {
          
          $warehouses = Warehouse::with('distribution_center', 'zones.zone_type')->orderBy('name')->where('id',$user->person->warehouse_id)->get();
            return $warehouses->toArray();
        }
       
    }

    // public function getByDistributionCenterId($id)
    // {
    //   $warehouses = Warehouse::where('distribution_center_id',$id)->orderBy('name')->get()
    // }
}
