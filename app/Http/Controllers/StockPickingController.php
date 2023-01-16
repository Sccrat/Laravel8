<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\EanCode13;
use App\Models\EanCode14;
use App\Models\EanCode14Detail;
use App\Models\EanCode128;
use App\Models\Pallet;
use App\Models\Stock;
use App\Models\PickingCount;
use App\Models\StockMovement;
use App\Models\StockTransition;
use App\Models\Charge;
use App\Common\Settings;
use App\Models\ZonePosition;
use App\Models\ZoneConcept;
use App\Models\StructureCode;
use App\Models\Suggestion;
use App\Models\Product;
use App\Models\SamplesDetail;
use App\Models\Schedule;
use App\Models\ScheduleTransform;
use App\Models\ScheduleTransformDetail;
use App\Models\ScheduleTransformResult;
use App\Models\ScheduleTransformResultPackaging;
use App\Models\ScheduleUnjoinDetail;
use App\Models\JoinReferences;
use App\Models\User;
use DB;
use App\Common\Codes;

use App\Common\StockFunctions;
use App\Enums\PackagingType;
use App\Enums\ScheduleAction;
use App\Enums\ScheduleType;
use App\Enums\ScheduleStatus;
use App\Common\SchedulesFunctions;
use App\Enums\PackagingStatus;
use App\Enums\TransformDetailType;
use App\Enums\TransformDetailStatus;
use App\Enums\TypeTransform;
use App\Models\ContainerFeature;
use App\Models\PositionFeature;
use App\Enums\SettingsKey;
use Log;
use App\Models\MergedPosition;
use App\Models\ScheduleTransition;
use App\Models\StockPickingConfig;
use App\Models\ScheduleTransformValidateAdjust;
use App\Models\StockPickingConfigProduct;
use App\Models\ScheduleCountPosition;
use App\Common\PickingConfig;
use App\Models\StockReprocess;
use App\Models\ScheduleCountBoxes;
use DateTimeZone;
use Carbon\Carbon;

