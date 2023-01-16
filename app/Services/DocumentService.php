<?php
namespace App\Services;

use App\Models\Document;
use App\Models\DocumentDetail;
use App\Services\BaseService;
use Illuminate\Support\Str;
use App\Enums\DocType;
use App\Enums\PackagingType;
use App\Models\Schedule;
use App\Models\StockTransition;

use DB;

class DocumentService extends BaseService
{
    public function getDocuments()
    {
        $documents = Document::with('detail')->get();

        return $documents;
    }

    public function getDocumentDetailById($id, $request)
    {
        $page = $request->input('page');
        $size = $request->input('size');

        $document = DocumentDetail::with('ean14.serial','product.product_type','detailCount.child_count.ean14',
            'detailCount.ean14','detailCount.detailMultipleCount.product','detailMultiple.product')
            ->where('document_id', $id);

        if(isset($page) && isset($size)) {
            $skip = $page * $size;
            $document = $document->take($size)->skip(($page - 1) * $size);
        }

        $document = $document->get();

        return $document->toArray();
    }

    public function getDocumentDetailReceive($id, $request)
    {
      $documents = DB::table('wms_document_details as dd')
      ->leftJoin('wms_products as p', 'dd.product_id', '=', 'p.id')
      ->leftJoin('wms_document_detail_multiples as ddm', 'dd.id', '=', 'ddm.document_detail_id')
      ->leftJoin('wms_products as pm', 'ddm.product_id', '=', 'pm.id')
      ->leftJoin('wms_ean_codes14 as codes', function ($join)
      {
        $join->on('dd.id', '=', 'codes.document_detail_id');
        $join->on('codes.canceled', '=', DB::raw('0'));
      })
      ->leftJoin('wms_ean_codes14 as codesD', function ($join)
      {
        $join->on('codes.id', '=', 'codesD.id');
        $join->on('codesD.canceled', '=', DB::raw('0'));
        $join->on('codesD.damaged', '=', DB::raw('1'));
      })
      ->leftJoin('wms_ean_codes14 as codesQ', function ($join)
      {
        $join->on('codes.id', '=', 'codesQ.id');
        $join->on('codesQ.canceled', '=', DB::raw('0'));
        $join->on('codesQ.quarantine', '=', DB::raw('1'));
      })
      ->leftJoin('wms_ean_codes14 as codesOk', function ($join)
      {
        $join->on('codes.id', '=', 'codesOk.id');
        $join->on('codesOk.canceled', '=', DB::raw('0'));
        $join->on('codesOk.quarantine', '=', DB::raw('0'));
      })
      ->where('dd.document_id', $id)
      ->select('dd.quanty', 
      'dd.cartons', 
      'dd.quanty_received', 
      'dd.id', 
      'dd.quarantine', 
      'dd.damaged_cartons', 
      'dd.observations',
      'dd.weight',
      'dd.product_id',
      'dd.unit',
      'p.reference',
      'p.description',
      'dd.approve_additional',
      'dd.is_additional',
      'p.serial_shipping',
      'dd.document_id',
      'dd.lot',
      //'codes.container_id',
      // DB::raw('IFNULL(codes.document_detail_id, ddm.document_detail_id) as ddc'),
      // DB::raw('IFNULL(ddm.document_detail_id, codes.document_detail_id) as ddmi'),
      // DB::raw('IF(codes.document_detail_id IS NULL AND ddm.document_detail_id IS NULL, UUID(), IF(codes.document_detail_id IS NOT NULL AND ddm.document_detail_id IS NULL, UUID(), null)) as nose'),
    //  DB::raw('IFNULL(codes.document_detail_id, UUID()) as ddc'),
    //   DB::raw('IFNULL(ddm.document_detail_id, UUID()) as ddmi'),
    DB::raw('IF(codes.document_detail_id IS NULL,IF(ddm.document_detail_id IS NOT NULL,ddm.document_detail_id,UUID()),null) as ddmi'),
    DB::raw('IF(ddm.document_detail_id IS NULL,IF(codes.document_detail_id IS NOT NULL,codes.document_detail_id,UUID()),null) as ddc'),
      // DB::raw('codes.document_detail_id as ddc'),
      // DB::raw('ddm.document_detail_id as ddmi'),
      //'ddm.product_id as ddmpid',
      DB::raw("IF(IFNULL(dd.quanty_received, 0) + IFNULL(dd.quarantine, 0) + IFNULL(dd.damaged_cartons, 0) = dd.cartons,1,0) as complete"),
      DB::raw('COUNT(DISTINCT codes.code14) as codes'),
      DB::raw('COUNT(DISTINCT codesD.code14) as codes_damaged'),
      DB::raw('COUNT(DISTINCT codesQ.code14) as codes_quarantine'),
      DB::raw('COUNT(DISTINCT codesOk.code14) as codes_ok'),
      // DB::raw('SUM(DISTINCT IFNULL(codes.damaged, 0)) as codes_damaged'),
      // DB::raw('SUM(DISTINCT IFNULL(codes.quarantine)) as codes_quarantine'),
      DB::raw('group_concat(DISTINCT CONCAT_WS("|", pm.reference, pm.description, pm.id, ddm.quanty)) as multiple'))
      // DB::raw('group_concat(DISTINCT pm.description) as multiple_descriptions'))
      ->groupBy('ddc')
      ->groupBy('ddmi')
      // ->groupBy('nose')
      ->get();

      return $documents;
    }

