<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EanCode14;
use App\Models\DocumentDetailCount;
use App\Models\DocumentDetail;
use DB;
use App\Common\Codes;
use App\Enums\PackagingType;
use App\Models\EanCode14Detail;
use App\Models\Stock;
use App\Models\StockTransition;
use App\Enums\ScheduleAction;
use App\Enums\ScheduleType;
use App\Enums\ScheduleStatus;
use App\Models\MasterBox;
use App\Models\Schedule;
use DateTimeZone;
use Carbon\Carbon;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class EanCode14Controller extends BaseController
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    $detailId = $request->input('document_detail_id');
    $code14 = $request->input('code14');
    $codes = EanCode14::with('pallet', 'serial', 'detail');

    if (isset($detailId)) {
      $codes = $codes->where('document_detail_id', $detailId);
    }

    if (isset($code14)) {
      $codes = $codes->where('code14', $code14);
    }

    $codes = $codes->where('canceled', 0);
    //$codes = Document::with('detail')->get();
    $codes = $codes->get();
    return $codes->toArray();
  }

  public function getAllCodes14ByDocumentId($id)
  {
    $allcodes = EanCode14::with('detail.product', 'document.clientdocument')->where('document_id', $id)->get();
    // foreach ($code as $value) {
    //   $allcodes = DocumentDetail::with('product_ean14.product','document', 'ean14.detail_m')
    //   // ->has('stock','=',0)
    //   ->where('quanty_received_pallet','>',0)
    //   ->where('document_id',$id)->get();
    // }

    return $allcodes->toArray();
  }

  public function reprintcode14(Request $request)
  {
    $data = $request->all();

    $detailSelected = $data['detailSelected'];
    $reason_code_id = $data['reason_code_id'];

    $html = '';
    DB::transaction(function () use ($detailSelected, &$html, $reason_code_id) {

      //Recorremos los detallesOrden a los cuales se les quiere generar código
      foreach ($detailSelected as  $valueDetail) {
        $quantyproducts =  $valueDetail['quanty'];
        // $code13 =  $valueDetail['code13'];
        // $reference =  $valueDetail['reference'];
        $document_detail_id =  $valueDetail['document_details_id'];
        $container_code =  $valueDetail['container_code'];
        // $description =  $valueDetail['description'];
        $container_id = $valueDetail['container_id'];
        $newstructurecode14 = $valueDetail['code14'];

        $products = $valueDetail['products'];

        //$objcode14 = Codes::GetCode14($valueDetail['ean_code14_id']);
        $objcode14 = EanCode14::findOrFail($valueDetail['ean_code14_id']);
        $objcode14->reason_code_id = $reason_code_id;
        $objcode14->canceled = 1;
        $objcode14->save();

        $new14 = [
          'document_detail_id' => $document_detail_id,
          'code14' => $newstructurecode14,
          // 'code13'=> $code13,
          'quanty' => $quantyproducts,
          'container_id' => $container_id,
          // 'product_id'=>$objcode14->product_id,
          'damaged' => $objcode14->damaged,
          'quarantine' => $objcode14->quarantine,
          'code_parent_id' => $objcode14->id,
        ];

        $savedcode14 = EanCode14::create($new14);
        $savedcode14->detail()->createMany($products);
        $newsavedcode14 = $savedcode14['code14'];


        $html .= Codes::getEan14Html($savedcode14->id);
        $html .= "<div class='breack-page'></div>";
      }
    });
    return  $html;
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

    $packaging_type = $data['packaging_type'];

    $detailSelected = $data['detailSelected'];

    //Consultamos la estrucutra para el tipo de embalaje
    $structurecodes = Codes::GetStructureCode($packaging_type);


    $html = '';
    //Esto fala cogerlo dinámicamente
    DB::transaction(function () use ($detailSelected, &$html, $structurecodes) {

      // Contador para identificar los grupos de 3 codigos para hacer el salto de pagina
      $breackCount = 0;
      $detailCount = 0;
      $detailLen   = count($detailSelected);

      //Recorremos los detallesOrden a los cuales se les quiere generar código
      foreach ($detailSelected as  $valueDetail) {
        $detailCount++;
        $numcartons = $valueDetail['cartons'];
        $quantyproducts =  $valueDetail['quanty'];
        // $code13 =  $valueDetail['code'];
        // $reference =  $valueDetail['reference'];
        $document_detail_id =  $valueDetail['id'];

        // $product_id =  $valueDetail['product_id'];
        $container_code =  $valueDetail['container_code'];
        // $description =  $valueDetail['description'];
        $container_id = $valueDetail['container_id'];

        $products = $valueDetail['products'];

        $reason_code = !empty($valueDetail['reason_code']) ? $valueDetail['reason_code'] : NULL;

        // Determina si existe o no conteo
        $count = !empty($valueDetail['count']) ? $valueDetail['count'] : false;

        // Si existe conteo se crean los codigos apoarti de este arreglo
        // Sino se usa la cantidad de cartons descrita en cada detalle del documento
        if ($count) {
          $i = 0;
          // la cantidad de codigos a generar, se define segun el numero de conteos diferentes a 0 hechos
          foreach ($count as $countvalue) {
            $i++;
            $quantyproducts =  $countvalue['quanty'];

            $breackCount++;
            $newstructurecode14 = '7704121' . $container_code;
            $damaged = $countvalue['damaged'];
            $quarantine = $countvalue['quarantine'];

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
              'document_detail_id' => $document_detail_id,
              'quanty' => $quantyproducts,
              'container_id' => $container_id,
              'reason_code_id' => $reason_code,
              // 'product_id'=>$product_id,
              'damaged' => $damaged,
              'quarantine' => $quarantine
            ];

            $savedcode14 = EanCode14::create($new14);
            $newsavedcode14 = $savedcode14['code14'] . $savedcode14['id'];
            $savedcode14->code14 = $newsavedcode14;
            $savedcode14->detail()->createMany($products);
            $savedcode14->save();

            // Actualizo el conteo para indicar que ya se imprimio
            $documents = DocumentDetailCount::findOrFail($countvalue['id']);
            $documents->has_error = 0;
            $documents->save();

            $html .= Codes::getEan14Html($savedcode14->id);

            $html .= "<div class='breack-page'></div>";
          } //END FOR

        } else {
          $damaged = !empty($valueDetail['damaged']) ? $valueDetail['damaged'] : false;
          $quarantine = !empty($valueDetail['quarantine']) ? $valueDetail['quarantine'] : false;
          //La cantidad de códigos a generar, se define según el número de cartones por cada detalle
          for ($i = 0; $i < $numcartons; $i++) {

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
              'document_detail_id' => $document_detail_id,
              'quanty' => $quantyproducts,
              'container_id' => $container_id,
              'reason_code_id' => $reason_code,
              // 'product_id'=>$product_id,
              'damaged' => $damaged,
              'quarantine' => $quarantine
            ];

            $savedcode14 = EanCode14::create($new14);
            $newsavedcode14 = $savedcode14['code14'] . $savedcode14['id'];
            $savedcode14->code14 = $newsavedcode14;
            $savedcode14->save();
            $savedcode14->detail()->createMany($products);

            $html .= Codes::getEan14Html($savedcode14->id);

            $html .= "<div style='page-break-after: always;' class='breack-page'></div>";
          }
        }
      }
    });

    if (empty($html)) {
      return $this->response->error('No se generó ningun codigo', 404);
    }

    return  $html;
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    $code14 = Codes::GetCode14($id);
    return $code14;
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
    $code = EanCode14::findOrFail($id);

    $code->canceled = array_key_exists('canceled', $data) ? $data['canceled'] : $code->canceled;
    $code->reason_code_id = array_key_exists('reason_code_id', $data) ? $data['reason_code_id'] : $code->reason_code_id;

    if (array_key_exists('serial', $data)) {
      $code->serial()->delete();
      $code->serial()->createMany($data['serial']);
    }


    $code->save();

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
  }

  public function cancelCodes(Request $request)
  {
    $detailId = $request->input('document_detail_id');
    $cant = $request->input('cant');
    if ($cant > 0) {
      $documentsCount = DocumentDetailCount::where('document_detail_id', $detailId)->where('has_error', true)->get();
      if (!empty($documentsCount)) {
        foreach ($documentsCount as $key => $count) {
          $count->ean14()->update(['canceled' => 1]);
          $count->ean14()->dissociate();
          $count->save();
        }
        // $code = EanCode14::where('document_detail_id', $detailId)->where('canceled', 0)->take($cant)->update(['canceled' => 1]);
      }
    }
    return $this->response->noContent();
  }


  public function generateCode14Picking(Request $request)
  {

    $data = $request->all();

    $packaging_type = $data['packaging_type'];

    $zone_position_id = $data['zone_position_id'];

    $detailSelected = $data['detailSelected'];

    $code = $data['code'];

    //Consultamos la estrucutra para el tipo de embalaje
    $structurecodes = Codes::GetStructureCode($packaging_type);


    $html = '';
    //Esto fala cogerlo dinámicamente
    DB::transaction(function () use ($detailSelected, &$html, $structurecodes, $zone_position_id, $data, $code) {

      // Contador para identificar los grupos de 3 codigos para hacer el salto de pagina
      $breackCount = 0;
      $detailCount = 0;
      $detailLen   = count($detailSelected);

      //Recorremos los detallesOrden a los cuales se les quiere generar código
      //  foreach ($detailSelected as  $valueDetail) {
      $detailCount++;
      $numcartons = 1;
      $quantyproducts =  $data['quanty'];
      //  $code13 =  $data['code'];
      // $reference =  $data['reference'];
      //$document_detail_id =  $data['id'];

      //  $product_id =  $data['product_id'];
      $container_code =  $data['container_code'];
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
        //'document_detail_id'=>$document_detail_id,
        'quanty' => $quantyproducts,
        'container_id' => $container_id,
        //  'reason_code_id'=>$reason_code,
        //  'product_id'=>$product_id,
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
            'concept' => 'relocate',
            //  'warehouse_id'=>$value['zone_position']['zone']['warehouse_id'],
            'user_id' => $data['session_user_id'],
          ];

          $StockTransition =  StockTransition::create($objTransition);

          $stockPosition->decrement('quanty', $value->quanty);







          if ($stockPosition->quanty <= 0) {

            $stockPosition->delete();
          }



          //  Crea el registro del movimiento
          //  $stockMovement = [
          //    'product_id'=>$value['product_id'],
          //    'product_reference'=>$value['product']['reference'],
          //    'product_ean'=>$value['product']['ean'],
          //    'product_quanty'=>$value['quanty'],
          //    'zone_position_code'=>$value['zone_position']['code'],
          //    'code128'=>$value['ean128']['code128'],
          //    'code14'=>$value['ean14']['code14'],
          //    'username'=>$username,
          //    'warehouse_id'=>$value['zone_position']['zone']['warehouse_id'],
          //    // TODO: Agregar enum a la action
          //    'action'=>'output',
          //    'concept'=>'relocate'
          //  ];
          //  array_push($datatransition, $stockMovement);
          //
          //  StockMovement::create($stockMovement);
        }
        if ($StockTransition) {
          $taskSchedules = [
            'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0] . ' ' . explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
            'name' => 'Almacenar referencias sobrantes reabastecimiento picking:' . $savedcode14->code14,
            'schedule_type' => ScheduleType::Restock,
            'schedule_action' => ScheduleAction::ToStock,
            'status' => ScheduleStatus::Process,
            'user_id' => $data['session_user_id']
          ];

          $schedule = Schedule::create($taskSchedules);
        }
      }

      $html .= Codes::getEan14Html($savedcode14->id);

      $html .= "<div style='page-break-after: always;' class='breack-page'></div>";
      //  }
      //  }
      //  }
    });

    if (empty($html)) {
      return $this->response->error('No se generó ningun codigo', 404);
    }

    return  $html;
  }



  public function generateCode(Request $request)
  {
    $data = $request->all();
    if (!empty($data)) {
      $structurecodes = Codes::GetStructureCode(PackagingType::Empaque);
      $newstructurecode14 = '7704121' . $data['container_id'];
      $products = $data['products'];
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

      $data['code14'] = $newstructurecode14;
      $savedcode14 = EanCode14::create($data);
      $newsavedcode14 = $savedcode14['code14'] . $savedcode14['id'];
      $savedcode14->code14 = $newsavedcode14;
      $savedcode14->detail()->createMany($products);
      $savedcode14->save();

      $html = Codes::getEan14Html($savedcode14->id);
      $res['html'] = $html;
      $res['ean14'] = $savedcode14;

      if (!empty($data['count_related'])) {
        $documentCount = DocumentDetailCount::findOrFail($data['count_related']);
        $documentCount->ean14_id = $savedcode14->id;
        $documentCount->save();

        if (count($documentCount->child_count) > 0) {
          foreach ($documentCount->child_count as $key => $child) {
            $child->count_parent = NULL;
            $child->save();
          }
        } else if ($documentCount->count_parent > 0) {
          $documentCount->count_parent = NULL;
          $documentCount->save();
        }
      }

      return $res;
    }
    return $this->response->error('Error al enviar la informacion', 404);
  }

  function getCodesReceipt(Request $request)
  {
    $companyId = $request->input('company_id');
    $warehouse = $request->input('warehouse');
    $productType = $request->input('product_type_id');
    $client = $request->input('client_id');
    $ean13 = $request->input('ean13');
    $reference = $request->input('reference');

    $codes = DocumentDetail::with('document.schedule_document_receipt.schedule.schedule_receipt.warehouse', 'pallet.ean128', 'product');

    $codes = $codes->whereHas('document.schedule_document_receipt.schedule.schedule_receipt.warehouse.distribution_center', function ($q) use ($companyId) {
      $q->where('company_id', $companyId);
    })
      ->where('quanty_received', '!=', 0);

    if (isset($warehouse)) {
      $codes = $codes->whereHas('document.schedule_document_receipt.schedule.schedule_receipt', function ($q) use ($warehouse) {
        $q->where('warehouse_id', $warehouse);
      });
    }
    if (isset($productType)) {
      $codes = $codes->whereHas('product.product_sub_type', function ($q) use ($productType) {
        $q->where('product_type_id', $productType);
      });
    }
    if (isset($ean13)) {
      $codes = $codes->orWhereHas('product', function ($q) use ($ean13) {
        $q->where('ean', 'LIKE', $ean13 . '%');
      });
    }
    if (isset($reference)) {
      $codes = $codes->whereHas('product', function ($q) use ($reference) {
        $q->where('reference', $reference)->orWhere('code', $reference)->orWhere('ean', $reference);
      });
    }
    if (isset($client)) {
      $stock = $stock->whereHas('product', function ($q) use ($client) {
        $q->where('client_id', $client);
      });
    }
    $codes = $codes->get();
    return $codes->toArray();
  }

  function getCode14ByCodigo(Request $request)
  {
    $code14 = $request->input('code14');
    $code14Ean = EanCode14::where('code14', $code14)->first();
    if (!$code14Ean) {
      throw new RuntimeException('No se encuentra creado el código 14 escaneado.');
    }
    $code14EanDetail = EanCode14::where('wms_ean_codes14.id', $code14Ean->id)
    ->leftjoin('wms_ean_codes14_detail as ed','ed.ean_code14_id','wms_ean_codes14.id')
    ->leftjoin('wms_products','ed.product_id','wms_products.id')
    ->select('*')
    ->get();
    return [
      'codeEan14'=>$code14Ean,
      'codeEan14Detail'=>$code14EanDetail->toArray(),
    ];
  }

  function updatePesoEan14(Request $request)
  {
    $code14 = $request->input('codes14');
    $weight = $request->input('weight');
    $weightCajaMaster = $request->input('weightCajaMaster');
    $model14 = EanCode14::find($code14['id']);
    $model14->weight = $weight;
    $model14->save();
    $masterModel = MasterBox::where('master', $model14->master)->get();
    foreach ($masterModel as  $value) {
      $value->peso = $weightCajaMaster;
      $value->save();
    }
    return response('Peso actualizado con exito.', Response::HTTP_OK);
  }
}
