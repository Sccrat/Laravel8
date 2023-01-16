<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\EanCode128;
use App\Models\EanCode14;
use App\Models\Pallet;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockTransition;
use App\Common\Settings;
use App\Models\ZonePosition;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\ScheduleTransform;
use App\Models\ScheduleTransformDetail;
use App\Models\ScheduleTransformResult;
use App\Models\ScheduleTransformResultPackaging;
use App\Models\ScheduleUnjoinDetail;
use App\Models\User;
use DB;
use App\Common\StockFunctions;
use App\Enums\PackagingType;
use App\Enums\ScheduleAction;
use App\Enums\ScheduleType;
use App\Enums\ScheduleStatus;
use App\Models\ContainerFeature;
use App\Enums\SettingsKey;
use Log;
use App\Models\MergedPosition;
use App\Models\ScheduleTransition;
use App\Models\StockPickingConfig;
use App\Models\StockPickingConfigProduct;
use App\Common\UserCommon;
use App\Models\ZoneConcept;
use App\Models\ProgressTask;
use App\Models\ProductEan14;
use App\Models\EnlistProducts;
use App\Models\DocumentDetail;
use App\Models\Eancodes14Packing;
use DateTimeZone;
use Carbon\Carbon;

class RelocateController extends BaseController
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
        //
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

    public function relocateRemove(Request $request)
    {
        $data = $request->all();

        $positionCode = array_key_exists('position', $data) ? $data['position'] : null;
        $packagingType = array_key_exists('packaging_type', $data) ? $data['packaging_type'] : null;
        $packagingProduct = array_key_exists('packaging', $data) ? $data['packaging'] : null;
        $codeInput = array_key_exists('codeunity', $data) ? $data['codeunity'] : null;
        $session_user_id = array_key_exists('session_user_id', $data) ? $data['session_user_id'] : null;
        $scheduleId = array_key_exists('schedule_id', $data) ? $data['schedule_id'] : null;
        $produc_Id = array_key_exists('product_id', $data) ? $data['product_id'] : null;
        $is_secondary = array_key_exists('is_secondary', $data) ? $data['is_secondary'] : null;
        $code14 = array_key_exists('code14', $data) ? $data['code14'] : null;
        $taskRemoveEnlistid = array_key_exists('taskRemoveEnlistid', $data) ? $data['taskRemoveEnlistid'] : null;
        $taskRemoveid = array_key_exists('taskRemoveid', $data) ? $data['taskRemoveid'] : null;
        $sugerencia_acon = array_key_exists('sugerencia_acon', $data) ? $data['sugerencia_acon'] : null;
        $sugerencia_semi = array_key_exists('sugerencia_semi', $data) ? $data['sugerencia_semi'] : null;
        $sugerencia_sin = array_key_exists('sugerencia_sin', $data) ? $data['sugerencia_sin'] : null;
        $companyId = array_key_exists('company_id', $data) ? $data['company_id'] : null;

        $username = UserCommon::getUsernameById($session_user_id);

        $dataRes = [];
        $findcode128 = EanCode128::where('code128', $codeInput)->first();
        if ($packagingType == PackagingType::Logistica) {
            if (!isset($findcode128)) {
                return $this->response->error('storage_pallet_code128_no_found', 404);
            }
            // DB::transaction(function () use ($findcode128,$positionCode,&$dataRes,$session_user_id,$username, $scheduleId,$codeInput,$produc_Id,$is_secondary,$code14,$sugerencia_acon,$sugerencia_semi,$sugerencia_sin,$taskRemoveEnlistid,$taskRemoveid,$companyId) {
                //Buscamos la posición de origen
                
                if (isset($taskRemoveEnlistid)) {
                    // return 5;

                    $settingsObj = new Settings($companyId);
                    $position = $settingsObj->get('stock_zone');
                    // return 5;
                    // $array = ['220','100'];
                    //$codeInput-$sugerencia_acon-$sugerencia_semi-$sugerencia_sin
                    $search128 = EanCode128::where('code128', $codeInput)->first();
                    
                    //buscamos que el 128 haga parte de alguno de los arreglos
                    if ($search128) {
                        
                        if (in_array($search128->id, $sugerencia_sin)) {
                            // return $search128;
                            $findposition = ZonePosition::with('zone_position_features.feature')->where('code', $positionCode)->first();

                            if (empty($findposition)) {
                                return $this->response->error('storage_pallet_position_source_no_found', 404);
                            }

                            $findStock = Stock::with('product','zone_position.zone','ean128','ean14','ean13')
                            ->whereHas('zone_position.zone', function ($query) use ($position) {
                                $query->where('name',$position);
                            })
                            ->where('code128_id', $findcode128['id'])
                            ->where('zone_position_id', $findposition['id'])->get()->toArray();

                            // return $findStock;

                            

                            $person = User::with('person')->where('id',$session_user_id)->first();
                            // return $this->response->error('storage_pallet_13_source_no_found'.$person->person->warehouse_id, 404);
               

                            // Consulta si la posicion se encuentra compuesta por mas posiciones
                            // $mergedPosition = MergedPosition::where('code128', $findcode128['id'])->first();



                            $datatransition = [];
                            if (!empty($findStock)) {

                        // Se recorre cada caja del pallet y se retira una a una insertando cada caja en transicion y en movimientos

                                foreach ($findStock as $key => $value) {
                                    // Inserta los registros del stock a la tabla de transicion
                                    $objTransition = [
                            'product_id'=>$value['product_id'],
                            'zone_position_id'=>$value['zone_position_id'],
                            'code128_id'=>$value['code128_id'],
                            'code_ean14'=>$value['code_ean14'],
                            'quanty'=>$value['quanty'],
                            // TODO: Agregar enum a la action
                            'action'=>'output',
                            'concept'=>'dispatch',
                            'warehouse_id'=>$value['zone_position']['zone']['warehouse_id'],
                            'user_id'=>$session_user_id,
                            'document_detail_id'=>$value['document_detail_id'],
                            'quanty_14'=>$value['quanty_14']
                          ];



                          $StockTransition =  StockTransition::create($objTransition);
                          $parent = Schedule::where('id',$taskRemoveEnlistid)->first();
                          $ScheduleTransition = [
                          'transition_id' => $StockTransition->id,
                          'schedule_id' => $parent->parent_schedule_id
                          ];

                          $sheduleTransition=  ScheduleTransition::create($ScheduleTransition);


                                    // Crea el registro del movimiento
                                    $stockMovement = [
                            'product_id'=>$value['product_id'],
                            'product_reference'=>$value['product']['reference'],
                            'product_ean'=>$value['product']['ean'],
                            'product_quanty'=>$value['quanty'],
                            'zone_position_code'=>$value['zone_position']['code'],
                            'code128'=>$value['ean128']['code128'],
                            'code_ean14'=>$value['code_ean14'],
                            'username'=>$username,
                            'warehouse_id'=>$value['zone_position']['zone']['warehouse_id'],
                            // TODO: Agregar enum a la action
                            'action'=>'output',
                            'concept'=>'dispatch'
                          ];
                                    array_push($datatransition, $stockMovement);

                                    StockMovement::create($stockMovement);

                                }


                                // Borra los registros del stock
                                $stockDeleted = Stock::where('code128_id', $findcode128['id'])
                        ->where('zone_position_id', $findposition['id'])->delete();



                              //limpio la posicion
                            // StockFunctions::removeVolumeFromPosition($findcode128['id'], $findposition['id'],$companyId);
                            // return $this->response->error('si lo encontre', 404);

                            EanCode128::where('id', $value['code128_id'])->update(['weight' => null , 'height' => null]);

                            } else {
                                return $this->response->error('storage_pallet_stock_not_found', 404);
                            }
                            $dataRes =  $datatransition;

                        } else {
                            return $this->response->error('msn_alert_error_pallet', 404);
                        }
                    } else {
                        return $this->response->error('msn_alett_not_pallet', 404);
                    }
                }
            //     elseif (isset($taskRemoveid) && !isset($taskRemoveEnlistid)) {
            //         return 1;
            //       $search128 = EanCode128::where('code128', $codeInput)->first();

            //       //buscamos que el 128 haga parte de alguno de los arreglos
            //       if ($search128) {

            //           if (in_array($search128->id, $sugerencia_acon) || in_array($search128->id, $sugerencia_semi) || in_array($search128->id, $sugerencia_sin)) {
            //               // return $this->response->error('si lo encontre', 404);
            //               $findposition = ZonePosition::with('zone_position_features.feature')->where('code', $positionCode)->first();

            //               if (empty($findposition)) {
            //                   return $this->response->error('storage_pallet_position_source_no_found', 404);
            //               }

            //               $findStock = Stock::with(
            //           'product',

            //           'zone_position.zone',

            //           'ean128',

            //           'ean14',

            //           'ean13'
            //         )->where('code128_id', $findcode128['id'])
            //         ->where('zone_position_id', $findposition['id'])->get()->toArray();

            //               // Consulta si la posicion se encuentra compuesta por mas posiciones
            //               $mergedPosition = MergedPosition::where('code128', $findcode128['id'])->first();

            //               $datatransition = [];
            //               if (!empty($findStock)) {

            //           // Se recorre cada caja del pallet y se retira una a una insertando cada caja en transicion y en movimientos

            //                   foreach ($findStock as $key => $value) {
            //                       // Inserta los registros del stock a la tabla de transicion
            //                       $objTransition = [
            //               'product_id'=>$value['product_id'],
            //               'zone_position_id'=>$value['zone_position_id'],
            //               'code128_id'=>$value['code128_id'],
            //               'code_ean14'=>$value['code_ean14'],
            //               'quanty'=>$value['quanty'],
            //               // TODO: Agregar enum a la action
            //               'action'=>'output',
            //               'concept'=>'picking',
            //               'warehouse_id'=>$value['zone_position']['zone']['warehouse_id'],
            //               'user_id'=>$session_user_id,
            //               'document_detail_id'=>$value['document_detail_id'],
            //               'quanty_14'=>$value['quanty_14']
            //             ];


            //             $StockTransition =  StockTransition::create($objTransition);
            //             $parent = Schedule::where('id',$taskRemoveid)->first();
            //             $ScheduleTransition = [
            //             'transition_id' => $StockTransition->id,
            //             'schedule_id' => $parent->parent_schedule_id
            //             ];

            //             $sheduleTransition=  ScheduleTransition::create($ScheduleTransition);

            //             //           // Crea el registro del movimiento
            //             //           $stockMovement = [
            //             //   'product_id'=>$value['product_id'],
            //             //   'product_reference'=>$value['product']['reference'],
            //             //   'product_ean'=>$value['product']['ean'],
            //             //   'product_quanty'=>$value['quanty'],
            //             //   'zone_position_code'=>$value['zone_position']['code'],
            //             //   'code128'=>$value['ean128']['code128'],
            //             //   'code_ean14'=>$value['ean14']['code_ean14'],
            //             //   'username'=>$username,
            //             //   'warehouse_id'=>$value['zone_position']['zone']['warehouse_id'],
            //             //   // TODO: Agregar enum a la action
            //             //   'action'=>'output',
            //             //   'concept'=>'picking'
            //             // ];
            //             //           array_push($datatransition, $stockMovement);

            //             //           StockMovement::create($stockMovement);
            //                   }


            //                   // Borra los registros del stock
            //                   $stockDeleted = Stock::where('code128_id', $findcode128['id'])
            //           ->where('zone_position_id', $findposition['id'])->delete();

            //                 //limpio la posicion
            //               StockFunctions::removeVolumeFromPosition($findcode128['id'], $findposition['id']);

            //               EanCode128::where('id', $value['code128_id'])->update(['weight' => null , 'height' => null]);

            //               } else {
            //                   return $this->response->error('storage_pallet_stock_not_found', 404);
            //               }
            //               $dataRes =  $datatransition;

            //           } else {
            //               return $this->response->error('msn_alert_error_pallet', 404);
            //           }
            //       } else {
            //           return $this->response->error('msn_alett_not_pallet', 404);
            //       }
            //     } elseif (!isset($scheduleId) && !isset($taskRemoveEnlistid)) {
            //         return 2;
            //         $findposition = ZonePosition::with('zone_position_features.feature')->where('code', $positionCode)->first();

            //         if (empty($findposition)) {
            //             return $this->response->error('storage_pallet_position_source_no_found', 404);
            //         }

            //         $findStock = Stock::with(
            //     'product',

            //     'zone_position.zone',

            //     'ean128',

            //     'ean14',

            //     'ean13'
            //   )->where('code128_id', $findcode128['id'])
            //   ->where('zone_position_id', $findposition['id'])->get()->toArray();

            //         // Consulta si la posicion se encuentra compuesta por mas posiciones
            //         $mergedPosition = MergedPosition::where('code128', $findcode128['id'])->first();

            //         $datatransition = [];
            //         if (!empty($findStock)) {

            //     // Se recorre cada caja del pallet y se retira una a una insertando cada caja en transicion y en movimientos

            //             foreach ($findStock as $key => $value) {
            //                 // Inserta los registros del stock a la tabla de transicion
            //                 $objTransition = [
            //         'product_id'=>$value['product_id'],
            //         'zone_position_id'=>$value['zone_position_id'],
            //         'code128_id'=>$value['code128_id'],
            //         'code_ean14'=>$value['code_ean14'],
            //         'quanty'=>$value['quanty'],
            //         // TODO: Agregar enum a la action
            //         'action'=>'output',
            //         'concept'=>'picking',
            //         'warehouse_id'=>$value['zone_position']['zone']['warehouse_id'],
            //         'user_id'=>$session_user_id,
            //         'document_detail_id'=>$value['document_detail_id'],
            //         'quanty_14'=>$value['quanty_14']
            //       ];

            //                 if (isset($scheduleId)) {
            //                     $find13 = Stock::whereIn('product_id', $produc_Id)
            //         ->where('code128_id', $value['code128_id'])
            //         ->whereHas('zone_position.zone', function ($q) {
            //             $q->where('is_secondary', false);
            //         })
            //         ->get();

            //                     if (count($find13)) {
            //                         $StockTransition =  StockTransition::create($objTransition);
            //                     } else {
            //                         return $this->response->error('storage_pallet_13_source_no_found', 404);
            //                     }
            //                 } else {
            //                     $StockTransition =  StockTransition::create($objTransition);
            //                 }


            //                 if (isset($scheduleId)) {
            //                     $ScheduleTransition = [
            //           'transition_id' => $StockTransition->id,
            //           'schedule_id' => $scheduleId
            //         ];

            //                     $sheduleTransition=  ScheduleTransition::create($ScheduleTransition);
            //                 }


            //                 // Crea el registro del movimiento
            //     //             $stockMovement = [
            //     //     'product_id'=>$value['product_id'],
            //     //     'product_reference'=>$value['product']['reference'],
            //     //     'product_ean'=>$value['product']['ean'],
            //     //     'product_quanty'=>$value['quanty'],
            //     //     'zone_position_code'=>$value['zone_position']['code'],
            //     //     'code128'=>$value['ean128']['code128'],
            //     //     'code_ean14'=>$value['ean14']['code_ean14'],
            //     //     'username'=>$username,
            //     //     'warehouse_id'=>$value['zone_position']['zone']['warehouse_id'],
            //     //     // TODO: Agregar enum a la action
            //     //     'action'=>'output',
            //     //     'concept'=>'picking'
            //     //   ];
            //     //             array_push($datatransition, $stockMovement);

            //     //             StockMovement::create($stockMovement);
            //             }


            //             // Borra los registros del stock
            //             $stockDeleted = Stock::where('code128_id', $findcode128['id'])
            //     ->where('zone_position_id', $findposition['id'])->delete();


            //             // Se debe validar las posiciones que se intervinieron en la transaccion, para verificar si despues del movimiento siguen ocupadas o si ya quedan libres

            //             // se prepara un arreglo para las tratar las posiciones intervenidas
            //             $positionsPallet = [];
            //             // Se ingresa por defecto la posicion inicial o la posicion real ocupada
            //             array_push($positionsPallet, $findposition);

            //             // Se evalua si existen posiciones compuestas, y de ser asi se remplaza la posicion original por las posiciones compuestas
            //             if (!empty($mergedPosition)) {
            //                 if ($mergedPosition->id > 0) {
            //                     $findpositionsMerged = ZonePosition::whereBetween('id', array(
            //           $mergedPosition->from_position_id,
            //           $mergedPosition->to_position_id))->get();


            //                     if (!empty($findpositionsMerged)) {
            //                         $positionsPallet = $findpositionsMerged;
            //                         $mergedPosition->delete();
            //                     }
            //                 }
            //             }

            //             //Decrease weight
            //             // $cFeatures = ContainerFeature::where('container_id', $findcode128['container_id'])->get();
            //             //Compare the Capacidad (kg) againts $weight
            //             $settingsObj = new Settings($companyId);
            //             $fCapacity = $settingsObj->get(SettingsKey::FEATURE_CAPACITY);
            //             $hCapacity = $settingsObj->get(SettingsKey::FEATURE_HEIGHT);

            //             $totalPositions = count($positionsPallet);
            //             foreach ($positionsPallet as $key => $position) {
            //                 $weight = $findcode128['weight'] / $totalPositions;
            //                 $height = $findcode128['height'];
            //                 //For each container feature reduce the position feature
            //                 // foreach ($cFeatures as $feature) {
            //                 foreach ($position->zone_position_features as $fPos) {
            //                     //Decrement the capacity
            //                     if ($fPos->feature->name == $fCapacity) {
            //                         $fPos->increment('free_value', $weight);
            //                     // break;
            //                     } elseif ($fPos->feature->name == $hCapacity) {
            //                         $fPos->increment('free_value', $height);
            //                         // break;
            //                     }
            //                 }
            //                 // }

            //                 // Habilita la posicion
            //                 $stockByPosition = StockFunctions::findStockByPosition($position['id']);
            //                 $count = count($stockByPosition);
            //                 // $count = Stock::where('zone_position_id', $position['id'])->count();
            //                 if ($count <= 0) {
            //                     //Cambiamos el estado de la posición para indicar que se encuentra libre
            //                     //TODO : Se comenta mientras se conoce bn la validación de cuando una posición esta totalmente ocupada o libre según las caracterízticas
            //                     $position->concept_id = null;
            //                     $position->save();
            //                 }
            //             }
            //         } else {
            //             return $this->response->error('storage_pallet_stock_not_found', 404);
            //         }
            //         $dataRes =  $datatransition;
            //     } else {
            //         return $this->response->error('pallet_null_picking', 404);
            //     }
            // });
        } elseif ($packagingType == PackagingType::Empaque) {
            $ean14_largo = substr($codeInput,2,14);
            $code14find = ProductEan14::where('code_ean14', $ean14_largo)->first();
            // return $code14find; 
            if (!isset($code14find)) {
                return $this->response->error('storage_pallet_code14_no_found', 404);
            }
            DB::transaction(function () use ($code14find,$positionCode,&$dataRes,$session_user_id,$username, $scheduleId,$codeInput,$produc_Id,$is_secondary,$code14,$taskRemoveEnlistid) {

              //Buscamos la posición de origen
                $findposition = ZonePosition::where('code', $positionCode)->first();

                if (empty($findposition)) {
                    return $this->response->error('storage_pallet_code14_no_found', 404);
                }

                $findStock = Stock::with(
                'product',
                  'zone_position.zone',
                  'ean128',
                  'ean14',
                  'ean13'
                )->where('code_ean14', $code14find['code_ean14'])
                ->where('zone_position_id', $findposition['id'])->get()->toArray();

                $datatransition = [];
                if (!empty($findStock)) {
                    foreach ($findStock as $key => $value) {

                    // Inserta los registros del stock a la tabla de transicion
                        $objTransition = [
                      'product_id'=>$value['product_id'],
                      'zone_position_id'=>$value['zone_position_id'],
                      'code128_id'=>$value['code128_id'],
                      'code_ean14'=>$value['code_ean14'],
                      'quanty'=>$value['quanty'],
                      // TODO: Agregar enum a la action
                      'action'=>'output',
                      'concept'=>'dispatch',
                      'warehouse_id'=>$value['zone_position']['zone']['warehouse_id'],
                      'user_id'=>$session_user_id,
                      'document_detail_id'=>$value['document_detail_id'],
                      'quanty_14'=>$value['quanty_14']
                    ];

                        if (isset($scheduleId)) {

                      // $validatePositionPicking = Stock::where('code14_id',$value['code14_id'])->where('zone_position_id',$findposition->id)
                            // ->get();
                            //
                            // foreach ($validatePositionPicking as $value) {
                            //
                            //   $productValidate = Product::where('id',$value->product_id)->first();
                            //   $sum = $productValidate->min_stock + $productValidate->stock_secure;
                            //
                            //   if($value->quanty >= $sum )
                            //   {
                            //     return $this->response->error('product_picking_capacity', 404);
                            //   }
                            // }

                            $erase_pallet = Pallet::where('code_ean14', $value['code_ean14'])->delete();

                            $find13 = Stock::whereIn('product_id', $produc_Id)
                      ->where('code_ean14', $value['code_ean14'])
                      // ->whereHas('zone_position.zone', function ($q) {
                      //   $q->where('is_secondary', false);
                      // })
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

                            $sheduleTransition=  ScheduleTransition::create($ScheduleTransition);
                        }

                        if (isset($taskRemoveEnlistid)) {
                            return $this->response->error('insert_pallet', 404);
                        }


                        // Crea el registro del movimiento
                        $stockMovement = [
                      'product_id'=>$value['product_id'],
                      'product_reference'=>$value['product']['reference'],
                      'product_ean'=>$value['product']['ean'],
                      'product_quanty'=>$value['quanty'],
                      'zone_position_code'=>$value['zone_position']['code'],
                      'code128'=>$value['ean128']['code128'],
                      'code_ean14'=>$value['code_ean14'],
                      'username'=>$username,
                      'warehouse_id'=>$value['zone_position']['zone']['warehouse_id'],
                      // TODO: Agregar enum a la action
                      'action'=>'output',
                      'concept'=>'picking'
                    ];
                        array_push($datatransition, $stockMovement);

                        StockMovement::create($stockMovement);
                    }


                    // Busco la caja en schedule_transition para borrarla (stock-relocate-schedule)
                    ScheduleTransition::whereHas('stock', function ($q) use ($code14find) {
                        $q->where('code_ean14', $code14find['code_ean14']);
                    })->delete();


                    // Borra los registros del stock
                    $stockDeleted = Stock::where('code_ean14', $code14find['code_ean14'])
                  ->where('zone_position_id', $findposition['id'])->delete();
                    // Habilita la posicion
                    // $count = Stock::where('zone_position_id', $findposition['id'])->count();
                    $stockByPosition = StockFunctions::findStockByPosition($findposition['id']);
                    $count = count($stockByPosition);
                    if ($count <= 0) {
                        //Cambiamos el estado de la posición para indicar que se encuentra libre
                        //TODO : Se comenta mientras
                        $findposition->concept_id = null;
                        $findposition->save();
                    }
                } else {
                    return $this->response->error('storage_pallet_stock_not_found', 404);
                }
                $dataRes =  $datatransition;
            });
        } elseif ($packagingType == PackagingType::Producto) {
            $product = Product::where('ean', $codeInput)->first();

            if (!isset($product)) {
                return $this->response->error('storage_pallet_product_no_found', 404);
            }
            DB::transaction(function () use ($product,$positionCode,$packagingProduct,&$dataRes,$session_user_id,$username, $scheduleId,$codeInput,$produc_Id,$is_secondary,$code14) {

                //Buscamos la posición de origen
                $findposition = ZonePosition::where('code', $positionCode)->first();

                if (!$findposition) {
                    return $this->response->error('storage_pallet_product_no_found', 404);
                }

                $verify = Stock::where('zone_position_id', $findposition->id)->where('code14_id', null)->where('product_id', $code14)->first();

                if (!$verify) {
                    $code14find = EanCode14::where('code14', $packagingProduct)->first();

                    if (!$code14find) {
                        return $this->response->error('storage_packaging_product_no_found', 404);
                    }



                    $findStock = Stock::with(
                    'product',
                      'zone_position.zone',
                      'ean128',
                      'ean14',
                      'ean13'
                    )->where('product_id', $code14)
                    ->where('code14_id', $code14find->id)
                    ->where('quanty', '>', 0)
                    ->where('zone_position_id', $findposition->id)
                    ->first();
                } else {
                    $findStock = Stock::with(
                    'product',
                      'zone_position.zone',
                      'ean128',
                      'ean14',
                      'ean13'
                    )->where('product_id', $code14)
                    ->where('code14_id', null)
                    ->where('quanty', '>', 0)
                    ->where('zone_position_id', $findposition->id)
                    ->first();
                }





                $datatransition = [];
                if ($findStock) {
                    if (!$verify) {
                        $find13 = Stock::
                    where('code14_id', $code14find->id)->where('zone_position_id', $findposition->id)
                    ->get();


                        if (count($find13)) {
                            $findStock->decrement('quanty', 1);
                        } else {
                            return $this->response->error('storage_pallet_13_source_no_found', 404);
                        }
                    } else {
                        $find13 = Stock::
                    where('product_id', $code14)->where('zone_position_id', $findposition->id)
                    ->get();


                        if (count($find13)) {
                            $findStock->decrement('quanty', 1);
                        } else {
                            return $this->response->error('storage_pallet_13_source_no_found', 404);
                        }
                    }

                    // $findStock->quanty -= 1;


                    if (!$verify) {
                        $stockeancode14 = Stock::where('product_id', $code14)->where('code14_id', $code14find->id)->where('zone_position_id', $findposition->id)->first();
                    } else {
                        $stockeancode14 = Stock::where('product_id', $code14)->where('code14_id', null)->where('zone_position_id', $findposition->id)->first();
                    }

                    if ($stockeancode14) {
                        if ($stockeancode14->quanty===0) {
                            // $verify = Stock::where('zone_position_id',$findposition->id)->where('code14_id',null)->where('product_id',$code14)->first();
                            //
                            if (!$verify) {
                                $transitionDeleted = Stock::where('product_id', $code14)->where('code14_id', $code14find->id)->where('zone_position_id', $findposition->id)
                          ->delete();
                            } else {
                                $transitionDeleted = Stock::where('product_id', $code14)->where('code14_id', null)->where('zone_position_id', $findposition->id)
                            ->delete();
                            }
                        }
                    }


                    // Inserta los registros del stock a la tabla de transicion
                    $objTransition = [
                      'product_id'=>$findStock['product_id'],
                      'zone_position_id'=>$findStock['zone_position_id'],
                      // 'code128_id'=>$findStock['code128_id'],
                      // 'code14_id'=>$findStock['code14_id'],
                      'quanty'=>1,
                      // TODO: Agregar enum a la action
                      'action'=>'output',
                      'concept'=>'picking',
                      'warehouse_id'=>$findStock['zone_position']['zone']['warehouse_id'],
                      'user_id'=>$session_user_id,
                    ];

                    // Crea el registro del movimiento
                    $stockMovement = [
                      'product_id'=>$findStock['product_id'],
                      'product_reference'=>$findStock['product']['reference'],
                      'product_ean'=>$findStock['product']['ean'],
                      'product_quanty'=>1,
                      'zone_position_code'=>$findStock['zone_position']['code'],
                      'code128'=>$findStock['ean128']['code128'],
                      'code14'=>$findStock['ean14']['code14'],
                      'username'=>$username,
                      'warehouse_id'=>$findStock['zone_position']['zone']['warehouse_id'],
                      // TODO: Agregar enum a la action
                      'action'=>'output',
                      'concept'=>'picking'
                    ];

                    StockMovement::create($stockMovement);

                    $picking= StockTransition::where('product_id', $findStock['product_id'])->where('code14_id', null)->first();

                    if ($picking) {
                        $picking->increment('quanty', 1);
                    } else {
                        $StockTransition =  StockTransition::create($objTransition);
                        if (isset($scheduleId)) {
                            $ScheduleTransition = [
                          'transition_id' => $StockTransition->id,
                          'schedule_id' => $scheduleId
                        ];

                            $sheduleTransition=  ScheduleTransition::create($ScheduleTransition);
                        }
                    }






                    $stockByPosition = StockFunctions::findStockByPosition($findposition['id']);
                    $count = count($stockByPosition);
                    if ($count <= 0) {
                        //Cambiamos el estado de la posición para indicar que se encuentra libre
                        //TODO : Se comenta mientras
                        $findposition->concept_id = null;
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
        $positionCode = array_key_exists('position', $data) ? $data['position'] : null;
        $positionsCode = array_key_exists('positions', $data) ? $data['positions'] : null;
        $packagingType = array_key_exists('packaging_type', $data) ? $data['packaging_type'] : null;
        $codeInput = array_key_exists('codeunity', $data) ? $data['codeunity'] : null;
        $scheduleId = array_key_exists('schedule_id', $data) ? $data['schedule_id'] : null;
        $scheduleIdTransform = array_key_exists('schedule_id_transform', $data) ? $data['schedule_id_transform'] : null;
        $produc_Id = array_key_exists('product_id', $data) ? $data['product_id'] : null;
        $code14 = array_key_exists('code14', $data) ? $data['code14'] : null;
        $warehouseId = array_key_exists('warehouse_id', $data) ? $data['warehouse_id'] : null;
        $multiple = array_key_exists('multiple', $data) ? $data['multiple'] : null;
        $codeInput13 = array_key_exists('codeunity13', $data) ? $data['codeunity13'] : null;
        $isEan14 = array_key_exists('isEan14', $data) ? $data['isEan14'] : false;
        $ean14Code = array_key_exists('ean14', $data) ? $data['ean14'] : null;
        $reprocessScheduleTargetId = array_key_exists('reprocess_schedule_target_id', $data) ? $data['reprocess_schedule_target_id'] : null;
        $taskrelocateEnlistid = array_key_exists('taskrelocateEnlistid', $data) ? $data['taskrelocateEnlistid'] : null;
        $taskrelocateid = array_key_exists('taskrelocateid', $data) ? $data['taskrelocateid'] : null;
        $sugerencia_acon = array_key_exists('sugerencia_acon', $data) ? $data['sugerencia_acon'] : null;
        $sugerencia_semi = array_key_exists('sugerencia_semi', $data) ? $data['sugerencia_semi'] : null;
        $sugerencia_sin = array_key_exists('sugerencia_sin', $data) ? $data['sugerencia_sin'] : null;

        $warehouse_array = array_key_exists('warehouse_array', $data) ? $data['warehouse_array'] : null;

        $relocateAction = array_key_exists('relocateAction', $data) ? $data['relocateAction'] : null;
        $actionTask = array_key_exists('actionTask', $data) ? $data['actionTask'] : null;


        $session_user_id = array_key_exists('session_user_id', $data) ? $data['session_user_id'] : null;
        $companyId = array_key_exists('company_id', $data) ? $data['company_id'] : null;

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
        $findcode128 = EanCode128::where('code128', $codeInput)->first();
        if ($packagingType == PackagingType::Logistica) {
            if (!isset($findcode128)) {
                return $this->response->error('storage_pallet_code128_no_found', 404);
            }
            // DB::transaction(function () use ($findcode128,$positionsCode,$zonepconcept,$username,$produc_Id,$scheduleId,$code14,$warehouseId,$codeInput,$multiple,$ean14Code,$scheduleIdTransform,$taskrelocateEnlistid,$sugerencia_acon,$sugerencia_semi,$sugerencia_sin,$companyId) {



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

                // Si viene por transformar, me salteo todo esto porque la posición donde se transforma es de concepto 'área de trabajo'
                if (empty($scheduleIdTransform)) {
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
                            } elseif ($fPos->feature->name == $hCapacity) {
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
                        $findposition = ZonePosition::where('code', $code)->first();
                        $posId = $findposition->id;

                        //Check from and to positions (range)
                        if ($minId == 0) {
                            $minId = $posId;
                            $maxId = $posId;
                        } else {
                            if ($posId < $minId) {
                                $minId = $posId;
                            } elseif ($posId > $maxId) {
                                $maxId = $posId;
                            }
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

                    //Cambiamos el estado de la posición para indicar que se encuentra ocupada
                    // $findposition->concept_id = $zonepconcept['id'];
                    ZonePosition::whereBetween('id', [$minId, $maxId])->update(['concept_id' => $zonepconcept['id']]);
                }

                //Buscamos la posición en la q se quiere almacenar
                if ($minId > 0) {
                    $findposition = ZonePosition::find($minId);
                } else {
                    $findposition = ZonePosition::with('zone')->where('code', $positionsCode[0]['codePosition'])->first();
                }


                if (empty($findposition)) {
                    return $this->response->error('storage_pallet_position_source_no_found', 404);
                }

                if (!empty($scheduleIdTransform)) {
                    // Me traigo el zone_concept_id de 'Área de Trabajo'
                    $settingsObj = new Settings();
                    $WorkAreaConcept = $settingsObj->get('zone_concept_work_area');
                    $WorkAreaConceptId = ZoneConcept::where('name', $WorkAreaConcept)->first()->id;
                }

                //Validamos que la posición se encuentre disponible para almacenar
                // Si es de transformar, la posición deberia ser de concepto 'Área de trabajo' nomás
                if (!empty($scheduleIdTransform) && $findposition['concept_id'] != $WorkAreaConceptId) {
                    return $this->response->error('storage_position_not_reprocess_zone_position', 404);
                }

                if (empty($scheduleIdTransform) && !$findposition['active']) {
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
                          'product_id'=>$value['product_id'],
                          'zone_position_id'=>$findposition['id'],
                          'code128_id'=>$value['code128_id'],
                          'code_ean14'=>$value['code_ean14'],
                          'quanty'=>$value['quanty'],
                          'active'=>1,
                          'document_detail_id'=>$value['document_detail_id'],
                          'quanty_14'=>$value['quanty_14']
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
                          'product_id'=>$value['product_id'],
                          'product_reference'=>$value['product']['reference'],
                          'product_ean'=>$value['product']['ean'],
                          'product_quanty'=>$value['quanty'],
                          'zone_position_code'=>$findposition['code'],
                          'code128'=>$value['ean128']['code128'],
                          'code_ean14'=>$value['ean14']['code_ean14'],
                          'username'=>$username,
                          'warehouse_id'=>$value['zone_position']['zone']['warehouse_id'],
                          // TODO: Agregar enum a la action
                          'action'=>'income',
                          'concept'=>'relocate'
                        ];

                        StockMovement::create($stockMovement);
                    }

                    // Actualizo este valor sólo si la tarea NO es de transformar.
                    // Porque, sino, a la posición de transformar (concept_id = área de trabajo) le cambia el concepto a 'almacenamiento'
                    if (empty($scheduleIdTransform)) {
                        $findposition->concept_id = $zonepconcept['id'];
                    }
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
            // });
        } elseif ($packagingType == PackagingType::Empaque) {
            // BUscar y validar la caja contra la tabla de transicion
            $ean14_largo = $codeInput;
            $code14find = ProductEan14::with('stock', 'document_detail')->where('code_ean14', $ean14_largo)->first();
            // return $code14find['code_ean14'];

            if (!isset($code14find)) {
                return $this->response->error('storage_pallet_code14_no_found', 404);
            }

            $dataRes = '';
            // DB::transaction(function () use ($code14find,$positionCode,&$dataRes,$zonepconcept,$username,$produc_Id,$scheduleId,$code14,$warehouseId,$codeInput,$multiple,$scheduleIdTransform,$codeInput13,$ean14Code, $reprocessScheduleTargetId,$taskrelocateEnlistid,$warehouse_array,$taskrelocateid,$relocateAction,$session_user_id,$actionTask,$companyId) {

                      //Buscamos la posición de origen
                $findposition = ZonePosition::where('code', $positionCode)->first();

                // $settingsObj = new Settings($companyId);
                // $PickingName = $settingsObj->get('picking_type');

                // $findpositionPicking = ZonePosition::with('zone.zone_type')
                //       ->where('code', $positionCode)
                //       ->whereHas('zone.zone_type', function ($query) use ($PickingName) {
                //           $query->where('name', $PickingName);
                //       })
                //       ->first();

                if (!$findposition) {
                    return $this->response->error('storage_pallet_position_target_no_found', 404);
                }



                    $transitionProduct = StockTransition::where('code_ean14', $code14find->code_ean14)->first();
                  
                if ($relocateAction !== 'condition' && $relocateAction !== 'semi_condition' && $relocateAction !== 'without_conditioning') {
                  $findTransition = StockTransition::with(
                          'product',
                            'zone_position.zone',
                            'ean128',
                            'ean14',
                            'ean13'
                          )->where('code_ean14', $code14find['code_ean14'])->get()->toArray();
                        //   return $findTransition;
                }else {
                  $settingsObj = new Settings($companyId);
                  $position = $settingsObj->get('transit_receive');

                  $person = User::with('person')->where('id',$session_user_id)->first();
                  // return $this->response->error('storage_pallet_13_source_no_found'.$person->person->warehouse_id, 404);
                  $findTransition = Stock::with('product','ean14','ean13','ean128')->with('zone_position.zone')
                  ->whereHas('zone_position.zone', function ($query) use ($person,$position) {
                      $query->where('warehouse_id', $person->person->warehouse_id)->where('name',$position);
                  })
                  ->where('code_ean14', $code14find['code_ean14'])
                  ->get();

                  // return $this->response->error('mira ome'.$findTransition, 404);

                }


                if (!empty($findTransition)) {
                    $isMine = false;
                    $created = false;
                    foreach ($findTransition as $key => $value) {

                            // Insertar la informacion en Stock con la nueva informacion de posicion

                        $objStock = [
                              'product_id'=>$value['product_id'],
                              'zone_position_id'=>$findposition->id,
                              'code128_id'=>$value['code128_id'],
                              'code_ean14'=>$value['code_ean14'],
                              'quanty'=>$value['quanty'],
                              'concept'=>'dispatch',
                              'active'=>1,
                            //   'quanty_14'=>$value['quanty_14'],
                              'document_detail_id'=>$value['document_detail_id'],
                              'quanty_14'=>$value['quanty_14']
                            ];

                        $objStockMultiple = [
                              'product_id'=>$value['product_id'],
                              'zone_position_id'=>$findposition->id,
                              'code128_id'=>$value['code128_id'],
                              'code_ean14'=>$value['code_ean14'],
                              'concept'=>'dispatch',
                              'quanty'=>1,
                              'active'=>1,
                            ];
                      if (isset($taskrelocateEnlistid)) {
                        //   return 'entro';
                          $settingsObj = new Settings($companyId);
                          $position = $settingsObj->get('dispatch_zone');
                          
                          $position_transit = ZonePosition::with('zone')
                          ->whereHas('zone', function ($query) use ($position) {
                              $query->where('name',$position);
                            })
                            ->where('id',$findposition->id)
                            ->first();
                            
                            
                            
                            if ($position_transit) {
                                
                                $verify = Stock::where('code_ean14',$value['code_ean14'])->where('code128_id',$value['code128_id'])->where('zone_position_id',$position_transit->id)->first();

                                $schedule = Schedule::where('id',$taskrelocateEnlistid)->first();

                                $enlist = EnlistProducts::with('product_ean14','document')->where('schedule_id',$schedule->parent_schedule_id)->where('code_ean14',$value['code_ean14'])->first();
                                $objStock['quanty_14'] = $enlist->quanty;
                                $objStock['quanty'] = $enlist->quanty*$enlist->product_ean14->quanty;

                                $detail = DocumentDetail::where('document_id',$enlist->document->id)->where('code_ean14',$value['code_ean14'])->first();
                                // return $this->response->error('mira ome '.$verify, 404);
                                
                                // if ($verify) {

                                // // Stock::create($objStock);
                                //     // $delete = StockTransition::where('code_ean14',$value['code_ean14'])->delete();
                                // }else {
                                    // $objStock['code128_id'] = null;
                                    $inventario = Stock::create($objStock);
                                    $obj = [
                                        "document_id"=>$enlist->document->id,
                                        "code_ean14"=>$value['code_ean14'],
                                        "code128_id"=>$value['code128_id'],
                                        "quanty_14"=>$objStock['quanty_14'],
                                        "stock_id"=>$inventario['id']
                                    ];
                                    
                                    Eancodes14Packing::create($obj);
                                    
                                    $delete = StockTransition::where('code_ean14',$value['code_ean14'])->where('concept','dispatch')->first();
                                    $delete->decrement('quanty_14',$objStock['quanty_14']);
                                    $delete->decrement('quanty',$objStock['quanty']);
                                    
                                    $transition_stock = StockTransition::where('code_ean14',$value['code_ean14'])->where('concept','dispatch')->first();
                                    if ($transition_stock) {
                                        $objStock_transition = [
                                            'product_id'=>$transition_stock['product_id'],
                                            'zone_position_id'=>$transition_stock['zone_position_id'],
                                            'code128_id'=>$transition_stock['code128_id'],
                                            'code_ean14'=>$transition_stock['code_ean14'],
                                            'quanty'=>$transition_stock['quanty'],
                                            'concept'=>'dispatch',
                                            'active'=>1,
                                            // 'quanty_14'=>$transition_stock['quanty_14'],
                                            'document_detail_id'=>$transition_stock['document_detail_id'],
                                            'quanty_14'=>$transition_stock['quanty_14']
                                        ];
                                        
                                        // return $objStock_transition;
                                        Stock::create($objStock_transition);
                                        // return $objStock_transition;
                                        $transition_stock_delete = StockTransition::where('code_ean14',$value['code_ean14'])->where('concept','dispatch')->delete();
                                    }
                                    
                                    // return $delete;
                                // }
                          // Crea el registro del movimiento

                          $stockMovement = [
                          'product_id'=>$value['product_id'],
                          'product_reference'=>$value['product']['reference'],
                          'product_ean'=>$value['product']['ean'],
                          'product_quanty'=>$value['quanty'],
                          'zone_position_code'=>$findposition['code'],
                          'code128'=>$value['ean128']['code128'],
                          'code_ean14'=>$value['code_ean14'],
                          'username'=>$username,
                          'warehouse_id'=>$value['warehouse_id'],
                          // TODO: Agregar enum a la action
                          'action'=>'income',
                          'concept'=>'relocate'
                        ];

                          StockMovement::create($stockMovement);
                          $task = Schedule::where('id',$taskrelocateEnlistid)->first();
                          $validate = ProgressTask::where('schedule_id',$taskrelocateEnlistid)->first();
                          if ($validate) {
                          $validate->increment('quanty',$value['quanty']);
                          }else {
                          $progress = [
                          'schedule_id'=>$taskrelocateEnlistid,
                          'quanty'=>$value['quanty']

                          ];
                          ProgressTask::create($progress);
                        }

                          // $transitionDeleted = StockTransition::with(
                          // 'product',
                          //   'zone_position.zone',
                          //   'ean128',
                          //   'ean14',
                          //   'ean13'
                          // )
                          // ->where('code14_id', $code14find['id'])
                          // ->delete();
                          // $drop = wms_ean_codes128::where()->first()

                        }else {
                          return $this->response->error('storage_pallet_13_source_no_found', 404);
                        }

                        // $transitorio = Stock::with('zone_position.zone')
                        // ->whereHas('zone_position.zone', function ($query) use ($position) {
                        //     $query->where('name',$position);
                        // })
                        // ->first();

                      }elseif ( $relocateAction === 'condition' || $relocateAction === 'semi_condition' || $relocateAction === 'without_conditioning') {

                          // return $this->response->error('gva si entro ', 404);
                        // $findStock = StockTransition::with('product','zone_position.zone','ean128','ean14','ean13')
                        // ->where('code14_id', $code14find['id'])->get()->toArray();

                        $settingsObj = new Settings();
                        $position = $settingsObj->get('transit_receive');

                        $WorkAreaConcept = $settingsObj->get('zone_concept_work_area');
                        $WorkAreaConceptId = ZoneConcept::where('name', $WorkAreaConcept)->first()->id;

                        $position_transit = ZonePosition::where('concept_id', $WorkAreaConceptId)->where('id',$findposition->id)->first();
                        // return $this->response->error('mira ome'.$position_transit, 404);
                        // $position_transit = ZonePosition::with('zone')
                        // ->whereHas('zone', function ($query) use ($position,$warehouse_array) {
                        //     $query->where('name',$position)->whereIn('warehouse_id',$warehouse_array);
                        // })
                        // ->where('id',$findposition->id)
                        // ->first();

                        if ($position_transit) {

                          // Crea el registro del movimiento
                          $ve = Stock::create($objStock);
                          // return $this->response->error('mira ome'.$ve, 404);

                          $stockMovement = [
                          'product_id'=>$value['product_id'],
                          'product_reference'=>$value['product']['reference'],
                          'product_ean'=>$value['product']['ean'],
                          'product_quanty'=>$value['quanty'],
                          'zone_position_code'=>$findposition['code'],
                          'code128'=>$value['ean128']['code128'],
                          'code14'=>$value['ean14']['code14'],
                          'username'=>$username,
                          'warehouse_id'=>$person->person->warehouse_id,
                          // TODO: Agregar enum a la action
                          'action'=>'income',
                          'concept'=>'relocate'
                        ];

                          StockMovement::create($stockMovement);

                          $transitionDeleted = Stock::where('code14_id', $code14)
                          ->whereHas('zone_position.zone', function ($query) use ($position) {
                              $query->where('name',$position);
                          })
                          ->delete();
                          // $drop = wms_ean_codes128::where()->first()
                          $task = Schedule::where('id',$actionTask)->first();
                          $validate = ProgressTask::where('schedule_id',$task->id)->first();
                          if ($validate) {
                          $validate->increment('quanty',$value['quanty']);
                          }else {
                          $progress = [
                          'schedule_id'=>$task->id,
                          'quanty'=>$value['quanty']

                          ];
                          ProgressTask::create($progress);
                        }

                        }else {
                          return $this->response->error('not_position_picking', 404);
                        }
                      }else if ($relocateAction === 'dispatch') {

                        // return $this->response->error('gva si entro ', 404);
                        $person = User::with('person')->where('id',$session_user_id)->first();
                        $settingsObj = new Settings();
                        $position = $settingsObj->get('dispatch');


                        $position_transit = ZonePosition::with('zone')
                        ->whereHas('zone', function ($query) use ($position) {
                            $query->where('name',$position);
                        })
                        ->where('id',$findposition->id)->first();


                        // return $this->response->error('mira ome'.$position_transit, 404);
                        if ($position_transit) {

                          // Crea el registro del movimiento
                          $ve = Stock::create($objStock);
                          // return $this->response->error('mira ome'.$ve, 404);

                          $stockMovement = [
                          'product_id'=>$value['product_id'],
                          'product_reference'=>$value['product']['reference'],
                          'product_ean'=>$value['product']['ean'],
                          'product_quanty'=>$value['quanty'],
                          'zone_position_code'=>$findposition['code'],
                          'code128'=>$value['ean128']['code128'],
                          'code14'=>$value['ean14']['code14'],
                          'username'=>$username,
                          'warehouse_id'=>$person->person->warehouse_id,
                          // TODO: Agregar enum a la action
                          'action'=>'income',
                          'concept'=>'relocate'
                        ];

                          StockMovement::create($stockMovement);

                          $transitionDeleted = StockTransition::where('code14_id', $code14)->delete();
                          // $drop = wms_ean_codes128::where()->first()

                        }else {
                          return $this->response->error('not_position_picking', 404);
                        }
                      }
                      elseif (isset($taskrelocateid)) {
                        $settingsObj = new Settings();
                        $position = $settingsObj->get('stand_type');

                        $position_transit = ZonePosition::with('zone')
                        ->whereHas('zone', function ($query) use ($warehouse_array) {
                            $query->where('warehouse_id',$warehouse_array);
                        })
                        ->where('id',$findposition->id)
                        ->first();

                        if ($position_transit) {
                          // Crea el registro del movimiento
                          Stock::create($objStock);

                          $stockMovement = [
                          'product_id'=>$value['product_id'],
                          'product_reference'=>$value['product']['reference'],
                          'product_ean'=>$value['product']['ean'],
                          'product_quanty'=>$value['quanty'],
                          'zone_position_code'=>$findposition['code'],
                          'code128'=>$value['ean128']['code128'],
                          'code14'=>$value['ean14']['code14'],
                          'username'=>$username,
                          'warehouse_id'=>$value['warehouse_id'],
                          // TODO: Agregar enum a la action
                          'action'=>'income',
                          'concept'=>'relocate'
                        ];

                          StockMovement::create($stockMovement);

                          $transitionDeleted = StockTransition::with(
                          'product',
                            'zone_position.zone',
                            'ean128',
                            'ean14',
                            'ean13'
                          )
                          ->where('code14_id', $code14find['id'])
                          ->delete();
                          // $drop = wms_ean_codes128::where()->first()

                        }else {
                          return $this->response->error('storage_pallet_13_source_no_found', 404);
                        }

                        // $transitorio = Stock::with('zone_position.zone')
                        // ->whereHas('zone_position.zone', function ($query) use ($position) {
                        //     $query->where('name',$position);
                        // })
                        // ->first();
                      }else if (isset($scheduleId)) {
                            if (in_array($value['product_id'], $produc_Id)) {
                                $objStock['code14_id'] = null;
                                $objStock['code128_id'] = null;


                                $picking= Stock::where('zone_position_id', $findposition->id)->where('product_id', $value['product_id'])->first();

                                $code13find = Product::where('ean', $codeInput13)->first();

                                if ($code13find) {
                                    $find13 = StockPickingConfig::where('zone_position_id', $findposition->id)->where('product_id', $code13find->id)
                                  ->first();
                                }


                                if ($multiple<=1) {
                                    if (!empty($picking)) {
                                        $picking->increment('quanty', $value['quanty']);
                                    } else {
                                        Stock::create($objStock);
                                    }
                                    // Crea el registro del movimiento
                                    $stockMovement = [
                                    'product_id'=>$value['product_id'],
                                    'product_reference'=>$value['product']['reference'],
                                    'product_ean'=>$value['product']['ean'],
                                    'product_quanty'=>$value['quanty'],
                                    'zone_position_code'=>$findposition['code'],
                                    'code128'=>$value['ean128']['code128'],
                                    'code14'=>$value['ean14']['code14'],
                                    'username'=>$username,
                                    'warehouse_id'=>$value['warehouse_id'],
                                    // TODO: Agregar enum a la action
                                    'action'=>'income',
                                    'concept'=>'relocate'
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
                                    if (!empty($find13)) {
                                        if (!empty($picking)) {
                                            $picking->increment('quanty', 1);
                                        // $picking->increment('quanty',$value['quanty']);
                                        } else {
                                            Stock::create($objStockMultiple);
                                        }
                                        // Crea el registro del movimiento
                                        $stockMovement = [
                                        'product_id'=>$value['product_id'],
                                        'product_reference'=>$value['product']['reference'],
                                        'product_ean'=>$value['product']['ean'],
                                        'product_quanty'=>$value['quanty'],
                                        'zone_position_code'=>$findposition['code'],
                                        'code128'=>$value['ean128']['code128'],
                                        'code14'=>$value['ean14']['code14'],
                                        'username'=>$username,
                                        'warehouse_id'=>$value['warehouse_id'],
                                        // TODO: Agregar enum a la action
                                        'action'=>'income',
                                        'concept'=>'relocate'
                                      ];

                                        StockMovement::create($stockMovement);

                                        $transition= StockTransition::where('code14_id', $code14find['id'])
                                      ->where('product_id', $value['product_id'])->first();

                                        if (!empty($transition)) {
                                            $transition->decrement('quanty', 1);
                                            // $picking->increment('quanty',$value['quanty']);
                                        }
                                        if ($transition->quanty===0) {
                                            $transitionDeleted = StockTransition::with(
                                          'product',
                                            'zone_position.zone',
                                            'ean128',
                                            'ean14',
                                            'ean13'
                                          )->where('code14_id', $code14find['id'])
                                          ->where('product_id', $value['product_id'])
                                          ->delete();
                                        }
                                        $isMine = true;
                                    } else {
                                        return $this->response->error('storage_pallet_13_source_no_found', 404);
                                    }
                                }
                            } else {
                                if (!$created) {
                                    // $created = true;
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

                                    $transition= StockTransition::where('code14_id', $code14find['id'])
                                      ->count();

                                    if ($transition<=1) {
                                        $taskSchedules = [
                                          'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0].' '.explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
                                          'name' => 'Editar pallet con referencias sobrantes reabastecimiento picking:',
                                          'schedule_type' => ScheduleType::Restock,
                                          'schedule_action' => ScheduleAction::Edit,
                                          'status' => ScheduleStatus::Process,
                                          'user_id' => $user->id
                                        ];

                                        $schedule = Schedule::create($taskSchedules);
                                    }
                                }
                            }
                        } elseif (isset($scheduleIdTransform)) {
                            // Si es de transformar, desamarro la caja del pallet
                            $transformar = ScheduleTransition::with('stock_transition')->where('schedule_id', $scheduleIdTransform)->whereHas('stock_transition', function ($query) use ($code14find) {
                                $query->where('code14_id', $code14find['id']);
                            })
                                  ->get();


                            if (!empty($transformar)) {
                                unset($objStock['code128_id']);
                                StockFunctions::removeEan14FromEan128($objStock['code14_id']);

                                $transform= Stock::where('zone_position_id', $findposition->id)->where('product_id', $value['product_id'])->first();

                                if ($multiple<=1) {
                                    Stock::create($objStock);

                                    // Crea el registro del movimiento
                                    $stockMovement = [
                                        'product_id'=>$value['product_id'],
                                        'product_reference'=>$value['product']['reference'],
                                        'product_ean'=>$value['product']['ean'],
                                        'product_quanty'=>$value['quanty'],
                                        'zone_position_code'=>$findposition['code'],
                                        'code14'=>$value['ean14']['code14'],
                                        'username'=>$username,
                                        'warehouse_id'=>$value['warehouse_id'],
                                        // TODO: Agregar enum a la action
                                        'action'=>'income',
                                        'concept'=>'relocate'
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
                                } else {
                                    if (!empty($transform)) {
                                        $transform->increment('quanty', 1);
                                    // $picking->increment('quanty',$value['quanty']);
                                    } else {
                                        Stock::create($objStockMultiple);
                                    }
                                    // Crea el registro del movimiento
                                    $stockMovement = [
                                          'product_id'=>$value['product_id'],
                                          'product_reference'=>$value['product']['reference'],
                                          'product_ean'=>$value['product']['ean'],
                                          'product_quanty'=>$value['quanty'],
                                          'zone_position_code'=>$findposition['code'],
                                          'code128'=>$value['ean128']['code128'],
                                          'code14'=>$value['ean14']['code14'],
                                          'username'=>$username,
                                          'warehouse_id'=>$value['warehouse_id'],
                                          // TODO: Agregar enum a la action
                                          'action'=>'income',
                                          'concept'=>'relocate'
                                        ];

                                    StockMovement::create($stockMovement);

                                    $transition= StockTransition::where('code14_id', $code14find['id'])
                                        ->where('product_id', $value['product_id'])->first();

                                    if (!empty($transition)) {
                                        $transition->decrement('quanty', 1);
                                        // $picking->increment('quanty',$value['quanty']);
                                    }
                                    if ($transition->quanty===0) {
                                        $transitionDeleted = StockTransition::with(
                                            'product',
                                              'zone_position.zone',
                                              'ean128',
                                              'ean14',
                                              'ean13'
                                            )->where('code14_id', $code14find['id'])
                                            ->where('product_id', $value['product_id'])
                                            ->delete();
                                    }
                                }
                                $isMine = true;
                            } else {
                                return $this->response->error('storage_pallet_13_source_no_found', 404);
                            }
                        } else {
                            
                                Stock::create($objStock);
                            



                            // Crea el registro del movimiento
                            // $stockMovement = [
                            //             'product_id'=>$value['product_id'],
                            //             'product_reference'=>$value['product']['reference'],
                            //             'product_ean'=>$value['product']['ean'],
                            //             'product_quanty'=>$value['quanty'],
                            //             'zone_position_code'=>$findposition['code'],
                            //             'code128'=>$value['ean128']['code128'],
                            //             'code_ean14'=>$value['ean14']['code_ean14'],
                            //             'username'=>$username,
                            //             'warehouse_id'=>$value['warehouse_id'],
                            //             // TODO: Agregar enum a la action
                            //             'action'=>'income',
                            //             'concept'=>'relocate'
                            //           ];

                            // StockMovement::create($stockMovement);

                            // $transitionDeleted = StockTransition::with(
                            //             'product',
                            //               'zone_position.zone',
                            //               'ean128',
                            //               'ean14',
                            //               'ean13'
                            //             )->where('code_ean14', $code14find['code_ean14'])->delete();
                        }
                    }

                    if (isset($scheduleId)) {
                        if (!$isMine) {
                            return $this->response->error('storage_pallet_13_source_no_found', 404);
                        }
                    }

                    // Actualizo este valor sólo si la tarea NO es de transformar o reproceso.
                    // Porque, sino, a la posición de transformar (concept_id = área de trabajo) le cambia el concepto a 'almacenamiento'
                    if (empty($scheduleIdTransform)) {
                        $findposition->concept_id = $zonepconcept['id'];
                    }

                    // if ($relocateAction !== 'dispatch' && !$taskrelocateEnlistid) {
                    //   $settingsObj = new Settings($companyId);
                    //   $WorkAreaConcept = $settingsObj->get('zone_concept_work_area');
                    //   $WorkAreaConceptId = ZoneConcept::where('name', $WorkAreaConcept)->first()->id;
                    //   $findposition->concept_id = $WorkAreaConceptId;
                    // }

                    $findposition->save();
                } else {
                    return $this->response->error('storage_pallet_code14_no_found', 404);
                }
                $dataRes =  $findposition;
            // });
        } elseif ($packagingType == PackagingType::Producto) {
            $product = Product::with('product_sub_type.product_type.product_category')->where('ean', $codeInput)->first();

            if (!$product) {
                return $this->response->error('storage_pallet_product_no_found', 404);
            }
            DB::transaction(function () use ($product,$positionCode,$zonepconcept,$username,$produc_Id,$scheduleId,$code14,$warehouseId,$codeInput13,$isEan14,$codeInput,$ean14Code) {

                                  //Buscamos la posición de origen
                $findposition = ZonePosition::where('code', $positionCode)->first();

                $settingsObj = new Settings();
                $PickingName = $settingsObj->get('picking_type');

                $findpositionPicking = ZonePosition::with('zone.zone_type')
                                  ->where('code', $positionCode)
                                  ->whereHas('zone.zone_type', function ($query) use ($PickingName) {
                                      $query->where('name', $PickingName);
                                  })
                                  ->first();

                if (empty($findposition)&&!$isEan14) {
                    return $this->response->error('storage_pallet_position_target_no_found', 404);
                }
                //Validamos que la posición se encuentre disponible para almacenar
                if (!$findposition['active']&&!$isEan14) {
                    return $this->response->error('storage_pallet_position_unavailable', 404);
                }

                $findTransition = StockTransition::with(
                                    'product',
                                      'zone_position.zone',
                                      'ean128',
                                      'ean14',
                                      'ean13'
                                    )->where('product_id', $product->id)->where('code14_id', null)->get()->toArray();



                if (!empty($findTransition) && !$isEan14) {
                    $isMine = false;
                    $created = false;
                    foreach ($findTransition as $key => $value) {

                                        // Insertar la informacion en Stock con la nueva informacion de posicion

                        $objStock = [
                                          'product_id'=>$value['product_id'],
                                          'zone_position_id'=>$findposition->id,
                                          'code128_id'=>$value['code128_id'],
                                          // 'code14_id'=>$value['code14_id'],
                                          // 'quanty'=>$value['quanty'],
                                          'quanty'=>1,
                                          'active'=>1,
                                        ];


                        if (!$findpositionPicking) {
                            $transform= Stock::where('zone_position_id', $findposition->id)->where('product_id', $product->id)->where('code14_id', null)->first();

                            if (!empty($transform)) {
                                $transform->increment('quanty', 1);
                            // $picking->increment('quanty',$value['quanty']);
                                            //esto es para validar que la posicion sea de picking y validar referencia,color y capacidad de la posicion
                            } else {
                                Stock::create($objStock);
                            }
                        }


                        if ($findpositionPicking) {
                            $sum = $product->min_stock + $product->stock_secure;
                            $validate_reference = Stock::with('product')
                                        ->whereHas('product', function ($q) use ($product) {
                                            $q->where('parent_product_id', $product->parent_product_id)->where('colour', $product->colour);
                                        })
                                        ->where('zone_position_id', $findposition->id)
                                        ->where('product_id', $value['product_id'])->first();



                            $picking= Stock::where('zone_position_id', $findposition->id)->where('product_id', $value['product_id'])->first();


                            $unit_category = $product->product_sub_type->product_type->product_category->units;

                            if ($picking && $picking->quanty < $unit_category || $validate_reference && $picking->quanty < $unit_category) {
                                if ($picking) {

                                              //preguntamos que si la posicion tiene capacidad lo deje ingresar productos

                                    $picking->increment('quanty', $value['quanty']);
                                } else {
                                    Stock::create($objStock);
                                }
                            } else {
                                return $this->response->error('alert_product_no_found_picking_zone', 404);
                            }
                        }


                        // Crea el registro del movimiento
                        $stockMovement = [
                                          'product_id'=>$value['product_id'],
                                          'product_reference'=>$value['product']['reference'],
                                          'product_ean'=>$value['product']['ean'],
                                          'product_quanty'=>$value['quanty'],
                                          'zone_position_code'=>$findposition['code'],
                                          'code128'=>$value['ean128']['code128'],
                                          'code14'=>$value['ean14']['code14'],
                                          'username'=>$username,
                                          'warehouse_id'=>$value['warehouse_id'],
                                          // TODO: Agregar enum a la action
                                          'action'=>'income',
                                          'concept'=>'relocate'
                                        ];

                        StockMovement::create($stockMovement);

                        $transition= StockTransition::where('code14_id', null)
                                        ->where('product_id', $product->id)->first();

                        if (!empty($transition)) {
                            $transition->decrement('quanty', 1);
                            // $picking->increment('quanty',$value['quanty']);
                        }

                        // $transitionDeleted = StockTransition::with(
                        //   'product','zone_position.zone'
                        //   ,'ean128','ean14','ean13'
                        //   )->where('code14_id',$value['code14_id'])->where('product_id',$product->id)->delete();

                        if ($transition->quanty===0) {
                            $transitionDeleted = StockTransition::with(
                                            'product',
                                              'zone_position.zone',
                                              'ean128',
                                              'ean14',
                                              'ean13'
                                            )->where('code14_id', $value['code14_id'])->where('product_id', $product->id)->delete();
                        }
                    }

                    $findposition->concept_id = $zonepconcept['id'];
                    $findposition->save();
                } elseif ($isEan14) {
                    // DB::transaction(function () use($data, &$return) {

                    $quantyRemove = 1;

                    $zonepconcept = ZoneConcept::where('is_storage', true)->where('active', true)->first();
                    if (!isset($zonepconcept)) {
                        return $this->response->error('storage_pallet_zone_concept_no_found', 404);
                    }

                    $product = Product::where('ean', $codeInput)->first();
                    if (!isset($product)) {
                        return $this->response->error('storage_pallet_product_no_found', 404);
                    }


                    $code14find = EanCode14::where('code14', $ean14Code)->first();

                    if (!isset($code14find)) {
                        return $this->response->error('storage_pallet_code14_no_found', 404);
                    }


                    $findTransition = StockTransition::with(
                                            'product',
                                              'zone_position.zone',
                                              'ean128',
                                              'ean14',
                                              'ean13'
                                            )->where('product_id', $product['id'])
                                            ->first();

                    $datatransition = [];
                    if ($findTransition) {
                        $findStock = Stock::with(
                                                'product',
                                                  'zone_position.zone',
                                                  'ean128',
                                                  'ean14',
                                                  'ean13'
                                                )->where('product_id', $product['id'])
                                                ->where('code14_id', $code14find['id'])
                                                ->where('quanty', '>', 0)
                                                ->first();

                        if ($findStock) {

                                                  // $findStock->quanty += $quantyRemove;
                            // $findTransition->quanty -= $quantyRemove;
                            $findStock->increment('quanty', $quantyRemove);
                            //  $findStock->quanty += $quantyRemove;
                            $findTransition->decrement('quanty', $quantyRemove);


                            // Crea el registro del movimiento
                            $stockMovement = [

                                                    'product_id'=>$findTransition['product_id'],
                                                    'product_reference'=>$findTransition['product']['reference'],
                                                    'product_ean'=>$findTransition['product']['ean'],
                                                    'product_quanty'=>$quantyRemove,

                                                    'zone_position_code'=>$findStock['zone_position']['code'],

                                                    'code128'=>$findStock['ean128']['code128'],
                                                    'code14'=>$findStock['ean14']['code14'],
                                                    // 'username'=>$data['user']['username'],

                                                    'warehouse_id'=>$findStock['zone_position']['zone']['warehouse_id'],
                                                    // TODO: Agregar enum a la action
                                                    'action'=>'income',
                                                    'concept'=>$findTransition['concept']
                                                  ];

                            StockMovement::create($stockMovement);
                            $findStock->save();

                            if ($findTransition->quanty == 0) {
                                $findTransition->delete();
                            } else {
                                $findTransition->save();
                            }

                            $return = $findStock;
                        } else {
                            return $this->response->error('storage_pallet_ean14_no_found_sample', 404);
                        }
                    } else {
                        return $this->response->error('storage_pallet_product_no_found_inspeccion', 404);
                    }
                    // });
                } else {
                    return $this->response->error('storage_pallet_product_no_found', 404);
                }
            });
        }

        if (!empty($dataRes)) {
            return $dataRes->toArray();
        }
        return $this->response->noContent();
    }

    public function getStockRelocateBySchedule(Request $request)
    {
        $data = $request->all();
        $schedule_id = $data['task'];

        $transition = ScheduleTransition::orderBy('id', 'desc')
    ->whereNull('transition_id')
    ->with('stock.product', 'stock.zone_position', 'stock.ean14')
    ->get();
        return $transition->toArray();
    }
}
