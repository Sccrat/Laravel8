<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Enums\ScheduleType;
use App\Common\SchedulesFunctions;
use App\Common\Settings;
use App\Models\ScheduleDocument;
use Illuminate\Support\Facades\DB;

class TaskController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = $request->all();
        $tree = array_key_exists('tree', $data) ? $data['tree'] : null;


        $userId = $request->input('user_id');

        $tasks = Schedule::with('user.person.warehouse', 'parent_schedule.schedule_receipt.warehouse', 'user.person.warehouse', 'parent_schedule.schedule_dispatch')
            ->where('company_id', $data['company_id'])
            ->whereOr('schedule_type', ScheduleType::Task)
            ->whereOr('schedule_type', ScheduleType::Transform)
            ->where('status', 'process');

        if (isset($userId)) {
            $tasks = $tasks->where('user_id', $userId);
        }

        // if (!empty($tree)) {
        //     $tasks->where('parent_schedule_id', null);
        //     $tasks->with('child_schedules.user.person.warehouse');
        // }

        $tasks = $tasks->get();

        return $tasks->toArray();
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
        $companyId = $request->input('company_id');

        if (array_key_exists('send_to_charge', $data) && array_key_exists('warehouse_id', $data)) {
            $warehouseId = $data['warehouse_id'];
            $sendToCharge = $data['send_to_charge'];

            switch ($sendToCharge) {
                case 'cedi_charge':
                    $userWithCharge = SchedulesFunctions::getCediBossByWarehouse($warehouseId, $this, $companyId);
                    break;
                case 'leader_charge':
                    $userWithCharge = SchedulesFunctions::getLeaderByWarehouse($warehouseId, $this, $companyId);
                    break;
            }

            if (!empty($userWithCharge)) {
                $data['user_id'] =  $userWithCharge->user->id;
            }
        }


        Schedule::create($data);

        return  $this->response->created();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $task = Schedule::findOrFail($id);
        return $task->toArray();
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
        $task = Schedule::findOrFail($id);

        $task->name = array_key_exists('name', $data) ? $data['name'] : $task->name;
        $task->user_id = array_key_exists('user_id', $data) ? $data['user_id'] : $task->user_id;
        $task->start_date = array_key_exists('start_date', $data) ? $data['start_date'] : $task->start_date;
        $task->end_date = array_key_exists('end_date', $data) ? $data['end_date'] : $task->end_date;
        $task->status = array_key_exists('status', $data) ? $data['status'] : $task->status;

        $task->save();

        return $this->response->noContent();
    }

    public function getPickingTask(Request $request, $taskId, $parent)
    {

        $settings = new Settings(22);
        $seconds_zone = $settings->Get('seconds');
        $good_zone = $settings->Get('good');
        // return $good;

        // Get the enlist products
        $enlist = DB::table('wms_enlist_products as ep')
            ->leftjoin('wms_products as pr', 'pr.id', '=', 'ep.product_id')
            ->leftJoin('wms_documents as doc', 'doc.id', '=', 'ep.document_id')
            ->leftJoin('wms_clients as cl', 'cl.id', '=', 'doc.client')
            ->select(
                'ep.product_id',
                'ep.unit',
                'ep.destiny_ware',
                'ep.id as enlist_id',
                'ep.document_id',
                'doc.number',
                'ep.picked_quanty',
                'ep.quanty',
                'ep.good',
                'ep.seconds',
                'ep.code_ean14',
                'cl.name as client',
                'pr.reference',
                'pr.ean',
                'doc.warehouse_origin'
            )
            ->where('ep.document_id', $parent)
            ->whereRaw('ep.picked_quanty < ep.quanty')
            ->get();

        //Separate the products
        // $products = array_pluck($enlist, 'product_id');


        //Separate lots
        $good = [];
        $seconds = [];
        foreach ($enlist as $value) {
            if ($value->good > 0) {
                $good[] = $value->product_id;
            }

            if ($value->seconds > 0) {
                $seconds[] = $value->product_id;
            }
        }
        // return  $good;
        // //Separate the products
        // $products = array_pluck($enlist, 'product_id');

        //Get the user warehouse
        $userId = $request->input('session_user_id');

        $warehouseId = DB::table('admin_users as u')
            ->join('wms_personal as p', 'u.personal_id', '=', 'p.id')
            ->where('u.id', $userId)
            ->pluck('p.warehouse_id');


        $stockC = [];
        // Get all the stock where the products are located
        if (count($good) > 0) {
            if ($enlist[0]->warehouse_origin == 'ECOMM' || $enlist[0]->warehouse_origin == '001' || $enlist[0]->warehouse_origin == 'CONPT' || $enlist[0]->warehouse_origin == 'DEVOLUCIONES FISICAS' || $enlist[0]->warehouse_origin == 'DEV.FISICAS CALIDAD' || $enlist[0]->warehouse_origin == 'DFWEB' || $enlist[0]->warehouse_origin == 'CLIENTE ESPECIAL SOU' || $enlist[0]->warehouse_origin == 'CON' || $enlist[0]->warehouse_origin == '269') {
                $enlist[0]->warehouse_origin = 'ArtMode';
            }
            $stock = DB::table('wms_stock as st')
                ->leftjoin('wms_products as pr', 'pr.id', '=', 'st.product_id')
                // ->join('wms_ean_codes14 as c14', 'c14.id', '=', 'st.code14_id')
                ->leftjoin('wms_ean_codes128 as c128', 'c128.id', '=', 'st.code128_id')
                ->leftjoin('wms_zone_positions as zp', 'zp.id', '=', 'st.zone_position_id')
                ->leftjoin('wms_zones as zo', 'zo.id', '=', 'zp.zone_id')
                ->leftjoin('wms_document_details as dd', 'dd.id', '=', 'st.document_detail_id')
                ->leftjoin('wms_zone_concepts as zc', 'zc.id', '=', 'zp.concept_id')
                ->select(
                    'pr.ean',
                    'pr.reference',
                    'pr.description',
                    'st.code14_id',
                    'st.code128_id',
                    'st.quanty',
                    'st.id as stock_id',
                    'st.code_ean14',
                    'c128.code128',
                    'zp.code as code_position',
                    'zp.level',
                    'zp.module',
                    'zp.row',
                    'zp.position',
                    'st.zone_position_id',
                    'st.product_id',
                    'st.quanty_14',
                    'dd.batch',
                    'dd.expiration_date',
                    'zc.name'
                )
                ->whereIn('st.product_id', $good)
                // ->whereIn('dd.batch', $lots)
                // ->whereIn('st.product_id', $products)
                ->where('zo.warehouse_id', $warehouseId)
                ->where('zc.name', $enlist[0]->warehouse_origin)
                ->orderBy('zp.row')
                ->orderBy('zp.module')
                ->orderBy('zp.level')
                ->orderBy('pr.id')
                ->get();
            // return $stock;

            $stockC[] = $stock;
        } else {
            $stockC[] = [];
        }

        if (count($seconds) > 0) {
            $stock = DB::table('wms_stock as st')
                ->leftjoin('wms_products as pr', 'pr.id', '=', 'st.product_id')
                // ->join('wms_ean_codes14 as c14', 'c14.id', '=', 'st.code14_id')
                ->leftjoin('wms_ean_codes128 as c128', 'c128.id', '=', 'st.code128_id')
                ->leftjoin('wms_zone_positions as zp', 'zp.id', '=', 'st.zone_position_id')
                ->leftjoin('wms_zones as zo', 'zo.id', '=', 'zp.zone_id')
                ->leftjoin('wms_document_details as dd', 'dd.id', '=', 'st.document_detail_id')
                ->leftjoin('wms_zone_concepts as zc', 'zc.id', '=', 'zp.concept_id')
                ->select(
                    'pr.reference',
                    'pr.description',
                    'st.code14_id',
                    'st.code128_id',
                    'st.quanty',
                    'st.id as stock_id',
                    'st.code_ean14',
                    'c128.code128',
                    'zp.code as code_position',
                    'zp.level',
                    'zp.module',
                    'zp.row',
                    'zp.position',
                    'st.zone_position_id',
                    'st.product_id',
                    'st.quanty_14',
                    'dd.batch',
                    'dd.expiration_date',
                    'zc.name'
                )
                ->whereIn('st.product_id', $seconds)
                // ->whereIn('dd.batch', $lots)
                // ->whereIn('st.product_id', $products)
                ->where('zo.warehouse_id', $warehouseId)
                ->where('zc.name', $seconds_zone)
                // ->where('zo.is_secondary', 0)
                ->orderBy('st.zone_position_id')
                ->get();
            $stockC[] = $stock;
        } else {
            $stockC[] = [];
        }

        $array = [];
        if (count($stockC[0]) > 0) {
            foreach ($stockC[0] as $value) {
                // return $value;
                $array[] = $value;
            }
        }

        // return $stockC;
        if (count($stockC[1]) > 0) {
            foreach ($stockC[1] as  $value) {
                $array[] = $value;
            }
        }
        // return $array;
        // $stock = \DB::table('wms_stock as st')
        // ->leftjoin('wms_products as pr', 'pr.id', '=', 'st.product_id')
        // // ->join('wms_ean_codes14 as c14', 'c14.id', '=', 'st.code14_id')
        // ->leftjoin('wms_ean_codes128 as c128', 'c128.id', '=', 'st.code128_id')
        // ->leftjoin('wms_zone_positions as zp', 'zp.id', '=', 'st.zone_position_id')
        // ->leftjoin('wms_zones as zo', 'zo.id', '=', 'zp.zone_id')
        // ->leftjoin('wms_document_details as dd', 'dd.id', '=', 'st.document_detail_id')
        // ->select(
        // 'pr.reference',
        // 'pr.description',
        // 'st.code14_id',
        // 'st.code128_id',
        // 'st.quanty',
        // 'st.id as stock_id',
        // 'st.code_ean14',
        // 'c128.code128',
        // 'zp.code as code_position',
        // 'zp.level',
        // 'zp.module',
        // 'zp.row',
        // 'zp.position',
        // 'st.zone_position_id',
        // 'st.product_id',
        // 'st.quanty_14',
        // 'dd.batch',
        // 'dd.expiration_date')
        // ->whereIn('st.product_id', $products)
        // // ->whereIn('dd.batch', $lots)
        // // ->whereIn('st.product_id', $products)
        // ->where('zo.warehouse_id', $warehouseId)
        // // ->where('zo.is_secondary', 0)
        // ->orderBy('st.zone_position_id')
        // ->get();
        // return $stock;


        //Get the total
        $total = DB::table('wms_enlist_products as ep')
            ->where('document_id', $parent)
            ->sum('quanty');

        $mercado = DB::table('wms_enlist_products as ep')
            ->where('document_id', $parent)
            ->sum('picked_quanty');

        $result = [
            'enlist' => $enlist,
            'stock' => $array,
            'total' => $total,
            'mercado' => $mercado
        ];

        return $result;
    }

    public function getPickingHistory($scheduleId)
    {
        $pks  =  DB::table('wms_eancodes14_packing as pck')
            ->join('wms_products as pr', 'pr.id', '=', 'pck.product_id')
            // ->join('wms_ean_codes14 as e14', 'e14.id', '=', 'pck.code14_id')
            ->leftjoin('wms_ean_codes128 as e128', 'e128.id', '=', 'pck.code128_id')
            ->where('pck.document_id', $scheduleId)
            ->select('pck.code_ean14', 'e128.code128', 'pck.stock_id', 'pck.code128_id', 'pck.quanty_14', 'pr.reference', 'pr.description')
            ->orderBy('pck.id', 'desc')
            ->get();

        return $pks;
    }

    public function viewPickingTaskEnlist($taskId)
    {
        // Get the enlist products
        $enlist = DB::table('wms_enlist_products as ep')
            ->join('wms_products as pr', 'pr.id', '=', 'ep.product_id')
            ->select('pr.reference', 'quanty', 'picked_quanty', 'pr.description', 'pr.ean')
            ->where('document_id', $taskId)
            ->orderBy('picked_quanty', 'asc')
            // ->whereRaw('picked_quanty < order_quanty')
            ->get();

        $lol = [];
        foreach ($enlist as $value) {
            if (($value->quanty - $value->picked_quanty) > 0) {
                $lol[] = $value;
            }
        }

        return $lol;
    }

    public function viewPickingTaskEnlistN($taskId)
    {
        // Get the enlist products
        $enlist = DB::table('wms_enlist_products as ep')
            ->join('wms_products as pr', 'pr.id', '=', 'ep.product_id')
            ->select('pr.reference', 'quanty', 'picked_quanty', 'pr.description', 'pr.ean')
            ->where('document_id', $taskId)
            ->orderBy('picked_quanty', 'asc')
            // ->whereRaw('picked_quanty < order_quanty')
            ->get();

        // $lol = [];
        // foreach ($enlist as $value) {
        //     if (($value->quanty - $value->picked_quanty) > 0) {
        //         $lol []= $value;
        //     }
        // }

        return $enlist;
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

    /**
     * Retorna la sugerencia del picking massivo con el enlist, el stock, lo mercado y el total
     * @author Santiago Muñoz
     */
    public function getPickingMassive(Request $request, $taskId, $waveUserId)
    {
        $userId = $request->input('session_user_id');
        $enlist = DB::table('wms_enlist_products_waves as wepw')
            ->join('wms_waves as ww', 'wepw.wave_id', '=', 'ww.id')
            ->join('wms_enlist_products_waves_users as wepwu', 'wepw.id', '=', 'wepwu.enlistproductwave_id')
            ->join('wms_products as wp', 'wepw.product_id', '=', 'wp.id')
            ->join('wms_documents as wd', function ($join) {
                $join->where(DB::raw("FIND_IN_SET(wd.id, ww.documents)"), "<>", "0");
            })
            ->leftJoin('wms_clients as cl', 'cl.id', '=', 'wd.client')
            ->select(
                'wepw.product_id',
                'wepw.id as enlist_id',
                'wd.number',
                'wepwu.picked_quanty',
                'wepwu.quanty',
                'cl.name as client',
                'wp.reference',
                'wp.ean',
                'wd.warehouse_origin'
            )
            ->where('wepwu.id', $waveUserId)
            ->where('wepwu.user_id', $userId)
            ->whereRaw('wepwu.picked_quanty < wepwu.quanty')
            ->get();

        $products = [];
        foreach ($enlist as $value) {
            $products[] = $value->product_id;
        }

        $warehouseId = DB::table('admin_users as u')
            ->join('wms_personal as p', 'u.personal_id', '=', 'p.id')
            ->where('u.id', $userId)
            ->pluck('p.warehouse_id');


        $stockC = [];
        if (count($products) > 0) {
            if ($enlist[0]->warehouse_origin == 'ECOMM' || $enlist[0]->warehouse_origin == '001' || $enlist[0]->warehouse_origin == 'CONPT' || $enlist[0]->warehouse_origin == 'DEVOLUCIONES FISICAS' || $enlist[0]->warehouse_origin == 'DEV.FISICAS CALIDAD' || $enlist[0]->warehouse_origin == 'DFWEB' || $enlist[0]->warehouse_origin == 'CLIENTE ESPECIAL SOU' || $enlist[0]->warehouse_origin == 'CON' || $enlist[0]->warehouse_origin == '269') {
                $enlist[0]->warehouse_origin = 'ArtMode';
            }
            $stock = DB::table('wms_stock as st')
                ->leftjoin('wms_products as pr', 'pr.id', '=', 'st.product_id')
                ->leftjoin('wms_ean_codes128 as c128', 'c128.id', '=', 'st.code128_id')
                ->leftjoin('wms_zone_positions as zp', 'zp.id', '=', 'st.zone_position_id')
                ->leftjoin('wms_zones as zo', 'zo.id', '=', 'zp.zone_id')
                ->leftjoin('wms_document_details as dd', 'dd.id', '=', 'st.document_detail_id')
                ->leftjoin('wms_zone_concepts as zc', 'zc.id', '=', 'zp.concept_id')
                ->select(
                    'pr.ean',
                    'pr.reference',
                    'pr.description',
                    'st.code14_id',
                    'st.code128_id',
                    'st.quanty',
                    'st.id as stock_id',
                    'st.code_ean14',
                    'c128.code128',
                    'zp.code as code_position',
                    'zp.level',
                    'zp.module',
                    'zp.row',
                    'zp.position',
                    'st.zone_position_id',
                    'st.product_id',
                    'st.quanty_14',
                    'dd.batch',
                    'dd.expiration_date',
                    'zc.name'
                )
                ->whereIn('st.product_id', $products)
                ->where('zo.warehouse_id', $warehouseId)
                ->where('zc.name', $enlist[0]->warehouse_origin)
                ->orderBy('zp.row')
                ->orderBy('zp.module')
                ->orderBy('zp.level')
                ->orderBy('pr.id')
                ->get();

            $stockC[] = $stock;
        } else {
            $stockC[] = [];
        }

        $array = [];
        if (count($stockC[0]) > 0) {
            foreach ($stockC[0] as $value) {
                $array[] = $value;
            }
        }

        $total = DB::table('wms_enlist_products_waves_users as wepv')
            ->where('id', $waveUserId)
            ->sum('quanty');

        $mercado = DB::table('wms_enlist_products_waves_users as wepv')
            ->where('id', $waveUserId)
            ->sum('picked_quanty');

        $result = [
            'enlist' => $enlist,
            'stock' => $array,
            'total' => $total,
            'mercado' => $mercado
        ];

        return $result;
    }

    /**
     * Retorna la data del enlist a mercar actualmente
     * @author Santiago Muñoz
     */
    public function viewPickingTaskEnlistMassive(Request $request, $waveUserId)
    {
        $userId = $request->input('session_user_id');
        $enlist = DB::table('wms_enlist_products_waves as wepw')
            ->join('wms_waves as ww', 'wepw.wave_id', '=', 'ww.id')
            ->join('wms_enlist_products_waves_users as wepwu', 'wepw.id', '=', 'wepwu.enlistproductwave_id')
            ->join('wms_products as wp', 'wepw.product_id', '=', 'wp.id')
            ->select('wp.reference', 'wepwu.quanty', 'wepwu.picked_quanty', 'wp.description', 'wp.ean')
            ->where('wepwu.id', $waveUserId)
            ->where('wepwu.user_id', $userId)
            ->orderBy('picked_quanty', 'asc')
            ->get();

        $lol = [];
        foreach ($enlist as $value) {
            if (($value->quanty - $value->picked_quanty) > 0) {
                $lol[] = $value;
            }
        }

        return $lol;
    }

    /**
     * Retorna la data del enlist a mercar actualmente al detalle
     * @author Santiago Muñoz
     */
    public function viewPickingTaskEnlistDetail(Request $request, $waveUserId)
    {
        $userId = $request->input('session_user_id');
        $enlist = DB::table('wms_enlist_products_waves as wepw')
            ->join('wms_waves as ww', 'wepw.wave_id', '=', 'ww.id')
            ->join('wms_enlist_products_waves_users as wepwu', 'wepw.id', '=', 'wepwu.enlistproductwave_id')
            ->join('wms_products as wp', 'wepw.product_id', '=', 'wp.id')
            ->select('wp.reference', 'wepwu.quanty', 'wepwu.picked_quanty', 'wp.description', 'wp.ean')
            ->where('wepwu.id', $waveUserId)
            ->where('wepwu.user_id', $userId)
            ->orderBy('picked_quanty', 'asc')
            ->get();

        return $enlist;
    }

    public function getPickingTaskAllocationMassive(Request $request, $taskId, $waveId)
    {
        $userId = $request->input('session_user_id');
        $enlist = DB::table('wms_enlist_products_waves as wepw')
            ->join('wms_waves as ww', 'wepw.wave_id', '=', 'ww.id')
            ->join('wms_enlist_products_waves_users as wepwu', 'wepw.id', '=', 'wepwu.enlistproductwave_id')
            ->join('wms_products as wp', 'wepw.product_id', '=', 'wp.id')
            ->join('wms_documents as wd', function ($join) {
                $join->where(DB::raw("FIND_IN_SET(wd.id, ww.documents)"), "<>", "0");
            })
            ->leftJoin('wms_clients as cl', 'cl.id', '=', 'wd.client')
            ->select(
                'wepw.product_id',
                'wepw.id as enlist_id',
                'wd.number',
                'wepwu.picked_quanty',
                'wepwu.quanty',
                'cl.name as client',
                'wp.reference',
                'wp.ean',
                'wd.warehouse_origin'
            )
            ->where('ww.id', $waveId)
            ->where('wepwu.user_id', $userId)
            ->whereRaw('wepwu.picked_quanty < wepwu.quanty')
            ->get();

        $products = [];
        foreach ($enlist as $value) {
            $products[] = $value->product_id;
        }

        $warehouseId = DB::table('admin_users as u')
            ->join('wms_personal as p', 'u.personal_id', '=', 'p.id')
            ->where('u.id', $userId)
            ->pluck('p.warehouse_id');


        $stockC = [];
        if (count($products) > 0) {
            if ($enlist[0]->warehouse_origin == 'ECOMM' || $enlist[0]->warehouse_origin == '001' || $enlist[0]->warehouse_origin == 'CONPT' || $enlist[0]->warehouse_origin == 'DEVOLUCIONES FISICAS' || $enlist[0]->warehouse_origin == 'DEV.FISICAS CALIDAD' || $enlist[0]->warehouse_origin == 'DFWEB' || $enlist[0]->warehouse_origin == 'CLIENTE ESPECIAL SOU' || $enlist[0]->warehouse_origin == 'CON' || $enlist[0]->warehouse_origin == '269') {
                $enlist[0]->warehouse_origin = 'ArtMode';
            }
            $stock = DB::table('wms_stock as st')
                ->leftjoin('wms_products as pr', 'pr.id', '=', 'st.product_id')
                ->leftjoin('wms_ean_codes128 as c128', 'c128.id', '=', 'st.code128_id')
                ->leftjoin('wms_zone_positions as zp', 'zp.id', '=', 'st.zone_position_id')
                ->leftjoin('wms_zones as zo', 'zo.id', '=', 'zp.zone_id')
                ->leftjoin('wms_document_details as dd', 'dd.id', '=', 'st.document_detail_id')
                ->leftjoin('wms_zone_concepts as zc', 'zc.id', '=', 'zp.concept_id')
                ->select(
                    'pr.ean',
                    'pr.reference',
                    'pr.description',
                    'st.code14_id',
                    'st.code128_id',
                    'st.quanty',
                    'st.id as stock_id',
                    'st.code_ean14',
                    'c128.code128',
                    'zp.code as code_position',
                    'zp.level',
                    'zp.module',
                    'zp.row',
                    'zp.position',
                    'st.zone_position_id',
                    'st.product_id',
                    'st.quanty_14',
                    'dd.batch',
                    'dd.expiration_date',
                    'zc.name'
                )
                ->whereIn('st.product_id', $products)
                ->where('zo.warehouse_id', $warehouseId)
                ->where('zc.name', $enlist[0]->warehouse_origin)
                ->orderBy('zp.row')
                ->orderBy('zp.module')
                ->orderBy('zp.level')
                ->orderBy('pr.id')
                ->get();

            $stockC[] = $stock;
        } else {
            $stockC[] = [];
        }

        $array = [];
        if (count($stockC[0]) > 0) {
            foreach ($stockC[0] as $value) {
                $array[] = $value;
            }
        }

        $total = DB::table('wms_enlist_products_waves_users as wepv')
            ->join('wms_enlist_products_waves as wepw', 'wepv.enlistproductwave_id', 'wepw.id')
            ->where('wepw.wave_id', $waveId)
            ->where('wepv.user_id', $userId)
            ->sum('wepv.quanty');

        $mercado = DB::table('wms_enlist_products_waves_users as wepv')
            ->join('wms_enlist_products_waves as wepw', 'wepv.enlistproductwave_id', 'wepw.id')
            ->where('wepw.wave_id', $waveId)
            ->where('wepv.user_id', $userId)
            ->sum('wepv.picked_quanty');

        $result = [
            'enlist' => $enlist,
            'stock' => $array,
            'total' => $total,
            'mercado' => $mercado
        ];

        return $result;
    }

    public function viewPickingTaskEnlistAllocationMassive(Request $request, $waveId)
    {
        $userId = $request->input('session_user_id');
        $enlist = DB::table('wms_enlist_products_waves as wepw')
            ->join('wms_waves as ww', 'wepw.wave_id', '=', 'ww.id')
            ->join('wms_enlist_products_waves_users as wepwu', 'wepw.id', '=', 'wepwu.enlistproductwave_id')
            ->join('wms_products as wp', 'wepw.product_id', '=', 'wp.id')
            ->select('wp.reference', 'wepwu.quanty', 'wepwu.picked_quanty', 'wp.description', 'wp.ean')
            ->where('ww.id', $waveId)
            ->where('wepwu.user_id', $userId)
            ->orderBy('picked_quanty', 'asc')
            ->get();

        $lol = [];
        foreach ($enlist as $value) {
            if (($value->quanty - $value->picked_quanty) > 0) {
                $lol[] = $value;
            }
        }

        return $lol;
    }

    public function viewPickingTaskEnlistAllocationMassiveN(Request $request, $waveId)
    {
        $userId = $request->input('session_user_id');
        $enlist = DB::table('wms_enlist_products_waves as wepw')
            ->join('wms_waves as ww', 'wepw.wave_id', '=', 'ww.id')
            ->join('wms_enlist_products_waves_users as wepwu', 'wepw.id', '=', 'wepwu.enlistproductwave_id')
            ->join('wms_products as wp', 'wepw.product_id', '=', 'wp.id')
            ->select('wp.reference', 'wepwu.quanty', 'wepwu.picked_quanty', 'wp.description', 'wp.ean')
            ->where('ww.id', $waveId)
            ->where('wepwu.user_id', $userId)
            ->orderBy('picked_quanty', 'asc')
            ->get();

        return $enlist;
    }
}