    public function indexApi($request)
    {
      $documents = $this->getDocuments();

      $this->res['respuesta'] = $documents->toArray();
      $this->res['exito'] = 1;
      $this->res['mensaje'] = "";

      return $this->res;
    }

    public function storeApi($request, $documentType = DocType::Receipt)
    {
      $data = $request->all();
      // return $data;

      //$docType == DocType::Departure

        //Check if the document already exists
        $documentId = $minDate = $maxDate = null;

        DB::beginTransaction();

        try {

          if($documentType == DocType::Receipt) {
            if(array_key_exists('container_number', $data)) {
              $documentId = DB::table('wms_documents')
                            ->where('number', $data['number'])
                            ->where('container_number', $data['container_number'])
                            ->value('id');
            }
            else {
              abort(500, 'El container_number es obligatorio');
            }
            
          } else {
            if(!array_key_exists('min_date', $data)) {
              abort(500, 'El campo min_date es obligatorio');
            }

            if(!array_key_exists('max_date', $data)) {
              abort(500, 'El campo max_date es obligatorio');
            }

            $minDate = $data['min_date'];
            $maxDate = $data['max_date'];

            $documentId = DB::table('wms_documents')->where('number', $data['number'])->value('id');
          }

          if(isset($documentId)) {
            if($documentType == DocType::Receipt) {
              abort(500, 'Ya existe un documento con el número ' . $data['number'] . ' en el container ' . $data['container_number'] . '');
            } else {
              abort(500, 'Ya existe un documento con el número ' . $data['number']);
            }
            
          }

          $receiptTypeId = null;

          if(array_key_exists('receipt_type', $data) && isset($data["receipt_type"])) {
            $receiptTypeId = DB::table('wms_receipt_types')->where('name',$data['receipt_type'])->value('id');

            if(!isset($receiptTypeId)) {
              abort(500, 'No existe el tipo de recibo ' . $data['receipt_type']);
            }
          }

          $provider               = array_key_exists('provider', $data) ? $data['provider'] : null;
          $agent                  = array_key_exists('agent', $data) ? $data['agent'] : null;
          $city                   = array_key_exists('city', $data) ? $data['city'] : null;
          $agentPhone             = array_key_exists('agent_phone', $data) ? $data['agent_phone'] : null;
          $driverName             = array_key_exists('driver_name', $data) ? $data['driver_name'] : null;
          $driverId               = array_key_exists('driver_identification', $data) ? $data['driver_identification'] : null;
          $driverPhone            = array_key_exists('driver_phone', $data) ? $data['driver_phone'] : null;
          $transportCompany       = array_key_exists('transport_company', $data) ? $data['transport_company'] : null;
          $transportCompanyPhone  = array_key_exists('transport_company_phone', $data) ? $data['transport_company_phone'] : null;
          $is_partial             = array_key_exists('is_partial', $data) ? $data['is_partial'] : null;
          $empresa                = array_key_exists('empresa', $data) ? $data['empresa'] : null;
          $idPedidoSAYA           = array_key_exists('idPedidoSAYA', $data) ? $data['idPedidoSAYA'] : null;
          

          $doc = [
            'number'                  =>  $data['number'],
            'client'                  =>  $data['client'],
            'status'                  =>  'process',
            'active'                  =>  1,
            'receipt_type_id'         =>  $receiptTypeId,
            'document_type'           =>  $documentType,
            'min_date'                =>  $minDate,
            'max_date'                =>  $maxDate,
            'provider'                =>  $provider,
            'agent'                   =>  $agent,
            'city'                    =>  $city,
            'agent_phone'             =>  $agentPhone,
            'driver_name'             =>  $driverName,
            'driver_identification'   =>  $driverId,
            'driver_phone'            =>  $driverPhone,
            'transport_company'       =>  $transportCompany,
            'transport_company_phone' =>  $transportCompanyPhone,
            'is_partial'              =>  $is_partial,
            'empresa'                 =>  $empresa,
            'idPedidoSAYA'            =>  $idPedidoSAYA
            
          ];

          if($documentType == DocType::Receipt) {
            $doc['container_number'] = $data['container_number'];
          }

          //Create the document
          $documentId = DB::table('wms_documents')->insertGetId($doc);

          //Sve the detail
          $detail   = [];
          $multiple = [];
          $conti = 0;
          foreach ($data['detail'] as $d) {

          // $expiration_date        = array_key_exists('expiration_date', $d) ? $d['expiration_date'] : null;
          // $lot                    = array_key_exists('lot', $d) ? $d['lot'] : null;
          // $weight                 = array_key_exists('weight', $d) ? $d['weight'] : null;
          // $meters                 = array_key_exists('meters', $d) ? $d['meters'] : null;
            //Get the product
            $productId  = null;
            $detailRand = ''; 

            $clientId = DB::table('wms_clients')
                    ->where('identification', $d['client_nit'])
                    ->where('code', $d['client_code'])
                    ->value('id');

            if(!isset($clientId)) {
              abort(500, 'No existe el cliente con nit ' . $d['client_nit'] . ' y código '. $d['client_code'] .'');
            }

            if(array_key_exists('ean', $d) && isset($d["ean"])) {

              $productId = DB::table('wms_products')->where('ean', $d["ean"])->where('reference', $d['reference'])->first();
              
              if(!$productId) {
                abort(500, 'No existe un producto asociado al ean ' . $d['ean'] . ' y a la referencia ' . $d['reference']);
              }

              //Chequear si el pedido tiene partes
              if($documentType == DocType::Departure) {
                $compProduct      = DB::table('wms_compound_product')->where('parent_product_id', $productId->id)->get();
                $parts            = count($compProduct);
                // $d['parts']       = $parts;
                $productId->parts = $parts;
              }

              if(!array_key_exists('parts', $d)) {
                DB::table('wms_products')->where('ean', $d["ean"])->where('reference', $d['reference'])->update(['parts' => 0]);
              }

              // if($documentType == DocType::Departure) {
              //   //validamos si el producto tiene o no inventario
              //   $stock = DB::table('wms_stock')->where('product_id',$productId->id)->first();
  
              //   if (!$stock) {
              //     abort(500, 'El producto no tiene inventario ' . $d['ean']);
              //   }
              // }

              
              //Validar si es un set
              if($productId->parts > 0) {
                $d['parts'] = $productId->parts;
                $d["parts_cartons"] = $d["cartons"];
                $d["parts_quanty"] = $d["quanty"];
              }

              if(!array_key_exists('parts', $d)) {
                $unit = $d["cartons"] * $d["quanty"];

                if($unit != $d["unit"]) {
                  abort(500, 'Las unidades en el documento ' . $data['number'] . ' ean ' . $d['ean'] .  ' no coinciden se esperaban ' . $unit . ' y llegaron ' . $d["unit"]);
                }
              }
              
              //Chequear si es un compuesto //data->compound
              if(array_key_exists('compound', $d) && isset($d["compound"]) && $d['compound']) {
                //Nuevo ean
                $ean = $productId->ean . 'X';

                $productCompoundId = DB::table('wms_products')->where('ean', $ean)->value('id');

                if(!isset($productCompoundId)) {
                  //Nuevo producto
                  $p = [
                    'description' => 'COMP. ' . $productId->description,
                    'code'        => $productId->code,
                    'reference'   => $productId->reference,
                    'active'      => 1,
                    'ean'         => $ean ,
                    'client_id'   => $productId->client_id,
                    'product_type_id' => $product->product_type_id
                  ];
  
                  $parent = $productId->id;
                  
                  $productId = DB::table('wms_products')->insertGetId($p);

                  //relacionar como producto compuesto
                  $compund = [
                    'ean13'             => $ean,
                    'product_id'        => $productId,
                    'quanty'            => 1,
                    'parent_product_id' => $parent
                  ];

                  DB::table('wms_compound_product')->insert($compund);
                } else {
                  $productId = $productCompoundId;
                }
                
              } else {
                $productId = $productId->id;
              }

              //Chequear si es un set //parts parts_cartons
              if(array_key_exists('parts', $d)) {
                if(isset($d['parts']) && $d['parts'] > 0) {
                  //Variable para concatenar el sufijo
                  $abc = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  
                  //validar el set
                  $product = DB::table('wms_products')->where('ean', $d["ean"])->first();
  
                  if(!$product) {
                    abort(500, 'No existe un set asociado al ean ' . $d['ean']);
                  }
  
                  DB::table('wms_products')->where('id', $product->id)->update(['parts' => $d['parts']]);
  
                  for ($pix=0; $pix < $d['parts']; $pix++) { 
                    //Create new product if not exist
                    $ean = $product->ean . $abc[$pix];
                    $productCompoundId = DB::table('wms_products')->where('ean', $ean)->value('id');
  
                    if(!isset($productCompoundId)) {
                      //Nuevo producto
                      $p = [
                        'description' => 'SET ' . $product->description . ' PARTE ' .  $abc[$pix],
                        'code'        => $product->code,
                        'reference'   => $product->reference,
                        'active'      => 1,
                        'ean'         => $ean ,
                        'client_id'   => $product->client_id,
                        'product_type_id' => $product->product_type_id
                      ];
  
                      $parent = $product->id;
  
                      $productId = DB::table('wms_products')->insertGetId($p);
  
                      //relacionar como producto compuesto
                      $compund = [
                        'ean13'             => $ean,
                        'product_id'        => $productId,
                        'quanty'            => 1,
                        'parent_product_id' => $parent
                      ];
  
                      DB::table('wms_compound_product')->insert($compund);
                    } else {
                      $productId = $productCompoundId;
                    }
  
                    $detail[] = [
                      "document_id"     => $documentId,
                      "unit"            => $d["parts_cartons"], //Cantidad de cajas * quanty
                      "quanty"          => $d["parts_quanty"],//$d["quanty"],
                      "cartons"         => $d["parts_cartons"],
                      "status"          => "count",
                      "product_id"      => $productId,
                      "client_id"       => $clientId,
                      "detail_code"     => '',
                      'lot'             =>  array_key_exists('lot', $d) ? $d["lot"] : "",
                      'expiration_date' =>  array_key_exists('expiration_date', $d) ? $d["expiration_date"] : "",
                      'weight'          =>  array_key_exists('weight', $d) ? $d["weight"] : 0,
                      'meters'          =>  array_key_exists('meters', $d) ? $d["meters"] : 0
                    ];
                  }
                }
                
              }

            } else if(array_key_exists('multiple', $d)) {
              //Es multiple 
              $detailRand = Str::random(64);
              foreach($d['multiple'] as $m) {
                $productMultipleId = DB::table('wms_products')->where('ean', $m["ean"])->value('id');

                if(!isset($productMultipleId)) {
                  abort(500, 'No existe un producto asociado al ean ' . $m['ean']);
                }

                $multiple[] = [
                  'product_id'  => $productMultipleId,
                  'quanty'      => $m['quanty'],
                  'detail_code' => $detailRand
                ];
              }
            }

             //Chequear si es un set Y si lo es no insertar
             if(!array_key_exists('parts', $d)) {
               
              $detail[] = [
                "document_id"     => $documentId,
                "unit"            => $d["unit"], //Cantidad de cajas * quanty
                "quanty"          => $d["quanty"],
                "cartons"         => $d["cartons"],
                "status"          => "count",
                "product_id"      =>  $productId,
                "client_id"       => $clientId,
                "detail_code"     => $detailRand,
                'lot'             =>  array_key_exists('lot', $d) ? $d["lot"] : "",
                'expiration_date' =>  array_key_exists('expiration_date', $d) ? $d["expiration_date"] : "",
                'weight'          =>  array_key_exists('weight', $d) ? $d["weight"] : 0,
                'meters'          =>  array_key_exists('meters', $d) ? $d["meters"] : 0
                // 'lot'             =>  '',
                // 'expiration_date' =>  '0000-00-00',
                // 'weight'          =>  0,
                // 'meters'          =>  '0.00'
              ];

              //Validar si tiene compuesto eanX
              $pCpmp = DB::table('wms_products')->where('ean', $d["ean"] . 'X')->first();

              if($pCpmp) {
                //$d["compound"] = true;
                $detail[] = [
                  "document_id"     => $documentId,
                  "unit"            => $d["unit"], //Cantidad de cajas * quanty
                  "quanty"          => $d["quanty"],
                  "cartons"         => $d["cartons"],
                  "status"          => "count",
                  "product_id"      => $pCpmp->id,
                  "client_id"       => $clientId,
                  "detail_code"     => $detailRand,
                  'lot'             =>  '',
                  'expiration_date' =>  '0000-00-00',
                  'weight'          =>  0,
                  'meters'          =>  '0.00'
                ];
              }


             }
            
             $conti++;
            // R/ Unit son las unidades esperadas 
            //(quanty * cartons), quanty es la cantidad de unidades que hay por caja.
          }

         

          DB::table('wms_document_details')->insert($detail);

          $clean = [];
          //Insert the multiples
          foreach ($multiple as &$m) {
            $documentDetailId = DB::table('wms_document_details')
                                ->where('detail_code', $m['detail_code'])
                                ->value('id');

            $m['document_detail_id'] = $documentDetailId;
            $clean[] = $m['detail_code'];
          }

          DB::table('wms_document_detail_multiples')->insert($multiple);

          //clean the random
          if(count($clean) > 0) {
            DB::table('wms_document_detail_multiples')
            ->whereIn('detail_code', $clean)
            ->update(['detail_code' => '']);

            DB::table('wms_document_details')
            ->whereIn('detail_code', $clean)
            ->update(['detail_code' => '']);
          }
          
          //Insert the product features

          DB::commit();

          $this->res['respuesta'] = "Documentos guardado con éxito ";
          $this->res['exito']     = 1;
          $this->res['mensaje']   = "Documento guardado con éxito";


        } catch (\Exception $e) {
          DB::rollBack();

          $this->res['respuesta'] = "No se puede guardar el documento";
          $this->res['exito']     = 0;
          $this->res['mensaje']   = $e->getMessage();
        }

      return $this->res;
    }

