<?php

namespace App\Common;
use App\Models\StructureCode;
use App\Models\EanCode14;
use App\Models\EanCode128;
use DB;

/**
 * Custom class for confoguration settings stored in the table wms_settings
 */
class Codes
{

  public static function GetStructureCode($packaging_type)
  {
    $StructureCode = StructureCode::with('ia_code')
    ->where('packaging_type', $packaging_type)
    ->orderBy('id')
    ->get();
    return $StructureCode->toArray();
  }

  public static function GetCode128($id)
  {
    $code128 = EanCode128::with('container')->findOrFail($id);
    return $code128->toArray();
  }

  public static function GetCode14($id)
  {
    $codes14 = DB::table('wms_ean_codes14')
    ->join('wms_containers', 'wms_ean_codes14.container_id', '=', 'wms_containers.id')
    ->join('wms_container_types', 'wms_containers.container_type_id', '=', 'wms_container_types.id')
    ->leftjoin('wms_document_details', 'wms_ean_codes14.document_detail_id', '=', 'wms_document_details.id')
    ->leftjoin('wms_documents', 'wms_document_details.document_id', '=', 'wms_documents.id')
    // Se cambia la relacion entre los productos y el codigo para que se haga directamente por la nueva columna product_id ya que hay codigos que no tiene detalle del documento como los generados apartir de una transformacion
    ->leftjoin('wms_products', 'wms_ean_codes14.product_id', '=', 'wms_products.id')
    // ->leftjoin('wms_products', 'wms_document_details.product_id', '=', 'wms_products.id')

    ->where('wms_ean_codes14.id', $id)
    // ->whereNotNull('wms_machines.warehouse_id')
    // ->whereNotNull('wms_machines.zone_id')
    ->select('wms_documents.number','wms_document_details.id as document_details_id ','wms_document_details.cartons','wms_products.id as product_id','wms_products.reference','wms_products.description','wms_ean_codes14.id as ean_code14_id','wms_ean_codes14.code14','wms_ean_codes14.code13','wms_ean_codes14.canceled','wms_ean_codes14.quanty','wms_container_types.id as container_type_id','wms_container_types.name as container_type_name','wms_container_types.code_container_type as container_type_code','wms_containers.id as container_id','wms_containers.name as container_name','wms_containers.code as container_code')
    ->orderBy('wms_document_details.id');

    $allcodes  = $codes14->get();
    return $allcodes;
  }

  public static function GetCode14ByCode($code14)
  {
    $codes14 = DB::table('wms_ean_codes14')
    ->join('wms_containers', 'wms_ean_codes14.container_id', '=', 'wms_containers.id')
    ->join('wms_container_types', 'wms_containers.container_type_id', '=', 'wms_container_types.id')
    ->leftjoin('wms_document_details', 'wms_ean_codes14.document_detail_id', '=', 'wms_document_details.id')
    ->leftjoin('wms_documents', 'wms_document_details.document_id', '=', 'wms_documents.id')
    // Se cambia la relacion entre los productos y el codigo para que se haga directamente por la nueva columna product_id ya que codigos que no tiene detalle del documento como los generados apartir de una transfomracion
    ->join('wms_ean_codes14_detail', 'wms_ean_codes14.id', '=', 'wms_ean_codes14_detail.ean_code14_id')

    ->leftjoin('wms_products', 'wms_ean_codes14_detail.product_id', '=', 'wms_products.id')
    // ->leftjoin('wms_products', 'wms_document_details.product_id', '=', 'wms_products.id')

    ->where('wms_ean_codes14.code14', $code14)
    // ->whereNotNull('wms_machines.warehouse_id')
    // ->whereNotNull('wms_machines.zone_id')
    ->select(
      'wms_documents.number',
      'wms_document_details.id as document_details_id ',
      'wms_document_details.cartons',

      'wms_products.id as product_id',
      'wms_products.reference',
      'wms_products.ean as ean13',
      'wms_products.description',

      'wms_ean_codes14.id as ean_code14_id',
      'wms_ean_codes14.code14',

      // 'wms_ean_codes14.code13',
      'wms_ean_codes14.canceled',
      'wms_ean_codes14.quanty',

      'wms_container_types.id as container_type_id',
      'wms_container_types.name as container_type_name',
      'wms_container_types.code_container_type as container_type_code',
      'wms_containers.id as container_id',
      'wms_containers.name as container_name',
      'wms_containers.code as container_code'
      )
    ->orderBy('wms_document_details.id');

    $allcodes  = $codes14->get();
    return $allcodes;
  }

  public function generateCodeEan14($codes,$container_id)
  {
      $html = '';
      $ean14Base = '7704121';
      if (is_array($codes)) {
          foreach ($codes as $key => $code) {
              $quantyproducts = $code['quanty'];


          }
      }

      return $html;
  }

