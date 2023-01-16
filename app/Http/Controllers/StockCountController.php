<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\StockCount;
use App\Models\Stock;
use DB;
use App\Common\Settings;
use App\Models\User;
// use App\Common\Settings;
use App\Models\StockMovement;
use App\Enums\ScheduleType;
use App\Enums\ScheduleAction;
use App\Enums\ScheduleStatus;
use App\Models\Schedule;
use DateTimeZone;
use Carbon\Carbon;

class StockCountController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $schedule_id = $request->input('schedule_id');
      $count = $request->input('count');
      if(isset($count)) {
        //$price = DB::table('orders')->max('price');
        $count = DB::table('wms_stock_counts')->where('schedule_id', $schedule_id)->max('count');
        return $count;
      }
      $counts = StockCount::with('schedule.schedule_stock');

      if(isset($schedule_id)) {
        $counts = $counts->where('schedule_id', $schedule_id);
      }

      $counts = $counts->get();
      return $counts->toArray();
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

      $sctockCount = [];
      $stocks = $data['stocks'];
      $scheduleId = $data['schedule_id'];
      $count = $data['count'];
      $companyId = $data['company_id'];

      // return $stocks;

      
      //Prepare the data
      foreach ($stocks as $value) {
        $sctockCount[] = [
          'product_id' => $value['product_id'],
          'code_ean14' => $value['code_ean14'],
          'zone_position_id' => $value['zone_position_id'],
          'code128_id' => $value['code128_id'],
          'quanty' => $value['quanty_14'],
          'schedule_id' => $scheduleId,
          'found' => $value['totalFound'],
          'count' => $count,
          'stock_id' => $value['stock_id'],
          // 'warehouse_id' => $value['warehouse_id']
        ];
      }
      
      if(!empty($sctockCount[0]))
      {
        StockCount::insert($sctockCount);
      }

      // return $stocks[0]['warehouse_id'];
      $settingsObj = new Settings($companyId);
      $chargeUserName = $settingsObj->get('inventary_analist');
      
      $user = User::whereHas('person.charge', function ($q) use ($chargeUserName)
      {
        $q->where('name', $chargeUserName);
      })->first();
      
      if(empty($user)) {
        return $this->response->error('user_not_found', 404);
      }
      // return $scheduleId;
      Schedule::where('parent_schedule_id', $scheduleId)->update(['status' => 'closed']);
      
              $taskSchedulesW = [
              'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0].' '.explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
              'name' => 'Validar Inventario total:',
              'schedule_type' => ScheduleType::Stock,
              'schedule_action' => ScheduleAction::Validate,
              'status' => ScheduleStatus::Process,
              'user_id' => $user->id,
              'parent_schedule_id'=> $scheduleId,
              'company_id'=>$companyId
              ];
              $scheduleW = Schedule::create($taskSchedulesW);


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

    public function getStockCounts(Request $request)
    {
      $warehouse = $request->input('warehouse_id');
      $schedule = $request->input('schedule_id');
      $productType = $request->input('product_type_id');
      $reference = $request->input('reference');
      $client = $request->input('client_id');
      $companyId = $request->input('company_id');

      //Get the settings stock_counts
      $settingsObj = new Settings($companyId);
      $lol = $settingsObj->get('stock_counts');

      $stock = DB::table('wms_stock as s')
                ->join('wms_zone_positions as zp', 's.zone_position_id', '=', 'zp.id')
                ->join('wms_zones as z', 'zp.zone_id', '=', 'z.id')
                ->join('wms_warehouses as w', 'z.warehouse_id', '=', 'w.id')
                ->join('wms_products as p', 's.product_id', '=', 'p.id')
                ->leftJoin('wms_product_sub_types as ps', 'p.product_sub_type_id', '=', 'ps.id')
                ->join('wms_clients as c', 'p.client_id', '=', 'c.id')
                ->join('wms_ean_codes14 as c14', 's.code14_id', '=', 'c14.id')
                ->leftJoin('wms_ean_codes128 as c128', 's.code128_id', '=', 'c128.id')
                ->select('s.id as stock_id',
                  'p.description as product',
                  'p.id as product_id',
                  'p.ean',
                  'c.name as client',
                  'c14.code14 as code14',
                  'c128.code128 as code128',
                  'zp.description as position',
                  'zp.code as position_code',
                  'p.reference',
                  'z.warehouse_id',
                  'w.name as warehouse',
                  's.quanty');

      if(isset($client)) {
        $stock = $stock->where('p.client_id', '=', $client);
      }

      if(isset($warehouse)) {
        $stock = $stock->where('z.warehouse_id', '=', $warehouse);
      }

      if(isset($reference)) {
        $stock = $stock->where('p.reference', $reference)->orWhere('p.code', $reference);
      }

      if(isset($productType)) {
        $stock = $stock->where('ps.product_type_id', $productType);
      }

      for ($i=1; $i <= $lol; $i++) {
        $sql = '(SELECT sum(found) FROM wms.wms_stock_counts where count = '. $i .' AND schedule_id = ' . $schedule . ' AND stock_id = s.id) as count' . $i;
        $stock = $stock->selectRaw($sql);
      }

      $stock = $stock->get();

      return $stock;
    }

    public function adjustStock(Request $request)
    {
      $data = $request->all();
      $stockId = $data['stock_id'];
      $adjust = $data['adjust'];
      //Get the value from the stock with the stock id
      $query = DB::table('wms_stock')
          ->where('id', $stockId);

      //Validate if it's an increment or a decrement
      $action = 'income';
      if($adjust > 0) {
        $query->increment('quanty', $adjust);
      } else {
        $adjust *= -1;
        $query->decrement('quanty', $adjust);
        $action = 'output';
      }

      //Log the movement
      $log = [
        'product_id' => $data['product_id'],
        'product_reference' => $data['reference'],
        'product_ean' => $data['ean'],
        'product_quanty' => $adjust,
        'zone_position_code' => $data['position_code'],
        'code128' => $data['code128'],
        'code14' => $data['code14'],
        'username' => $data['username'],
        'warehouse_id' => $data['warehouse_id'],
        'action' => $action,
        'concept' => 'adjustment'
      ];

      StockMovement::create($log);

      // $table->increments('id');
      // $table->integer('product_id')->unsigned()->nullable();
      // $table->string('product_reference',200)->nullable();
      // $table->string('product_ean',500)->nullable();
      // $table->integer('product_quanty')->unsigned()->nullable();
      // $table->string('zone_position_code',100);
      // $table->string('code128',500)->nullable();
      // $table->string('code14',500)->nullable();
      // $table->string('username',50);
      // $table->integer('warehouse_id')->unsigned();
      // $table->enum('action', ['income', 'output']);
      // $table->enum('concept', ['storage', 'relocate', 'transform', 'adjustment', 'dispatch', 'pickin']);
      // $table->timestamps();

      return $this->response->noContent();
    }
}