    public function getDocumentContainers()
    {
      $containers = DB::table('wms_documents')
                    ->where('document_type', DocType::Receipt)
                    ->whereNotNull('container_number')
                    ->select('client', 'container_number')
                    ->distinct('container_number')
                    ->groupBy('container_number')
                    ->get();

      return $containers;
    }

    public function getDocumentsByContainer($container)
    {
      $documents = DB::table('wms_documents')
      ->where('container_number', $container)
      ->select('id as document_id', 'number', 'client')
      ->get();

      return $documents;
    }

    public function getDocumentDetails($request)
    {
        $receiptType = $request->input('receipt_type');
        $documents = Document::with('detail');

        if(isset($receiptType)) {
            $documents = $documents->where('receipt_type_id', $receiptType);
        }

        $documents = $documents->get();
        return $documents->toArray();
    }

    public function getDocumentDetailsByDocumentId($id, $request)
    {
        $withoutean14= $request->input('withoutean14');


      $query = DB::table('wms_document_details')
      ->join('wms_documents', 'wms_document_details.document_id', '=', 'wms_documents.id')
      ->leftjoin('wms_products', 'wms_document_details.product_id', '=', 'wms_products.id')
      ->leftjoin('wms_ean_codes14', 'wms_document_details.id', '=', 'wms_ean_codes14.document_detail_id')
      ->leftjoin('wms_document_detail_multiples', 'wms_document_details.id', '=', 'wms_document_detail_multiples.document_detail_id')
      ->leftjoin('wms_products as products_multiple', 'products_multiple.id', '=', 'wms_document_detail_multiples.product_id')
      ->where('wms_document_details.document_id', $id)
      // ->whereNotNull('wms_machines.warehouse_id')
      // ->whereNotNull('wms_machines.zone_id')

      // Traemos campos del detalle y del documento, adicional al momento de traer el numero de cajas validamos
      // Si ya tiene cajas validadas en el cierre wms_document_details.quanty_received y si no las tiene usamos
      // las que se especifican en el documento wms_document_details.cartons
      ->select('wms_document_details.id','wms_documents.number',
        'wms_document_details.quanty',
        'wms_document_details.cartons as original_car',
        'wms_document_details.quanty_received',
        'wms_document_details.id as document_details_id ',
        DB::raw('IF(wms_document_details.quarantine IS NOT NULL, wms_document_details.quarantine,0) as quarantine'),
        DB::raw('IF(wms_document_details.damaged_cartons IS NOT NULL, wms_document_details.damaged_cartons,0) as damaged'),
        DB::raw('IF(wms_document_details.quanty_received IS NOT NULL,
                    IF(wms_document_details.quanty_received > wms_document_details.cartons,
                          wms_document_details.cartons,
                          wms_document_details.quanty_received)
                    ,wms_document_details.cartons) - COUNT(wms_ean_codes14.id) as cartons'),
        DB::raw('COUNT(wms_ean_codes14.id) as codes'),'wms_ean_codes14.id as ean14_id',


        DB::raw('IF(wms_products.reference IS NOT NULL,
          wms_products.reference,
          GROUP_CONCAT(products_multiple.reference SEPARATOR " ")) as reference'),

          DB::raw('IF(wms_products.ean IS NOT NULL,
          wms_products.ean,
          GROUP_CONCAT(products_multiple.ean SEPARATOR " ")) as ean'),

        DB::raw('IF(wms_products.ean IS NOT NULL,
          wms_products.ean,
          GROUP_CONCAT(products_multiple.ean SEPARATOR " ")) as code'),

           DB::raw('IF(wms_document_details.damaged_cartons IS NOT NULL,
          wms_document_details.damaged_cartons,
          GROUP_CONCAT(wms_document_details.damaged_cartons SEPARATOR " ")) as damaged_cartons'),

          DB::raw('IF(wms_document_details.quarantine IS NOT NULL,
          wms_document_details.quarantine,
          GROUP_CONCAT(wms_document_details.quarantine SEPARATOR " ")) as quarantine_cant'),

        DB::raw('IF(wms_products.description IS NOT NULL,
          wms_products.description,
          GROUP_CONCAT(products_multiple.description SEPARATOR "-")) as description'),

        'wms_document_details.product_id')
      ->orderBy('wms_document_details.id')
      ->groupBy('wms_document_details.id');

      // Con esta validacion filtramos si queremos trarer el detalle de un documento que no tenga creado un ean14
      if(!empty($withoutean14)) {
        // $query->where('ean14_id IS NULL');
        $query->havingRaw('(cartons > 0 OR  quarantine > 0 OR damaged > 0) and ean14_id IS NULL');
      }

      $details  = $query->get();

      foreach ($details as $key => $value) {
        $documentDetail = DocumentDetail::findOrFail($value->id);
        $products = $documentDetail->detailMultiple()->get()->toArray();
        $details[$key]->products = [];
        if (count($products) > 0) {
          $details[$key]->products = $products;
        }else{
            $product = ['product_id'=>$details[$key]->product_id,'quanty'=>$details[$key]->quanty];
            array_push($details[$key]->products, $product);
        }

      }

      return $details;
    }