  public static function getEan14Html($ean14_id)
  {
    $code14 = EanCode14::with('product')->findOrFail($ean14_id);
    $html = '';


    // $html .='<div class="row" style="border: solid 1px black;margin:10px;">';
    // $html .='<div style="border:solid 1px black;min-height:30px" class="col-xs-12 text-center cii-title">C.I IBLU S.A.S </div>';
    // $html .='<div style="border:solid 1px black;" class="col-xs-12"><barcode render="img" type="code128b" string="' . $code14['code14'] . '"options="options"></barcode></div>';
    // $html .='<div style="border:solid 1px black;" class="col-xs-12"> ';
    // $html .='<div class="row" style="border-bottom: 1px solid black;font-weight: bold;"> <div class="col-xs-6" >Cod. Barras</div><div class="col-xs-2 text-right" style="border-left: 1px solid black;border-right: 1px solid black;font-weight: bold;">Cantidad</div><div class="col-xs-2 text-center" style="border-right: 1px solid black;font-weight: bold;">Referencia</div><div class="col-xs-2 text-center"style="font-weight: bold;">Descripción</div></div>';
    // $html .='<div class="row barcode-row">  <div class="col-xs-6" style="padding: 10px;"><barcode render="img" type="code128b" string="' . $code14['code13'] . '"options="options"></barcode></div><div class="col-xs-2 text-right text-code" style="border-left: 1px solid black;border-right: 1px solid black;">' . $code14['quanty'] . '</div><div class="col-xs-2 text-center text-code" style="border-right: 1px solid black;">' . $code14['product']['reference'] . '</div><div class="col-xs-2 text-center text-code ">' . $code14['product']['description'] . '</div></div>';
    // $html .='</div>';
    // $html .='</div>';

    $html .='<table>';
    $html .='<thead>';
    $html .='<tr><td style="border: 1px solid;text-align:center"><h2>ARTMODE</h2></td></tr>';
    $html .='</thead>';
    $html .='<tbody>';
    $html .='<tr><td style="padding:10px 5px;text-align:center"><barcode render="img" type="code128b" string="'.$code14['code14'].'"options="options"></barcode></td></tr>';
    $html .='<tr style="text-align:center"><td>'.$code14['code14'].'</td></tr>';


    if ($code14['quarantine']) {
      $html .='<tr><td style="border: 1px solid;padding:5px;text-align:center"><h2>CUARENTENA</h2></td></tr>';
    }
    if ($code14['damaged']) {
      $html .='<tr><td style="border: 1px solid;padding:5px;text-align:center"><h2>DEFECTUOSA</h2></td></tr>';
    }

    $html .='<tbody>';
    $html .='</table>';



    return $html;
  }

  public static function getPositionHtml($code,$level,$module,$row,$position)
  {
    // $code14 = EanCode14::with('product')->findOrFail($ean14_id);
    $html = '';

    $html .='<table>';
    // $html .='<thead>';
    $html .='<tr><td style="border: 1px solid;text-align:center"><h4>ART MODE</h4></td></tr>';
    $html .='<tr style="text-align:center"><td>'."fila: ".$row." "."Modulo: ".$module." "."nivel: ".$level." "."Posición: ".$position." ".'</td></tr>';
    $html .='<tr><td style="padding:10px 5px;text-align:center"><barcode render="img" type="code128b" string="'.$code.'" options="options"></barcode></td></tr>';
    $html .='<tr style="text-align:center"><td>'.$code.'</td></tr>';
    $html .='</table>';



    return $html;
  }

  public static function getEan128Html($ean128_id)
  {
    $code128 = EanCode128::with('ean14')->findOrFail($ean128_id);
    $html = '';

    // $html .='<div class="row" style="border: solid 1px black;margin:10px;">';
    // $html .='<div style="border:solid 1px black;min-height:30px" class="col-xs-12 text-center cii-title">C.I IBLU S.A.S</div>';
    // $html .='<div style="border:solid 1px black;" class="col-xs-12"><barcode render="img" type="code128b" string="' . $newsavedcode128 . '"options="options"></barcode></div>';
    // $html .='<div style="border:solid 1px black;" class="col-xs-12"/>';

    // $html .='<div style="border:solid 1px black;" class="col-xs-12">';
    // $html .='<div class="col-xs-6">Cantidad de EAN14 : '.count($detailSelected).'</div>';
    // $html .='</div>';


    $html .='<table>';
    $html .='<thead>';
    $html .='<tr ><td style="border: 1px solid;text-align:center"><h2>SOBERANA S.A</h2></td></tr>';
    $html .='</thead>';
    $html .='<tbody>';
    $html .='<tr><td style="padding:10px 5px;text-align:center"><barcode render="img" type="code128b" string="'.$code128->code128.'"options="options"></barcode></td></tr>';
    $html .='<tr style="text-align:center"><td>'.$code128->code128.'</td></tr>';
    $html .='<tr><td style="border: 1px solid;padding:5px;">Cantidad de EAN14 :'.count($code128->ean14).'</td></tr>';
    $html .='<tbody>';
    $html .='</table>';



    return $html;
  }

  public static function getHtmlFromCode($code)
  {
          $html = '';
          $html .='<table>';
          $html .='<thead>';
          $html .='<tr><td style="border: 1px solid;text-align:center"><h2>C.I IBLU S.A.S</h2></td></tr>';
          $html .='</thead>';
          $html .='<tbody>';
          $html .='<tr><td style="padding:10px 5px;text-align:center"><barcode render="img" type="code128b" string="'.$code.'"options="options"></barcode></td></tr>';
          $html .='<tr style="text-align:center"><td>'.$code.'</td></tr>';

          $html .='<tbody>';
          $html .='</table>';

          return $html;
  }


}
