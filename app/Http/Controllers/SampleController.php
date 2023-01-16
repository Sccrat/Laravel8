<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Samples;
use App\Models\StockTransition;
use App\Models\ZoneConcept;
use App\Models\ZonePosition;
use App\Models\StockMovement;
use App\Models\Stock;
use App\Models\Product;
use App\Models\EanCode14;
use DB;

class SampleController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
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
        $return  = [];
        DB::transaction(function () use($data, &$return) {

            $sample = Samples::create($data);

            ///Get the zoneid from warehouse
            $warehouseId = $data['warehouse_id'];
            $zonePositionId = ZonePosition::whereHas('zone', function ($q) use ($warehouseId)
            {
              $q->where('warehouse_id', $warehouseId);
            })->first();

            $sample->sampleDetail()->createMany($data['sampleDetail']);

            foreach ($sample->sampleDetail as $key => $value) {
                $value->ean14->quanty = (int)$value->ean14->quanty - (int)$value->quanty;
                $value->ean14->save();

                foreach ($value->ean14->detail as $key2 => $detail) {
                  if ($detail->product_id === $value->product_id) {
                    $detail->quanty = (int)$detail->quanty - (int)$value->quanty;
                    $detail->save();
                    break;
                  }
                }
                
                //Get receipt zone position
                $objTransition = [
                'product_id'=>$value->product_id,
                'zone_position_id'=>$zonePositionId->id,
                //'zone_position_id'=>'',
                'code128_id'=>'',
                'code14_id'=>$value->package_ean14_id,
                'quanty'=>$value->quanty,
                // TODO: Agregar enum a la action
                'action'=>'income',
                'concept'=>'inspection',
                'user_id'=>$data['session_user_id']
                ];

                StockTransition::create($objTransition);
            }
            $return = $sample;
        });

        return $return;
    }

    public function relocate(Request $request)
    {
        $data = $request->all();

        $return  = [];

        $isEan14 = array_key_exists('isEan14', $data)?$data['isEan14']:false;

        if ($isEan14) {
            DB::transaction(function () use($data, &$return) {

                $ean14Code = array_key_exists('ean14', $data) ? $data['ean14'] : NULL;
                $packagingType = array_key_exists('packaging_type', $data) ? $data['packaging_type'] : NULL;
                $codeInput = array_key_exists('codeunity', $data) ? $data['codeunity'] : NULL;
                $quantyRemove = 1;

                $zonepconcept = ZoneConcept::where('is_storage',true)->where('active',true)->first();
                if(!isset($zonepconcept))
                {
                    return $this->response->error('storage_pallet_zone_concept_no_found', 404);
                }

                $product = Product::where('ean', $codeInput)->first();
                if(!isset($product))
                {
                  return $this->response->error('storage_pallet_product_no_found', 404);
                }


                $code14find = EanCode14::where('code14', $ean14Code)->first();

                if(!isset($code14find))
                {
                 return $this->response->error('storage_pallet_code14_no_found', 404);
                }


                $findTransition = StockTransition::with(
                  'product','zone_position.zone'
                  ,'ean128','ean14','ean13'
                  )->where('product_id',$product['id'])
                   ->where('concept',"inspection")->first();

                $datatransition = [];
                if (!empty($findTransition)) {

                  $findStock = Stock::with(
                  'product','zone_position.zone'
                  ,'ean128','ean14','ean13'
                  )->where('product_id',$product['id'])
                    ->where('code14_id',$code14find['id'])
                    ->where('quanty','>',0)
                    ->first();

                  if (!empty($findStock)) {

                     $findStock->quanty += $quantyRemove;
                     $findTransition->quanty -= $quantyRemove;


                      // Crea el registro del movimiento
                      $stockMovement = [

                      'product_id'=>$findTransition['product_id'],
                      'product_reference'=>$findTransition['product']['reference'],
                      'product_ean'=>$findTransition['product']['ean'],
                      'product_quanty'=>$quantyRemove,

                      'zone_position_code'=>$findStock['zone_position']['code'],

                      'code128'=>$findStock['ean128']['code128'],
                      'code14'=>$findStock['ean14']['code14'],
                      'username'=>$data['user']['username'],

                      'warehouse_id'=>$findStock['zone_position']['zone']['warehouse_id'],
                      // TODO: Agregar enum a la action
                      'action'=>'income',
                      'concept'=>$findTransition['concept']
                      ];

                      StockMovement::create($stockMovement);
                      $findStock->save();

                      if ($findTransition->quanty == 0) {
                        $findTransition->delete();
                      }else{
                        $findTransition->save();
                      }

                      $return = $findStock;
                  }else{
                      return $this->response->error('storage_pallet_ean14_no_found_sample', 404);
                  }
                }else{
                  return $this->response->error('storage_pallet_product_no_found_inspeccion', 404);
                }
            });
        }else{
            DB::transaction(function () use($data, &$return) {

                $positionCode = array_key_exists('position', $data) ? $data['position'] : NULL;
                $packagingType = array_key_exists('packaging_type', $data) ? $data['packaging_type'] : NULL;
                $codeInput = array_key_exists('codeunity', $data) ? $data['codeunity'] : NULL;

                $quantyRemove = 1;

                $zonepconcept = ZoneConcept::where('is_storage',true)->where('active',true)->first();
                if(!isset($zonepconcept))
                {
                    return $this->response->error('storage_pallet_zone_concept_no_found', 404);
                }



                $findposition = ZonePosition::where('code', $positionCode)->first();

                if(empty($findposition))
                {
                  return $this->response->error('storage_pallet_position_no_found', 404);
                }
                //Validamos que la posición se encuentre disponible para almacenar
                if(!$findposition['active'])
                {
                  return $this->response->error('storage_pallet_position_unavailable', 404);
                }


                $findTransition = StockTransition::with(
                  'product','zone_position.zone'
                  ,'ean128','ean14','ean13'
                  )->where('concept',"inspection");

                $product = Product::where('ean', $codeInput)->first();
                $ean14Search = false;
                if(!isset($product))
                {
                  $code14find = EanCode14::where('code14', $codeInput)->first();
                  if (!empty($code14find)) {
                    $findTransition->where('code14_id',$code14find->id);
                    $ean14Search = true;
                  }else{
                    return $this->response->error('storage_pallet_product_no_found', 404);
                  }
                }else{
                  $findTransition->where('product_id',$product['id']);
                }

                $transitionArr = [];
                if (!$ean14Search) {
                  $transitionArr[] = $findTransition->first();
                }else{
                  $transitionArr = $findTransition->get();                  
                }

                foreach ($transitionArr as $key => $findTransition) {
                  $datatransition = [];
                  if (!empty($findTransition)) {

                    if ($ean14Search) {
                      $quantyRemove = $findTransition['quanty'];
                      $findTransition['quanty'] = 0;
                    }else{
                      $findTransition['quanty'] -= 1;
                    }

                    // Inserta los registros de transicion a la tabla de stock
                    $objStock = [
                    'product_id'=>$findTransition['product_id'],
                    'zone_position_id'=>$findposition['id'],
                    'code128_id'=>$findTransition['code128_id'],
                    'code14_id'=>$findTransition['code14_id'],
                    'quanty'=>$quantyRemove,
                    'active'=>1
                    ];

                    // Crea el registro del movimiento
                    $stockMovement = [
                    'product_id'=>$findTransition['product_id'],
                    'product_reference'=>$findTransition['product']['reference'],
                    'product_ean'=>$findTransition['product']['ean'],
                    'product_quanty'=>$quantyRemove,

                    'zone_position_code'=>$findposition['code'],

                    'code128'=>$findTransition['ean128']['code128'],
                    'code14'=>$findTransition['ean14']['code14'],
                    'username'=>$data['user']['username'],

                    'warehouse_id'=>$findposition->zone->warehouse_id,
                    // TODO: Agregar enum a la action
                    'action'=>'income',
                    'concept'=>$findTransition['concept']
                    ];

                    StockMovement::create($stockMovement);
                    $storedObje = Stock::create($objStock);

                    $findposition->concept_id = $zonepconcept['id'];
                    $findposition->save();

                    if ($findTransition->quanty == 0) {
                          $findTransition->delete();
                    }else{
                          $findTransition->save();
                    }

                    $return = $storedObje;
                  }else{
                    return $this->response->error('storage_pallet_product_no_found_inspeccion', 404);
                  }
                }
            });

        }


        return $return;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
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
}
