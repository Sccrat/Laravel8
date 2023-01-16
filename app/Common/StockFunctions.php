<?php
namespace App\Common;

use DB;
use App\Models\Stock;
use App\Models\Schedule;
use App\Models\ScheduleTransition;
use App\Models\MergedPosition;
use App\Models\EanCode14;
use App\Models\EanCode14Detail;
use App\Models\EanCode128;
use App\Models\PositionFeature;
use App\Models\ZonePosition;
use App\Models\StockTransition;
use App\Models\StockReprocess;
use App\Models\Pallet;
use App\Common\Settings;
use App\Common\Codes;
use App\Enums\SettingsKey;
use App\Enums\ScheduleType;
use App\Enums\ScheduleAction;
use App\Enums\ScheduleStatus;


class StockFunctions {

    public static function findStockByPosition($position_id) {
    	$mergeRange = MergedPosition::where('from_position_id', '<=',$position_id)->
                                                where('to_position_id', '>=' ,$position_id)->
                                                orderBy('to_position_id', 'desc')->first();
        $stockInformation = [];

        if (!empty($mergeRange)) {
        	$stockInformation = Stock::whereBetween('zone_position_id', array(
                      $mergeRange->from_position_id,
                      $mergeRange->to_position_id))->get();
        }else{
        	$stockInformation = Stock::where('zone_position_id', $position_id)->get();
        }
		return $stockInformation;
    }
    public static function wherePosition($position_id,$stockObj)
    {

        $mergeRange = MergedPosition::where('from_position_id', '<=',$position_id)->
                                                where('to_position_id', '>=' ,$position_id)->
                                                orderBy('to_position_id', 'desc')->first();

        if (!empty($mergeRange)) {
            $stockObj = $stockObj->whereBetween('zone_position_id', array(
                      $mergeRange->from_position_id,
                      $mergeRange->to_position_id));
        }else{
            $stockObj = $stockObj->where('zone_position_id', $position_id);
        }

        return $stockObj;
    }

    /**
   * ¡¡¡¡¡¡¡¡¡¡¡IMPORTANTE!!!!!!!!!!!!!!
   *
   * A esta función se la debería invocar DESPUES de quitar la caja de wms_stock
   * porque lo que hace es liberar la posición (si corresponde)
   */
  public static function removeVolumeFromPosition($code128Id = NULL, $zonePositionId,$companyId) {
    /**
     * Si no viene el Ean128, asumo que acaba de quitar de stock un Ean14.
     * Entonces, sólo chequeo el stock en esa posición.
     */
    if (is_null($code128Id)) {
      // Chequeo si la posición quedó vacía
      self::verifyPositionHasStock($zonePositionId);
      return;
    }

    // Busco el Ean128
    $ean128 = EanCode128::with('container.container_features.feature','merged_position')->find($code128Id);

    $positionsFeatures = PositionFeature::with('feature');

    // Si el Ean128 ocupaba 1 (una) sola posición...
    if (empty($ean128->merged_position)) {
      $positionsFeatures = $positionsFeatures->where('zone_position_id', $zonePositionId);

      // Chequeo si la posición quedó vacía
      self::verifyPositionHasStock($zonePositionId,$companyId);
    }
    // Si se detecta que el Ean128 estaba almacenado en una posición combinada...
    else {
      $positionsFeatures = $positionsFeatures->whereBetween('zone_position_id', [$ean128->merged_position->from_position_id, $ean128->merged_position->to_position_id]);

      // Chequeo si la posición quedó vacía
      $this::verifyPositionHasStock(array($ean128->merged_position->from_position_id, $ean128->merged_position->to_position_id));

      // Elimino el registro en 'wms_merged_positions'
      $ean128->merged_position-delete();
    }
    // Busco las características de la posición
    $positionsFeatures = $positionsFeatures->get();

    // Features Setting Keys
    $settingsObj = new Settings($companyId);
    $featureCapacity = $settingsObj->get(SettingsKey::FEATURE_CAPACITY);
    $featureHeight = $settingsObj->get(SettingsKey::FEATURE_HEIGHT);

    // Actualizo el 'free_value' en 'wms_position_features'
    foreach($positionsFeatures as &$posFeature) {
      switch ($posFeature->feature->name) {
        // Capacidad
        case $featureCapacity:
          $posFeature->increment('free_value', $ean128->weight);
          break;
        // Altura
        case $featureHeight:
          $posFeature->increment('free_value', $ean128->height);
          break;
      }
    }
  }

  private static function verifyPositionHasStock($zonePositionId,$companyId) {
    /**
     * Busco la/s posición/es recibidas que NO tengan stock y
     * que NO sean de los siguientes conceptos: [Área de Trabajo].
     * Ya que, a las posiciones con estos conceptos, no se les cambia el estado.
     */

    $settingsObj = new Settings($companyId);
    $excludedConcepts[] = $settingsObj->get('zone_concept_work_area');

    $zonePositionsWithoutStock = ZonePosition::doesnthave('stocks')
      ->whereDoesntHave('concept', function($q) use($excludedConcepts) {
        $q->whereIn('name', $excludedConcepts);
      });

    if (!is_array($zonePositionId)) {
      $zonePositionsWithoutStock = $zonePositionsWithoutStock->where('id', $zonePositionId);
    }
    else {
      $zonePositionsWithoutStock = $zonePositionsWithoutStock->whereBetween('id', $zonePositionId);
    }

    // Si hay posiciones sin stock (vacias), las marco como libres.
    $zonePositionsWithoutStock->update(['concept_id' => NULL]);
  }

