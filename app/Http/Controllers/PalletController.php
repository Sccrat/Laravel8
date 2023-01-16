<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\EanCode14;
use App\Models\EanCode128;
use App\Models\ZonePosition;
use App\Models\Pallet;
use App\Models\StructureCode;
use App\Models\StockTransition;
use App\Models\DocumentDetail;
use DB;
use App\Common\Codes;
use App\Common\Features;
use App\Common\Settings;
use Log;
use App\Enums\PackagingType;
use App\Models\EanCode14Detail;
use App\Models\User;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class PalletController extends BaseController
{

  public function getPallesByPosition(Request $request, $id)
  {
    $zone_position  = ZonePosition::where('code', $id)->first();
    if (!isset($zone_position)) {
      return $this->response->error('storage_pallet_position_no_found', 404);
    }

    $pallet = DB::table('wms_pallet')
      // ->join('wms_ean_codes14', 'wms_ean_codes14.id', '=', 'wms_pallet.code14_id')
      ->join('wms_ean_codes128', 'wms_ean_codes128.id', '=', 'wms_pallet.code128_id')
      ->join('wms_stock', 'wms_pallet.code128_id', '=', 'wms_stock.code128_id')
      ->join('wms_containers', 'wms_ean_codes128.container_id', '=', 'wms_containers.id')
      ->join('wms_container_types', 'wms_containers.container_type_id', '=', 'wms_container_types.id')

      ->where('wms_stock.zone_position_id', $zone_position['id'])
      // ->whereNotNull('wms_pallet.code14_id')
      ->whereNotNull('wms_stock.code128_id')
      ->select('wms_ean_codes128.id as code128_id', 'wms_ean_codes128.code128 as code', 'wms_containers.name as container');
    // ->orderBy('wms_pallet.code128_id')

    $allcodes  = $pallet->distinct()->get();
    return $allcodes;
  }

  public function getPallesByCode14(Request $request, $id)
  {
    $positioncode = $request['position'];
    $zone_position  = ZonePosition::where('code', $positioncode)->first();
    if (!isset($zone_position)) {
      return $this->response->error('storage_pallet_position_no_found', 404);
    }

    $pallet = DB::table('wms_pallet')
      ->join('wms_ean_codes14', 'wms_ean_codes14.id', '=', 'wms_pallet.code14_id')
      ->join('wms_ean_codes128', 'wms_ean_codes128.id', '=', 'wms_pallet.code128_id')
      ->join('wms_stock', 'wms_ean_codes128.id', '=', 'wms_stock.code128_id')
      ->join('wms_containers', 'wms_ean_codes128.container_id', '=', 'wms_containers.id')
      ->join('wms_container_types', 'wms_containers.container_type_id', '=', 'wms_container_types.id')

      ->where('wms_ean_codes14.code14', $id)
      ->where('wms_stock.zone_position_id', $zone_position['id'])
      ->whereNotNull('wms_pallet.code14_id')
      ->whereNotNull('wms_stock.code128_id')
      ->select('wms_ean_codes128.id as code128_id', 'wms_pallet.id', 'wms_ean_codes128.code128 as code', 'wms_containers.name as container');
    // ->orderBy('wms_pallet.code128_id')

    $allcodes  = $pallet->get();
    return $allcodes;
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function getUnitsByPosition(Request $request)
  {
    $documentid = $request->input('documentid');
    $pallet = DB::table('wms_pallet')
      ->join('wms_ean_codes128', 'wms_ean_codes128.id', '=', 'wms_pallet.code128_id')
      ->join('wms_containers', 'wms_ean_codes128.container_id', '=', 'wms_containers.id')
      ->join('wms_container_types', 'wms_containers.container_type_id', '=', 'wms_container_types.id')
      ->join('wms_documents', 'wms_ean_codes128.document_id', '=', 'wms_documents.id')
      ->where('wms_documents.id', $documentid)
      // ->whereNotNull('wms_machines.warehouse_id')
      // ->whereNotNull('wms_machines.zone_id')
      ->select('wms_documents.number', 'wms_pallet.id', 'wms_pallet.code14_id', 'wms_pallet.code128_id')
      ->orderBy('wms_pallet.code128_id')
      ->get();

    // $pallet = $query->toArray();

    // $pallet = Pallet::orderBy('code128_id');

    $code128 = 0;
    $code128ant = 0;
    $posParent = -1;
    // $pallet = $pallet->get();
    // $pallet = $pallet->toArray();
    $result = [];

    foreach ($pallet as $item) {
      $pal = get_object_vars($item);
      $code128 = $pal['code128_id'];

      if ($code128 != $code128ant) {
        $child = [];

        $code128find = Codes::GetCode128($code128);

        $result[] = [
          'code128_id' => $pal['code128_id'],
          'code' => $code128find['code128'],
          'isParent' => 1,
          'container_type' => $code128find['container']['content_type'],
          'container_id' => $code128find['container_id'],
          'container_name' => $code128find['container']['name'],
          'container_code' => $code128find['container']['code'],
          'children' => []
        ];
        $posParent++;
      }
      $code14find = Codes::GetCode14($pal['code14_id']);
      $code14 = get_object_vars($code14find[0]);
      $child[] = [
        'code14_id' => $pal['code14_id'],
        'isParent' => 0,
        'document_details_id' => $code14['document_details_id'],
        'number' => $code14['number'],
        'reference' => $code14['reference'],
        'description' => $code14['description'],
        'code' => $code14['code14'], //Code14
        'code13' => $code14['code13'],
        'quanty' => $code14['quanty'],
        'container_type' => $code14['container_type_name'],
        'container_type_code' => $code14['container_type_code'],
        'container_type_id' => $code14['container_type_id'],
        'container_name' => $code14['container_name'],
        'container_code' => $code14['container_code'],
        'container_id' => $code14['container_id'],
        'cartons' => $code14['cartons']
        // 'children'=>[]
      ];
      $result[$posParent]['children'] = $child;
      $code128ant = $code128;
    }

    //$documents = Document::with('detail')->get();
    return $result;
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    $documentid = $request->input('documentid');
    $pallet = DB::table('wms_pallet')
      ->join('wms_ean_codes128', 'wms_ean_codes128.id', '=', 'wms_pallet.code128_id')
      ->join('wms_containers', 'wms_ean_codes128.container_id', '=', 'wms_containers.id')
      ->join('wms_container_types', 'wms_containers.container_type_id', '=', 'wms_container_types.id')
      ->join('wms_documents', 'wms_ean_codes128.document_id', '=', 'wms_documents.id')
      ->where('wms_documents.id', $documentid)
      ->where('wms_ean_codes128.canceled', 0)
      // ->whereNotNull('wms_machines.warehouse_id')
      // ->whereNotNull('wms_machines.zone_id')
      // ->select('wms_documents.number','wms_pallet.id','wms_pallet.code14_id','wms_pallet.code128_id')
      ->orderBy('wms_pallet.code128_id')
      ->get();

    // $pallet = $query->toArray();

    // $pallet = Pallet::orderBy('code128_id');

    $code128 = 0;
    $code128ant = 0;
    $posParent = -1;
    // $pallet = $pallet->get();
    // $pallet = $pallet->toArray();
    $result = [];

    foreach ($pallet as $item) {
      $pal = get_object_vars($item);
      $code128 = $pal['code128_id'];

      if ($code128 != $code128ant) {
        $child = [];

        $code128find = Codes::GetCode128($code128);

        $result[] = [
          'code128_id' => $pal['code128_id'],
          'code' => $code128find['code128'],
          'isParent' => 1,
          'container_type' => $code128find['container']['content_type'],
          'container_id' => $code128find['container_id'],
          'container_name' => $code128find['container']['name'],
          'container_code' => $code128find['container']['code'],
          'children' => []
        ];
        $posParent++;
      }
      $code14find = Codes::GetCode14($pal['code14_id']);
      if (!empty($code14find[0])) {
        $code14 = get_object_vars($code14find[0]);
        $child[] = [
          'code14_id' => $pal['code14_id'],
          'isParent' => 0,
          'document_details_id' => $code14['document_details_id'],
          'number' => $code14['number'],
          'reference' => $code14['reference'],
          'description' => $code14['description'],
          'code' => $code14['code14'], //Code14
          'code13' => $code14['code13'],
          'quanty' => $code14['quanty'],
          'container_type' => $code14['container_type_name'],
          'container_type_code' => $code14['container_type_code'],
          'container_type_id' => $code14['container_type_id'],
          'container_name' => $code14['container_name'],
          'container_code' => $code14['container_code'],
          'container_id' => $code14['container_id'],
          'cartons' => $code14['cartons']
          // 'children'=>[]
        ];
        $result[$posParent]['children'] = $child;
      }
      $code128ant = $code128;
    }

    //$documents = Document::with('detail')->get();
    return $result;
  }

  public function reprintcode128(Request $request)
  {
    $data = $request->all();
    $reason_code_id = $data['reason_code_id'];
    $detailSelected = $data['detailSelected'];

    $html = '';
    DB::transaction(function () use ($detailSelected, &$html, $reason_code_id) {

      foreach ($detailSelected as  $valueDetail) {
        $objcode128 = EanCode128::findOrFail($valueDetail['code128_id']);
        $objcode128->reason_code_id = $reason_code_id;
        $objcode128->canceled = 1;
        $objcode128->save();

        $new128 = [
          'code128' => $valueDetail['code'],
          'document_id' => $objcode128['document_id'],
          'container_id' => $objcode128['container_id'],
          'weight' => $objcode128['weight']
        ];

        $savedcode128 = EanCode128::create($new128);
        $newsavedcode128 = $savedcode128['code128'];


        //Recorremos los detallesOrden a los cuales se les quiere generar código
        foreach ($valueDetail['children'] as  $child) {
          // $html .='<div style="border:solid 1px black;" class="col-xs-12">';
          $pallet = [
            'code14_id' => $child['code14_id'],
            'code128_id' => $savedcode128['id']
          ];
          // $numcartons = $child['cartons'];
          $quantyproducts =  $child['quanty'];
          $code14 =  $child['code'];
          $reference =  $child['reference'];
          $container_code =  $child['container_code'];
          $description =  $child['description'];
          $savepallet = Pallet::create($pallet);
          // $html .='<div class="row" style="border-bottom: 1px solid black;font-weight: bold;"> <div class="col-xs-6" >Cod. 14</div><div class="col-xs-1 text-right" style="border-left: 1px solid black;border-right: 1px solid black;font-weight: bold;">Cant.</div><div class="col-xs-2 text-center" style="border-right: 1px solid black;font-weight: bold;">Referencia</div><div class="col-xs-3 text-center"style="font-weight: bold;">Descripción</div></div>';
          // $html .='<div class="row barcode-row">  <div class="col-xs-6" style="padding: 5px;"><barcode render="img" type="code128b" string="' . $code14 . '"options="options"></barcode></div><div class="col-xs-1 text-right text-code" style="border-left: 1px solid black;border-right: 1px solid black;">' . $quantyproducts . '</div><div class="col-xs-2 text-center text-code" style="border-right: 1px solid black;">' . $reference . '</div><div class="col-xs-3 text-center text-code ">' . $description . '</div></div>';
          // $html .='</div>';
        }
        $html = Codes::getEan128Html($savedcode128->id);
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
    //  return $data;
    $packaging_type = $data['packaging_type'];
    $container_id = $data['container_id'];
    // $container_code = $data['container_code'];
    $document_id = !empty($data['document_id']) ? $data['document_id'] : '';
    $detailSelected = $data['codesSelected'];
    $weight = $data['weight'];
    $height = $data['height'];
    $companyId = $data['company_id'];



    //Soberana S.A.S
    //Consultamos la estrucutra para el tipo de embalaje




    $settingsObj = new Settings($companyId);
    $useCode128 = $settingsObj->get('use_code128') === 'true' ? true : false;

    if ($useCode128) {
      $html = '';
      $structurecodes = Codes::GetStructureCode($packaging_type);


      DB::transaction(function () use ($detailSelected, &$html, $structurecodes, $document_id, $weight, $height) {
        $contador = 0;
        // $newstructurecode14 = '7704121'.$container_code;
        $newstructurecode14 = '';
        //Recorremos la estructura y generamos la estrucutra de los códigos IA
        foreach ($structurecodes as $structure) {
          $ia_code = $structure['ia_code'];
          $code_ia = $ia_code['code_ia'];
          //Validamos si el código IA debe tomar datos de alguna tabla
          if ((!empty($ia_code['table']) && empty($ia_code['field'])) || (!empty($ia_code['field']) && empty($ia_code['table']))) {
            return $this->response->error('No se pueden generar los códigos, porque el código GTIN' . $code_ia . ' está mal configurado', 404);
          } else if (!empty($ia_code['table']) && !empty($ia_code['field'])) {
            $table = $ia_code['table'];
            $field = $ia_code['field'];

            $results = DB::select(DB::raw('SELECT ' . $field . ' FROM ' . $table . ' WHERE id = ' . $document_id . ''));
            if (is_null($results)) {
              return $this->response->error('No se pueden generar los códigos, porque el código GTIN' . $code_ia . 'está mal configurado', 404);
            } else {
              $array = json_decode(json_encode($results[0]), True);
              if ($contador == 0) {
                $newstructurecode14 .= '(' . $code_ia . ')' . $array['number'] . '7704121';
              } else {
                $newstructurecode14 .= '(' . $code_ia . ')' . $array['number'];
              }
            }
          } else {
            if ($contador == 0) {
              $newstructurecode14 .= '(' . $ia_code['code_ia'] . ')' . '7704121';
            } else {
              $newstructurecode14 .= '(' . $ia_code['code_ia'] . ')';
            }
          }
          $contador++;
        }
        $new128 = [
          'code128' => $newstructurecode14,
          'document_id' => $document_id,
          'company_id' => $companyId,
          // 'container_id' => $container_id,
          'weight' => $weight,
          'height' => $height
        ];

        $savedcode128 = EanCode128::create($new128);
        $newsavedcode128 = $savedcode128['code128'] . $savedcode128['id'];
        $savedcode128->code128 = $newsavedcode128;
        $savedcode128->save();
        //Suggest position
        // try {
        if ($savedcode128->document_id > 0) {
          Features::suggestPalletPosition($newsavedcode128);
        }
        // } catch (\Exception $e) {
        // An internal error with an optional message as the first parameter.
        // Log::error('function suggestPalletPosition PallentController.php line 364'.$e->getMessage());
        //return $this->response->errorInternal();
        // }

        //Recorremos los detallesOrden a los cuales se les quiere generar código
        foreach ($detailSelected[0]['quanty_pallet'] as  $valueDetail) {
          $pallet = ['code_ean14' => $detailSelected[0]['code_ean14'], 'document_detail_id' => $detailSelected[0]['id'], 'code128_id' => $savedcode128['id']];
          $savepallet = Pallet::create($pallet);

          // Busco la caja (EAN14) en el stock en transición
          $packOnTransition = StockTransition::where('code_ean14', $detailSelected[0]['code_ean14'])->first();

          // Si está en transición, le pego el ID de su nuevo pallet (EAN128)
          if (!empty($packOnTransition)) {
            $packOnTransition->code128_id = $savedcode128['id'];
            $packOnTransition->save();
          }
        }

        $html = Codes::getEan128Html($savedcode128->id);
      });
    } else {
      //Codigo interno
      $newstructurecode14 = str_random(7);
      $new128 = [
        'code128' => $newstructurecode14,
        'document_id' => $document_id,
        'container_id' => $container_id,
        'weight' => $weight,
        'height' => $height,
        'company_id' => $companyId
      ];

      $savedcode128 = EanCode128::create($new128);

      // for ($i=0; $i < $detailSelected; $i++) {
      foreach ($detailSelected as $value) {
        $pallet = [
          'code_ean14' => $value['reference'], 'document_detail_id' => $value['document_detail_id'],
          'code128_id' => $savedcode128['id'],
          'quanty' => $value['quanty_pallet']
        ];
        $savepallet = Pallet::create($pallet);

        $less = $value['quanty_received_pallet'] >= $value['cartons'] ? $value['cartons'] :  $value['quanty_received_pallet'] + $value['quanty_pallet'];
        $config = DocumentDetail::where('id', $value['document_detail_id'])->update(['quanty_received_pallet' => $less]);
      }
      // }


      // foreach ($detailSelected as  $valueDetail) {

      // }

      $html = Codes::getEan128Html($savedcode128->id);
      // return $newstructurecode14;
      $sugg =   Features::suggestPalletPosition($newstructurecode14, $companyId);
    }


    return  $html;
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

  public function getPalletByCode128(Request $request)
  {
    $code128 = $request->input('code128');
    $pallet = Pallet::whereHas('ean128', function ($q) use ($code128) {
      $q->where('code128', $code128);
    })->with('code14.documentDetail.product', 'ean128')->get();

    return $pallet->toArray();
  }

  public function checkCode14(Request $request)
  {
    $data = $request->all();
    //Check if the 14 exists
    $code14 = EanCode14::where('code14', $data['code14'])->first();
    if ($code14 != null) {
      //The code exists
      //Check if the code belongs to another pallet
      $pallet = Pallet::whereHas('ean128', function ($q) use ($data) {
        $q->where('code128', '<>', $data['code128']);
      })->whereHas('code14', function ($q) use ($data) {
        $q->where('code14', $data['code14']);
      })->with('code14.documentDetail.product')->first();

      if ($pallet != null) {
        //Can add the code 14
        return $this->response->error('El código pertenece a otro pallet', 404);
      }

      //  $responsini = [
      //    "message" => "Código disponible",
      //    "status_code" => 200
      //  ];

      $code14 = EanCode14::where('code14', $data['code14'])->with('documentDetail.product')->first();

      return $code14->toArray();
    } else {
      return $this->response->error('No existe el código ean14', 404);
    }
  }

  public function updatePallet(Request $request)
  {
    $data = $request->all();

    $code128id = $data['code128id'];

    $toRemove = $data['toRemove'];
    $toAdd = $data['toAdd'];

    DB::transaction(function () use ($toRemove, $toAdd, $code128id, $data) {
      //Delete the 14s to remove
      Pallet::whereIn('code14_id', $toRemove)->delete();

      //Remove also the to add to avoid duplicates
      Pallet::whereIn('code14_id', $toAdd)->delete();

      $pallet = [];
      //Prepare the data for insert
      foreach ($toAdd as $row) {
        $pallet[] = [
          "code14_id" => $row,
          "code128_id" => $code128id
        ];
      }

      Pallet::insert($pallet);

      //Update volumen
      $eanCode128 = EanCode128::where('id', $code128id)->first();
      $eanCode128->weight = $data['weight'];
      $eanCode128->height = $data['height'];
      $eanCode128->save();

      //Check if the pallet is in transition
      $transition = StockTransition::where('code128_id', $code128id)->first();

      if (!empty($transition)) {
        //Update the transition stock
        StockTransition::whereIn('code14_id', $toRemove)->delete();
        StockTransition::whereIn('code14_id', $toAdd)->delete();
        $stockTransition = [];

        foreach ($toAdd as $row) {
          //get the product inside the box
          $detail = EanCode14Detail::where('ean_code14_id', $row)->first();

          if (!empty($detail)) {
            $stockTransition[] = [
              'product_id'        => $detail->product_id,
              'zone_position_id'  => $transition->zone_position_id,
              'code128_id'        => $code128id,
              'code14_id'         => $row,
              'quanty'            => $detail->quanty,
              'action'            => $transition->action,
              'warehouse_id'      => $transition->warehouse_id,
              'user_id'           => $transition->user_id
            ];
          }
        }
        //Insert the new boxes to transition too
        StockTransition::insert($stockTransition);
      }

      //Suggest the new position
      Features::suggestPalletPosition($eanCode128->code128);

      return $this->response->created();
    });
  }

  public function storage(Request $request)
  {
      $data = $request->all();
      $code14_id = $data['code14_id'];
      $code_ean14 = $data['code_ean14'];
      $document_id = !empty($data['document_id']) ? $data['document_id'] : '';
      $document_detail_id = $data['document_detail_id'];
      $companyId = $data['company_id'];
      $user = User::where('id', $data['session_user_id'])->first();


      if ($data['good']) {
        $newstructurecode14 = str_random(7);
        $new128 = [
          'code128' => $newstructurecode14,
          'document_id' => $document_id,
          'container_id' => 1,
          'weight' => 0,
          'height' => 0,
          'company_id' => $companyId
        ];

        $savedcode128 = EanCode128::create($new128);
        $pallet = [
          'code_ean14' => $code_ean14,
          'document_detail_id' => $document_detail_id,
          'code128_id' => $savedcode128['id'],
          'quanty' => $data['good'],
          "good" => $data['good'],
          "seconds" => 0,
          "sin_conf" => 0
        ];
        Pallet::create($pallet);
        $config = EanCode14Detail::where('id', $code14_id)->first();
        $config->increment('quanty_received_pallet', $data['good']);
        $config->increment('good_pallet', $data['good']);
        $sugg = Features::suggestStoragePosition($newstructurecode14, $companyId, $data['product_id'], $document_id, 'good', $data['warehouse_id'], $user);
      }

      if ($data['seconds']) {
        $newstructurecode14 = str_random(7);
        $new128 = [
          'code128' => $newstructurecode14,
          'document_id' => $document_id,
          'container_id' => 1,
          'weight' => 0,
          'height' => 0,
          'company_id' => $companyId
        ];
        $savedcode128 = EanCode128::create($new128);
        $pallet = [
          'code_ean14' => $code_ean14,
          'document_detail_id' => $document_detail_id,
          'code128_id' => $savedcode128['id'],
          'quanty' => $data['seconds'],
          "good" => 0,
          "seconds" => $data['seconds'],
          "sin_conf" => 0
        ];
        Pallet::create($pallet);
        $config = EanCode14Detail::where('id', $code14_id)->first();
        $config->increment('quanty_received_pallet', $data['seconds']);
        $config->increment('seconds_pallet', $data['seconds']);
        $sugg = Features::suggestStoragePosition($newstructurecode14, $companyId, $data['product_id'], $document_id, 'seconds', $data['warehouse_id'], $user);
    	}

    if ($data['sin_conf']) {
      $newstructurecode14 = str_random(7);
      $new128 = [
        'code128' => $newstructurecode14,
        'document_id' => $document_id,
        'container_id' => 1,
        'weight' => 0,
        'height' => 0,
        'company_id' => $companyId
      ];

      $savedcode128 = EanCode128::create($new128);

      $pallet = [
        'code_ean14' => $code_ean14,
        'document_detail_id' => $document_detail_id,
        'code128_id' => $savedcode128['id'],
        'quanty' => $data['sin_conf'],
        "good" => 0,
        "seconds" => 0,
        "sin_conf" => $data['sin_conf']
      ];
      Pallet::create($pallet);
      $config = EanCode14Detail::where('id', $code14_id)->first();
      $config->increment('quanty_received_pallet', $data['sin_conf']);
      $config->increment('sin_conf_pallet', $data['sin_conf']);
      $sugg = Features::suggestStoragePosition($newstructurecode14, $companyId, $data['product_id'], $document_id, 'sin_conf', $data['warehouse_id'], $user);
    }
  }
}