class StockPickingController extends BaseController
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
        $data = $request->all();
        $warehouseId = $data['warehouse_id'];

        //Get the user id (etiqueteo y empaque)

        $settingsObj = new Settings();
        $chargeUserName = $settingsObj->get('stock_group');
        // $user = DB::selectOne("SELECT u.id from wms_personal p join wms_groups g on g.id=p.group_id join users u on p.id=u.personal_id where p.warehouse_id=12 and g.`name`=$chargeUserName");


        $user = User::whereHas('person.group', function ($q) use ($chargeUserName)
        {
          $q->where('name', $chargeUserName);
        })->whereHas('person', function ($q) use ($warehouseId)
        {
          $q->where('warehouse_id', $warehouseId);
        })->first();

        if(empty($user)) {
          return $this->response->error('user_not_found', 404);
        }


        $taskSchedules = [
               'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0].' '.explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
               'name' => 'Reabastecer zona de picking:',
               'schedule_type' => ScheduleType::Restock,
               'schedule_action' => ScheduleAction::Store,
               'status' => ScheduleStatus::Process,
               'user_id' => $user->id
             ];

       $schedule = Schedule::create($taskSchedules);

       $data['schedule_id'] = $schedule->id;
       $config = StockPickingConfig::create($data);
       $config->stock_picking_config_product()->createMany($data['products_id']);
       return $this->response->created();


    }






    public function getQuantyStockByZoneProduct($zone_id,$product_id)
    {
        $zone = Zone::with('zone_positions')->findOrFail($zone_id);

        $positions = $zone->zone_positions;

        $positions_id_in = [];
        foreach ($positions as $key => $value) {
            $positions_id_in[] = $value->id;
        }

        $stock = Stock::where('product_id',$product_id)->whereIn('zone_position_id',$positions_id_in)->get()->toArray();
        $quanty = 0;
        foreach ($stock as $keyStoc => $box) {
            $quanty += (int)$box['quanty'];
        }
        return $quanty;
    }

    public function getallConfigurationPicking()
    {



      $config = StockPickingConfigProduct::with('zone_position.stocks','product.stock','stock_picking_config.category','stock_picking_config.product')
      ->whereHas('stock_picking_config', function ($query)  {
        $query->where('status','process');
      })
      ->get();




      return $config->toArray();


    }
    public function getPickingCountUnits($id)
    {
       $count = ScheduleCountBoxes::with('product.picking_count')
       ->whereHas('product.picking_count', function ($query)  {
         $query->where('status','count');
       })
       ->where('schedule_id', $id)->get();

      //  $mira = PickingCount::with('schedule_picking_count')->where('schedule_id', $id)->get();
       return $count->toArray();
    }
    public function UpdateCount2(Request $request)
    {
      $data = $request->all();
      $product_id= array_key_exists('product_id', $data) ? $data['product_id'] : null;
      $conteo = array_key_exists('conteo', $data) ? $data['conteo'] : null;


      $config = PickingCount::where('product_id', $product_id)->update([
        'count2' => $conteo

      ]);
      //  return $count->toArray();
    }
    public function updateStatusCountPosition(Request $request)
    {
      $data = $request->all();
      // $value = $data['value'];
      $value = array_key_exists('value', $data) ? $data['value'] : null;
      // $id = $data['id'];
      $id = array_key_exists('id', $data) ? $data['id'] : null;
      // $count1 = $data['count1'];
      $count1 = array_key_exists('count1', $data) ? $data['count1'] : null;
      // $count2 = $data['count2'];
      $count2 = array_key_exists('count2', $data) ? $data['count2'] : null;
      $realcount = array_key_exists('realcount', $data) ? $data['realcount'] : null;
      $schedule_id = array_key_exists('schedule_id', $data) ? $data['schedule_id'] : null;
      $product_id = array_key_exists('product_id', $data) ? $data['product_id'] : null;
      // $schedule_id = $data['schedule_id'];
      // $product_id = $data['product_id'];

      $partial_count = array_key_exists('partial_count', $data) ? $data['partial_count'] : null;

      if (isset($value)) {
        $status= Stock::where('id', $id)
        ->update(['status' => $value]);
      }

      if(isset($partial_count) && isset($schedule_id))
      {
        $count = PickingCount::where('product_id',$product_id)->where('status','count')->first();
        if($count)
        {
          $picking= PickingCount::where('product_id', $product_id)->where('schedule_id', $schedule_id)
          ->update(['partial_count' => $partial_count]);
        }else {
          $objeto = [
            "schedule_id"=>$schedule_id,
            "partial_count"=>$partial_count,
            "product_id"=>$product_id,
            "real_count"=>$realcount
          ];
          $picking = PickingCount::create($objeto);
        }


      }

      if (isset($product_id)) {

        $count = PickingCount::where('product_id',$product_id)->first();


      if (!$count) {

        $objeto = [
          "schedule_id"=>$schedule_id,
          "count1"=>$count1,
          "product_id"=>$product_id,
          "real_count"=>$realcount
        ];
        $picking = PickingCount::create($objeto);

      }else {

        $picking= PickingCount::where('product_id', $product_id)->where('schedule_id', $schedule_id)
        ->update(['count2' => $count2]);

      }
}


      // return $status;
    }
    public function getStatusCountPositionByTaskId(Request $request)
    {

      $data = $request->all();
      // $schedule_id = $data['schedule_id'];
      $schedule_id = array_key_exists('schedule_id', $data) ? $data['schedule_id'] : null;
      $product_id = array_key_exists('product_id', $data) ? $data['product_id'] : null;

      // $product_id = $data['product_id'];

      if(isset($schedule_id) && isset($product_id))
      {
        $config = PickingCount::where('schedule_id', $schedule_id)->where('product_id', $product_id)->first();

      }

      return $config->toArray();


    }
    public function getScheduleById(Request $request)
    {
      $data = $request->all();
      $schedule_id = $data['task'];


      $config = Schedule::with('schedule_transition.stock_transition.product','schedule_transition.stock_transition.ean14','schedule_transition.stock_transition.ean128','parent_schedule.schedule_transition.stock_transition.product','parent_schedule.schedule_transition.stock_transition.ean14', 'parent_schedule.schedule_transition.stock_transition.ean14','parent_schedule.schedule_transition.stock_transition.ean128','parent_schedule.schedule_transform.scheduleTransformDetail','parent_schedule.schedule_position')->where('id', $schedule_id)->first();




      return $config->toArray();


    }
    public function getPickingStockConfig()
    {

      PickingConfig::CheckStockPickingConfig();


    }
    public function getPositionByCodePicking(Request $request)
    {

      $data = $request->all();
      // $codePosition = $data['codePosition'];
      $codePosition = array_key_exists('codePosition', $data) ? $data['codePosition'] : null;

      // $schedule_id = $data['schedule_id'];
      $schedule_id = array_key_exists('schedule_id', $data) ? $data['schedule_id'] : null;

      if(isset($codePosition) && isset($schedule_id))
      {
        $config = ZonePosition::where('code', $codePosition)->first();

        $count = ScheduleCountPosition::with('zone_position')->where('zone_position_id', $config->id)->where('schedule_id', $schedule_id)->first();

        if(!$count)
        {
            return $this->response->error('not_position_picking', 404);
        }

      }

      return $count->toArray();

      // return $config;


    }
    public function UpdateQuantyBoxes(Request $request)
    {
      $data = $request->all();
      $codes = array_key_exists('codes', $data) ? $data['codes'] : [];
      $schedule_id = array_key_exists('schedule_id', $data) ? $data['schedule_id'] : null;


          foreach ($codes as $value) {
            $config = ScheduleCountBoxes::where('product_id',$value['product_id'])->where('code14_id',$value['code14_id'])->where('schedule_id',$value['schedule_id'])->update([
              'count' => $value['quantyCount']
            ]);

          }

          $config = Schedule::where('id', $schedule_id)->update([
            'status' => 'closed'

          ]);





    }
    public function updateStatusCountPicking(Request $request)
    {
      $data = $request->all();
      $status = array_key_exists('status', $data) ? $data['status'] : null;
      $deleteAll = array_key_exists('delete', $data) ? $data['delete'] : null;
      $product_id = array_key_exists('product_id', $data) ? $data['product_id'] : null;
      $zone_position_id = array_key_exists('zone_position_id', $data) ? $data['zone_position_id'] : null;
      $schedule_id = array_key_exists('schedule_id', $data) ? $data['schedule_id'] : null;
      $count2 = array_key_exists('count2', $data) ? $data['count2'] : null;
      $realcount = array_key_exists('realcount', $data) ? $data['realcount'] : null;
      $diferencia = array_key_exists('diferencia', $data) ? $data['diferencia'] : null;
      $schedule_parent = array_key_exists('parent_schedule_id', $data) ? $data['parent_schedule_id'] : null;
      $parent_schedule_id = array_key_exists('parent_schedule_id', $data) ? $data['parent_schedule_id'] : null;
      // return $diferencia;
      if($deleteAll)
      {
        $config = PickingCount::where('product_id', $product_id)->update([
          'status' => $status
        ]);
        // $valor = $realcount-$count2;


      $delete = Stock::where('product_id',$product_id)->where('zone_position_id',$zone_position_id)->first();

        if ($diferencia <= $delete->quanty) {
          // echo "entonces que pirobito";
          $delete->decrement('quanty',$diferencia);
        }else {
          // echo "entonces que pirobito otra vez";
            $delete = Stock::where('product_id',$product_id)->where('zone_position_id',$zone_position_id)->delete();
        }

        $delete2 = Stock::where('product_id',$product_id)->where('zone_position_id',$zone_position_id)->first();

        if ($delete2) {

          if($delete2->quanty===0 || $diferencia <=0)
          {
            $delete = Stock::where('product_id',$product_id)->where('zone_position_id',$zone_position_id)->delete();
          }

        }

        $config = Schedule::where('id', $schedule_id)->update(['status' => 'closed']);

        $boxesClosed = PickingCount::where('schedule_id', $schedule_parent)->get();

        if (!$boxesClosed->isEmpty()) {
          $cerrar = true;
          foreach ($boxesClosed as  $value) {

            if ($value->status != 'closed') {
              $cerrar = false;
            }

          }
        }
        if ($cerrar) {

          $config = Schedule::where('id', $schedule_parent)->update([
            'status' => 'closed']);

          $consult = Schedule::where('id', $schedule_parent)->first();

          $config = Schedule::where('id', $consult->parent_schedule_id)->update([
            'status' => 'closed']);

        }

        $boxes = ScheduleCountBoxes::where('schedule_id', $schedule_parent)->get();

        if(!$boxes->isEmpty())
        {

          foreach ($boxes as  $value) {

            if($value->count === null)
            {
              $config2 = EanCode14Detail::where('ean_code14_id', $value->code14_id)->first();
              $value->count = $config2->quanty;
            }

            $config = EanCode14::where('id', $value->code14_id)->update([
              'quanty' => $value->count
            ]);

            $config2 = EanCode14Detail::where('ean_code14_id', $value->code14_id)->update([
              'quanty' => $value->count
            ]);

            $config3 = StockTransition::where('code14_id', $value->code14_id)->update([
              'quanty' => $value->count
            ]);

          }


        }





      }
      else {

        // $config = Schedule::where('id', $schedule_id)->update([
        //   'status' => 'closed'
        // ]);


        $zona= ZonePosition::with('zone')->where('id',$zone_position_id)->first();
        $warehouseId=$zona->zone->warehouse_id;

        $settingsObj = new Settings();
        $chargeUserName = $settingsObj->get('stock_group');
        $user = User::whereHas('person.group', function ($q) use ($chargeUserName)
        {
          $q->where('name', $chargeUserName);
        })->whereHas('person', function ($q) use ($warehouseId)
        {
          $q->where('warehouse_id', $warehouseId);
        })->first();

        if(empty($user)) {
          return $this->response->error('user_not_found', 404);
        }


        $taskSchedules = [
        'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0].' '.explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
        'name' => 'Contar cajas/bultos zona de picking :',
        'schedule_type' => ScheduleType::Restock,
        'schedule_action' => ScheduleAction::Count,
        'status' => ScheduleStatus::Process,
        'parent_schedule_id'=>$schedule_parent,
        'user_id' => $user->id
        ];

        $schedule = Schedule::create($taskSchedules);


      }

    }
    public function getBoxesBySchedule(Request $request)
    {
      $data = $request->all();
      $schedule_id = $data['schedule_id'];

      $config = ScheduleCountBoxes::with('ean14','product.picking_count.reason_codes')

      ->where('schedule_id', $schedule_id)
      ->where('count', null)
      ->has('product.picking_count.reason_codes')
      ->get();

      return $config->toArray();


    }
    public function nullCount(Request $request)
    {
      $data = $request->all();
      $product_id = array_key_exists('product_id', $data) ? $data['product_id'] : null;
      $schedule_id = array_key_exists('schedule_id', $data) ? $data['schedule_id'] : null;
      $zone_position_id = array_key_exists('zone_position_id', $data) ? $data['zone_position_id'] : null;


      $delete = PickingCount::where('product_id',$product_id)->delete();

      $config = Stock::where('product_id',$product_id)->where('zone_position_id',$zone_position_id)->update([
        'status' => 0
      ]);

    }
    public function pickingUpdateConfig(Request $request)
    {
      $data = $request->all();
      $product_id = array_key_exists('product_id', $data) ? $data['product_id'] : null;
      $min_stock = array_key_exists('min_stock', $data) ? $data['min_stock'] : null;
      $stock_secure = array_key_exists('stock_secure', $data) ? $data['stock_secure'] : null;
      $category_id = array_key_exists('category_id', $data) ? $data['category_id'] : null;
      $usuario = array_key_exists('usuario', $data) ? $data['usuario'] : null;
      $warehouse_id = array_key_exists('warehouse_id', $data) ? $data['warehouse_id'] : null;
      $dad_product_id = array_key_exists('dad_product_id', $data) ? $data['dad_product_id'] : null;

      if(isset($product_id))
      {
        $settingsObj = new Settings();
        $PickingName = $settingsObj->get('picking_type');

        $findpositionPicking = ZonePosition::with('zone.zone_type','stocks','picking_config_product')
        ->whereHas('zone.zone_type', function ($query) use($PickingName) {
          $query->where('name',$PickingName);
        })
        ->whereHas('zone', function ($querys) use($warehouse_id) {
          $querys->where('warehouse_id',$warehouse_id);
        })
        ->has('stocks','<',1)->has('picking_config_product','<',1)->first();
        if($findpositionPicking)
        {
          $objeto = [
              'product_id'=>$product_id,
              'min_stock'=>$min_stock,
              'stock_secure'=>$stock_secure,
              // 'schedule_id'=>$schedule->id,
              'dad_product_id'=>$dad_product_id,
              'category_id'=>$category_id,
              'warehouse_id'=>$warehouse_id,
              'zone_position_id'=>$findpositionPicking->id

            ];

            $config = StockPickingConfig::create($objeto);

            $objeto['stock_picking_config_id'] = $config->id;

            $config_product = StockPickingConfigProduct::create($objeto);
        }else {
          return $this->response->error('picking_configuration_zone_null', 404);
        }


      }else if(isset($dad_product_id) && !isset($product_id)) {

        $settingsObj = new Settings();
        $PickingName = $settingsObj->get('picking_type');

        $findpositionPicking = ZonePosition::with('zone.zone_type','stocks','picking_config_product')
        ->whereHas('zone.zone_type', function ($query) use($PickingName) {
          $query->where('name',$PickingName);
        })
        ->whereHas('zone', function ($querys) use($warehouse_id) {
          $querys->where('warehouse_id',$warehouse_id);
        })
        ->has('stocks','<',1)->has('picking_config_product','<',1)->first();

        if($findpositionPicking)
        {



          $objetoC = [
              'product_id'=>$dad_product_id,
              'min_stock'=>null,
              'stock_secure'=>null,
              'dad_product_id'=>$dad_product_id,
              'category_id'=>$category_id,
              'warehouse_id'=>$warehouse_id,
              'zone_position_id'=>$findpositionPicking->id

            ];

          $config = StockPickingConfig::create($objetoC);

          $productCategory = Product::with('stock.zone_position.zone.zone_type','product_sub_type.product_type.product_category','stock')
          ->where('id',$dad_product_id)
          // ->has('stock')
          ->first();

          $settingsObj = new Settings();
          $StanteriaName = $settingsObj->get('stand_type');

          $son_references = Product::with('stock.zone_position.zone.zone_type','product_sub_type.product_type.product_category','stock')
          ->where('reference', 'like', $productCategory->reference.'%')
          ->whereHas('stock.zone_position.zone.zone_type', function ($q) use ($StanteriaName)
          {
            $q->where('name', $StanteriaName);
          })
          ->has('stock')
          ->where('id','!=',$dad_product_id)
          ->groupBy('reference')
          ->groupBy('colour')
          ->groupBy('size')
          ->orderBy('colour', 'ASC')
          ->orderBy('size', 'ASC')
          ->get();

          // return $son_references;

          $unidades_cate = StockPickingConfig::with('category')->where('category_id',$category_id)->first();
          $category_unit = $unidades_cate->category->units;


          $objeto=[];
          $posicion = $findpositionPicking->id;
          $arreglo_posiciones = [];
          // $posicion_aux = $findpositionPicking->id;
          $aux = $category_unit;
          $color = null;
          foreach ($son_references as $value) {


            $sum = $value->min_stock + $value->stock_secure;

            // $consult = Product::where('id',$value->id)->first();
            // $color = $consult->colour;

            if(is_null($color)||$sum <= $category_unit && $value->colour===$color )
            {
              $category_unit -= $sum ;
              $color = $value->colour;

            }
            else {
              //$arreglo_posiciones []=$posicion;
              array_push($arreglo_posiciones, $posicion);
            //  [1,2,4,56,2,3,43,4,3]
              $findpositionPickingOuter = ZonePosition::with('zone.zone_type','stocks','picking_config_product')
              ->whereHas('zone.zone_type', function ($query) use($PickingName) {
                $query->where('name',$PickingName);
              })
              ->whereHas('zone', function ($querys) use($warehouse_id) {
                $querys->where('warehouse_id',$warehouse_id);
              })
              ->has('stocks','<',1)->has('picking_config_product','<',1)->whereNotIn('id',$arreglo_posiciones)->first();



              if($findpositionPickingOuter)
              {
                $posicion = $findpositionPickingOuter->id;
                if($category_unit<$aux)
                {
                  $category_unit=$aux;
                  $category_unit -= $sum ;
                  $color = $value->colour;
                  // $posicion=$findpositionPickingOuter->id;
                }

              }else {
                return $this->response->error('picking_configuration_zone_null', 404);
              }



            }

            $objeto []= [
                'product_id'=>$value->id,
                'min_stock'=>$value->min_stock,
                'stock_secure'=>$value->stock_secure,
                'zone_position_id'=>$posicion,
                'stock_picking_config_id'=>$config->id

              ];



          }



          // $objeto['stock_picking_config_id'] = $config->id;

          // $config_product = StockPickingConfigProduct::create($objeto);
          $config->stock_picking_config_product()->createMany($objeto);


        }else {
          return $this->response->error('picking_configuration_zone_null', 404);
        }



      }




    }
    public function updateConfiguration(Request $request)
    {
      $data = $request->all();
      $min_stock = $data['min_stock'];
      $id = $data['id'];
      $idConfig = $data['idConfig'];
      $stock_secure = $data['stock_secure'];

      $config = Product::where('id', $id)->update([
        'min_stock' => $min_stock,
        'stock_secure' => $stock_secure
      ]);

      $config = StockPickingConfigProduct::where('zone_position_id', $idConfig)->where('product_id', $id)->update([
        'min_stock' => $min_stock,
        'stock_secure' => $stock_secure
      ]);

      // return $config;
    }
    public function productConfig(Request $request)
    {
      $data = $request->all();
      $product_id = array_key_exists('product_id', $data) ? $data['product_id'] : null;


      $validar = StockPickingConfig::where('product_id',$product_id)->where('status','process')->get();

      if (!$validar->isEmpty()) {
          return $this->response->error('product_picking_configuration', 404);
      }

    }
    public function deleteConfig(Request $request)
    {


    $data = $request->all();
    $id = $data['id'];
    $config = StockPickingConfig::whereIn('id', $id)->delete();




      return $config;


    }
    public function generate_task(Request $request)
    {
      $data = $request->all();
      $usuario = array_key_exists('usuario', $data) ? $data['usuario'] : null;

      $settingsObj = new Settings();
      // $PickingName = $settingsObj->get('picking_type');
      $cediUserName = $settingsObj->get('cedi_charge');
      $user = User::whereHas('person.group', function ($q) use ($cediUserName)
      {
        $q->where('name', $cediUserName);
      })->first();

      if(empty($user)) {
        \Log::error('user_not_found ');
        return $this->response->error('user_not_founda', 404);
      }


      $validar= StockPickingConfig::where('schedule_id', null)->first();
      if($validar)
      {
        $taskSchedules = [
          'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0].' '.explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
          'name' => 'Reabastecer zona de picking:',
          'schedule_type' => ScheduleType::Restock,
          'schedule_action' => ScheduleAction::Store,
          'status' => ScheduleStatus::Process,
          'user_id' => $usuario
        ];

        $schedule = Schedule::create($taskSchedules);

        $update= StockPickingConfig::where('schedule_id', null)
        ->update(['schedule_id' => $schedule->id]);

        $taskSchedulesa = [
          'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0].' '.explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
          'name' => 'Requerimiento de materiales:',
          // 'schedule_type' => ScheduleType::Restock,
          // 'schedule_action' => ScheduleAction::Store,
          'parent_schedule_id' => $schedule->id,
          'status' => ScheduleStatus::Process,
          'user_id' => $user->id
        ];

        $schedulea = Schedule::create($taskSchedulesa);

      }else {
          return $this->response->error('not_task_generate', 404);
      }


    }
    public function getProductByIdSchedule(Request $request)
    {
      $data = $request->all();
      $schedule_id = $data['task'];


      $config = StockPickingConfig::with('stock_picking_config_product.product.stock.zone_position.zone.zone_type','stock_picking_config_product.product.stock.ean14.pallet.ean128','stock_transition','stock_picking_config_product.zone_position','stock_picking_config_product.product.stock.ean13')->where('schedule_id', $schedule_id)->get();

      return $config->toArray();
    }
    public function updateStatus(Request $request)
    {
      $data = $request->all();
      $schedule_id = $data['schedule_id'];


      $config = StockPickingConfig::where('schedule_id', $schedule_id)->update([
        'status' => 'closed'

      ]);

    }

    public function reasonCodesave(Request $request)
    {
      $data = $request->all();
      $idSchedule = $data['schedule_id'];
      $product_id = $data['product_id'];
      $reason_code_id = $data['reason_code_id'];

      $reason= PickingCount::where('schedule_id', $idSchedule)->where('product_id', $product_id)
      ->update(['reason_codes_id' => $reason_code_id]);

      return $reason;
    }

    public function getReferenceByConfiguration($id)
    {


      $config = Stock::with('product.stock_picking_config_product')->where('zone_position_id', $id)->get();

      return $config->toArray();


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