  public static function transformResultDecrementStock($zonePositionId, $productId, $code14Id)
  {
    // Quito la caja (EAN14) original de stock porque se va a convertir en unidades (EAN13) en stock.
    Stock::where('zone_position_id', $zonePositionId)
    ->where('product_id', $productId)
    ->where('code14_id', $code14Id)
    ->delete();

    // Elimino el EAN14
    $ean14 = EanCode14::find($code14Id);
    if (!empty($ean14)) {
      $ean14->delete();
    }
  }

  public static function printCodeReprocess($packaging, $userId, $zonePositionId)
  {
    $arrRes = [];
    DB::transaction(function () use($packaging, &$arrRes, $userId, $zonePositionId) {

      $savedcode14 = self::transformGenerateEan14($packaging);

      // Busco la información de la posición para extraer el warehouse_id
      $zonePosition = ZonePosition::with('zone')->findOrFail($zonePositionId);

      // TODO: Agregar enum en los estados y concentos de las transiciones
      $objTransition = [
        'product_id'=>$packaging['product_id'],
        'zone_position_id' => $zonePositionId,
        'code14_id'=>$savedcode14->id,
        'quanty'=>$packaging['quanty'],
        'action'=>'income',
        'concept'=>'transform',
        'user_id' => $userId,
        'warehouse_id' => $zonePosition->zone->warehouse_id
      ];

      StockTransition::create($objTransition);

      $arrRes['html'] = Codes::getEan14Html($savedcode14->id);
      $arrRes['obj'] = $packaging;
      $arrRes['ean14'] = [
        'code14' => $savedcode14->code14,
        'id' => $savedcode14->id
      ];
    });

    return $arrRes;
  }

  public static function transformGenerateEan14($box)
  {
    $newstructurecode14 = '7704121';

    $new14 = [
      'code14' => $newstructurecode14,
      'document_detail_id' => NULL,
      'quanty' => $box['quanty'],
      'container_id' => $box['container_id'],
      'stored' => 1
    ];

    $savedcode14 = EanCode14::create($new14);
    $newsavedcode14 = $savedcode14['code14'].$savedcode14['id'];
    $savedcode14->code14 =$newsavedcode14;
    $savedcode14->save();

    $code14Detail = [
      'ean_code14_id' => $savedcode14['id'],
      'product_id' => $box['product_id'],
      'quanty'=>$box['quanty']
    ];
    EanCode14Detail::create($code14Detail);

    return $savedcode14;
  }

  /**
   * Esta función se utiliza para generar el flujo para devolver
   * la caja/bulto de RR (ya reprocesada), a su posición de origen.
   */
  public static function reprocessScheduleBackToSource($code14Id)
  {
    $stockReprocess = StockReprocess::with('schedule_target.child_schedules', 'ean14.stock', 'zone_position')->where('code14_id', $code14Id)->first();
    $reciptRRSchedule = NULL;
    /**
     * Busco la tarea hija que sea de tipo Stock -> Reprocess (La tarea de quien recibió en RR).
     * Esa tarea tiene el user_id que necesito para asignar la tarea de regreso.
     */
    foreach($stockReprocess->schedule_target->child_schedules as $schedule) {
      if ($schedule->schedule_type === ScheduleType::Stock && $schedule->schedule_action === ScheduleAction::Reprocess) {
        $reciptRRSchedule = $schedule;
        break;
      }
    }

    // Genero la tarea para llevar el EAN14 a la posición de origen.
    $deliverRRDoneScheduleModel = array(
      'name' => 'Reubicar EAN14 '. $stockReprocess->ean14->code14 . ' en la posición ' . $stockReprocess->zone_position->code,
      'schedule_type' => ScheduleType::Stock,
      'schedule_action' => ScheduleAction::Deliver,
      'status' => ScheduleStatus::Process,
      'user_id' => $reciptRRSchedule->user_id,
      'parent_schedule_id' => $stockReprocess->schedule_source_id
    );
    $deliverRRDoneScheduleId = Schedule::create($deliverRRDoneScheduleModel)->id;

    // Asocio la caja a la tarea
    $ean14ToSchedule = array(
      'schedule_id' => $deliverRRDoneScheduleId,
      'stock_id' => $stockReprocess->ean14->stock[0]['id']
    );
    ScheduleTransition::create($ean14ToSchedule);

    // Actualizo stock_reprocess.schedule_target_id
    $stockReprocess->schedule_target_id = $deliverRRDoneScheduleId;
    $stockReprocess->save();
  }

  public static function removeEan14FromEan128($ean14) {
    $pallet = Pallet::where('code14_id', $ean14)->first();

    if (empty($pallet)) { return; }

    $ean128Id = $pallet->code128_id;

    // Quito la caja (EAN14) del pallet (EAN128)
    $pallet->delete();

    // Chequeo que el pallet (EAN128) aún tenga cajas (EAN14).
    $ean128 = EanCode128::has('ean14')->where('id', $ean128Id)->first();

    // Si aún tiene cajas (EAN14), le hago un reset al peso y alto
    if (!empty($ean128)) {
      $ean128->update([
        'weight' => NULL,
        'height' => NULL
      ]);
    }
    // Sino, lo borro.
    else {
      EanCode128::findOrFail($ean128Id)->delete();
    }

    return;
  }
}
