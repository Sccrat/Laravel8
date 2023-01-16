<?php

namespace App\Common;
use DB;

/**
 * Custom class for confoguration settings stored in the table wms_settings
 */
class Products
{
  public static function GetProcutcsByPositionId($positionid)
  {
    $codes14 = DB::table('wms_stock')
    ->join('wms_products', 'wms_stock.product_id', '=', 'wms_products.id')
    ->join('wms_product_sub_types', 'wms_product_sub_types.id', '=', 'wms_products.product_sub_type_id')

    ->where('wms_stock.zone_position_id', $positionid)
    ->select('wms_product_sub_types.name as product_type_name','wms_stock.quanty','wms_products.id as product_id','wms_products.reference','wms_products.description','wms_products.code','wms_products.size','wms_products.colour')
    ->orderBy('wms_products.id');

    $allcodes  = $codes14->get();
    return $allcodes;
  }
}