    public function getPlainDocumentsReceipt()
    {
      $documents = DB::table('wms_documents')
      ->select('number', 'count_status', 'id', 'client', 'container_number')
      ->where('document_type', DocType::Receipt)
      ->orderBy('number')
      ->get();

      return $documents;
    }

    public function getPlainDocumentsReceiptBytype($id)
    {
      
      if ($id === PackagingType::Logistica) {
        $documents = DB::table('wms_documents')
      ->join('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
      ->join('wms_products', 'wms_document_details.product_id', '=', 'wms_products.id')
      ->select('wms_documents.number', 'wms_documents.count_status', 'wms_documents.id', 'wms_documents.client', 'wms_documents.container_number',DB::raw('SUM(wms_products.serial_shipping) as ship'))
      ->where('wms_documents.document_type', DocType::Receipt)
      //->where('wms_products.serial_shipping',0)
      // ->where('wms_products.serial_shipping','<',1)
      ->orderBy('wms_documents.number')
      ->groupBy('wms_documents.number')
      // ->havingRaw('SUM(wms_products.serial_shipping) = 0')
      //->groupBy('wms_products.serial_shipping')
      ->get();
      }elseif($id === PackagingType::Empaque){
        $documents = DB::table('wms_documents')
      ->join('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
      ->join('wms_products', 'wms_document_details.product_id', '=', 'wms_products.id')
      ->select('wms_documents.number', 'wms_documents.count_status', 'wms_documents.id', 'wms_documents.client', 'wms_documents.container_number',DB::raw('SUM(wms_products.serial_shipping) as ship'))
      ->where('wms_documents.document_type', DocType::Receipt)
      //->where('wms_products.serial_shipping',0)
      // ->where('wms_products.serial_shipping','<',1)
      ->orderBy('wms_documents.number')
      ->groupBy('wms_documents.number')
      ->havingRaw('SUM(wms_products.serial_shipping) = 0')
      //->groupBy('wms_products.serial_shipping')
      ->get();
      }else {
        // return $id;
      $documents = DB::table('wms_documents')
      ->join('wms_document_details', 'wms_documents.id', '=', 'wms_document_details.document_id')
      ->join('wms_products', 'wms_document_details.product_id', '=', 'wms_products.id')
      ->select('wms_documents.number', 'wms_documents.count_status', 'wms_documents.id', 'wms_documents.client', 'wms_documents.container_number')
      ->where('wms_documents.document_type', DocType::Receipt)
      ->where('wms_products.serial_shipping',1)
      ->orderBy('wms_documents.number')
      ->groupBy('wms_documents.number')
      ->get();
      }
      

      return $documents;
    }

    public function suggestionPalletPicking($scheduleId)
    {

      $parent_schedule = Schedule::where('id',$scheduleId)->first();
      // return $parent_schedule->parent_schedule_id;
        $enlist = DB::table('wms_enlist_products')
        ->where('schedule_id', $parent_schedule->parent_schedule_id)
        ->where('status', 'planed')
        ->select('product_id', 'order_quanty')->get();

        if(empty($enlist)) {
          return;
        }

        $result = [];

        foreach ($enlist as $value) {
          $productId = $value->product_id;
          $quanty = $value->order_quanty;

          $query = 'select t.product_id,t.zone_position_id,t.code128_id,t.code14_id,t.quanty,wms_zones.warehouse_id,\'dispatch\' as concept, t.id as stock_id 
                      from (select t.*, (@sum := @sum + quanty) as cume_stock
                          from wms_stock t cross join
                              (select @sum := 0) params
                              
                                  where product_id = '.$productId.' and code128_id is not NULL
                          order by zone_position_id 
                          ) t
                          inner join wms_zone_positions on wms_zone_positions.id= t.zone_position_id
                          inner join wms_zones on wms_zones.id= wms_zone_positions.zone_id
                      where cume_stock < '.$quanty.' or (cume_stock >= '.$quanty.' and cume_stock - quanty < '.$quanty.')
                      ';

          $results = DB::select( DB::raw($query));

          $result =  array_merge($result, $results);
          //$result[] = $results;
        }

        //Insertar en transition
        $result= json_decode( json_encode($result), true);
        DB::table('wms_stock_transition')->insert(array_map(function ($value)
        {
          unset($value['stock_id']);
          return $value;
        }, $result));

        //Remover del stock
        DB::table('wms_stock')->whereIn('id', array_map(function ($value)
        {
          return $value['stock_id'];
        }, $result))->delete();

        DB::table('wms_enlist_products')
            ->where('schedule_id', $parent_schedule->parent_schedule_id)
            ->update(['status' => 'not_planned']);

        return $result;
        //return "ok";
    }

    public function suggestionPalletPickingNew($scheduleId)
    {

      // $parent_schedule = Schedule::where('id',$scheduleId)->first();
      // return $parent_schedule->parent_schedule_id;
        $enlist = DB::table('wms_enlist_products')
        ->where('schedule_id', $scheduleId)
        // ->where('schedule_id', $parent_schedule->parent_schedule_id)
        // ->where('status', 'planed')
        ->select('product_id', 'order_quanty')->get();

        if(empty($enlist)) {
          return;
        }

        $result = [];

        foreach ($enlist as $value) {
          $productId = $value->product_id;
          $quanty = $value->order_quanty;

          $query = 'select t.product_id,t.zone_position_id,t.code128_id,t.code14_id,t.quanty,wms_zones.warehouse_id,\'dispatch\' as concept, t.id as stock_id 
                      from (select t.*, (@sum := @sum + quanty) as cume_stock
                          from wms_stock t cross join
                              (select @sum := 0) params
                              
                                  where product_id = '.$productId.' and code128_id is not NULL
                          order by zone_position_id 
                          ) t
                          inner join wms_zone_positions on wms_zone_positions.id= t.zone_position_id
                          inner join wms_zones on wms_zones.id= wms_zone_positions.zone_id
                      where cume_stock < '.$quanty.' or (cume_stock >= '.$quanty.' and cume_stock - quanty < '.$quanty.')
                      ';

          $results = DB::select( DB::raw($query));

          $result =  array_merge($result, $results);
        }

        // DB::table('wms_enlist_products')
        //     ->where('schedule_id', $parent_schedule->parent_schedule_id)
        //     ->update(['status' => 'not_planned']);

        return $result;
        //return "ok";
    }

}
