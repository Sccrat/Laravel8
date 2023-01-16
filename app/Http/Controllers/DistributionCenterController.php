<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\DistributionCenter;
use App\Models\DistributionCenterFeature;
use App\Models\Stock;

use DB;

class DistributionCenterController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $companyId = $request->input('company_id');
        $distCenters = DistributionCenter::where('company_id', $companyId)->with('city')->orderBy('name')->get();
        return $distCenters->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\DistributionCenterRequest $request)
    {
      $data = $request->all();
      $cedi = DistributionCenter::create($data);


      //Delete the warehouse_features
      if(array_key_exists('distribution_center_features', $data)) {
        $cedi->distribution_center_features()->createMany($data['distribution_center_features']);
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
      $machine = DistributionCenter::with('city', 'distribution_center_features.feature')->findOrFail($id);
      $result = $machine->toArray();

      //Get the structures with the nested city
      // $structure = Structure::with('city')->findOrFail($id);
      // $result = $structure->toArray();
      //Get the country name and attach to the nested city
      $countryCode = $result['city']['country_code'];
      $countryName = DB::table('countries')->where('code', '=', $countryCode)->pluck('name');
      $result['city']['country_name'] = $countryName;
      //Get the children

      return $result;
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
      $distCenter = DistributionCenter::findOrFail($id);

      $distCenter->name = array_key_exists('name', $data) ? $data['name'] : $distCenter->name;
      $distCenter->city_id = array_key_exists('city_id', $data) ? $data['city_id'] : $distCenter->city_id;
      $distCenter->address = array_key_exists('address', $data) ? $data['address'] : $distCenter->address;

      //Check if the user want to inactivate or activate
      if(array_key_exists('active', $data) && $data['active'] != $distCenter->active) {
        if(!$data['active']) {
          //Check if is possible to inactivate
          //check  if the distribution center has something on the stock
          $stock = Stock::whereHas('zone_position.zone.warehouse', function ($q) use ($id)
          {
            $q->where('distribution_center_id', $id);
          })->first();

          if(is_null($stock)) {
            //The center can be inactivated
            $distCenter->active = $data['active'];
          } else {
            //Response 409 conflict
            return $this->response->error('distribution_center_cant_inactivate', 409);
          }
        } else {
          $distCenter->active = $data['active'];
        }
      }

      $distCenter->save();

      //Delete the distribution_center_features
      if(array_key_exists('distribution_center_features', $data)) {
        DistributionCenterFeature::where('distribution_center_id', $id)->delete();
        $distCenter->distribution_center_features()->createMany($data['distribution_center_features']);
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
      $distributionCenter = DistributionCenter::findOrFail($id);

      //Check if is possible to inactivate
      //check  if the distribution center has something on the stock
      $stock = Stock::whereHas('zone_position.zone.warehouse', function ($q) use ($id)
      {
        $q->where('distribution_center_id', $id);
      })->first();

      if(is_null($stock)) {
        //The center can be inactivated
        $distributionCenter->delete();
      } else {
        //Response 409 conflict
        return $this->response->error('distribution_center_cant_delete', 409);
      }

      return $this->response->noContent();
    }

    public function getStructure(Request $request, $id)
    {
      //Check if we need capacity
      $capacity = $request->input('capacity');
      $companyId = $request->input('company_id');

      $distCenter = DistributionCenter::with('warehouses.zones.zone_positions')->where('company_id', $companyId);

      if($capacity) {
        //Get the total positions of the cedi
      }

      $distCenter = $distCenter->findOrFail($id);

      return $distCenter->toArray();
    }

    public function getFullStructure(Request $request)
    {
      $companyId = $request->input('company_id');
      $distCenter = DistributionCenter::with('warehouses.zones')->where('company_id', $companyId)->get();

      //$structure = $distCenter->warehouses()->get();

      return $distCenter->toArray();
    }
}
