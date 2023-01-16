<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\ScheduleStock;
use App\Enums\ScheduleStatus;
use App\Enums\ScheduleType;
use App\Transformers\ScheduleTransformer;
use App\Enums\ScheduleAction;

class ScheduleStockController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $schedules = Schedule::with('schedule_stock')
          ->where('schedule_type', ScheduleType::Stock)
          ->get();

      return $schedules->toArray();
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
      // return $data;
      if($data['schedule_type'] === ScheduleType::Stock) {
        $data['status'] = ScheduleStatus::Process;
        $schedule = Schedule::create($data);
       

        //Save the task
        $taskSchedule = [
          'start_date' => $data['start_date'],
          'end_date' => $data['end_date'],
          'name' => $data['name'],
          'schedule_type' => ScheduleType::Task,
          'schedule_action' => ScheduleAction::Inventary,
          'status' => ScheduleStatus::Process,
          'notified' => false,
          'user_id' => $data['schedule_stock']['user_id'],
          'company_id'=>$data['company_id'],
          'parent_schedule_id'=>$schedule->id
        ];
        Schedule::create($taskSchedule);
        $scheduleReceipt = new ScheduleStock($data['schedule_stock']);
        $schedule['id'] = $schedule->id;
        // return $schedule.','.$schedule->id;
        $schedule->schedule_receipt()->save($scheduleReceipt);
        // return $schedule->id;

      }

      return $this->response->item($schedule, new ScheduleTransformer)->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $schedule = Schedule::with('schedule_stock')->findOrFail($id);

      return $schedule->toArray();
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
      $schedule = Schedule::with('schedule_stock')->findOrFail($id);

      $schedule->name = array_key_exists('name', $data) ? $data['name'] : $schedule->name;
      $schedule->start_date = array_key_exists('start_date', $data) ? $data['start_date'] : $schedule->start_date;
      $schedule->end_date = array_key_exists('end_date', $data) ? $data['end_date'] : $schedule->end_date;


      $schedule->save();

      //Update the schedule_stock
      if(array_key_exists('schedule_receipt', $data)) {
        $scheduleStock = $schedule->schedule_stock;
        $receipt = $data['schedule_stock'];

        $scheduleStock->warehouse_id = $receipt['warehouse_id'];
        $scheduleStock->client_id = $receipt['client_id'];
        $scheduleStock->product_type_id = $receipt['product_type_id'];
        $scheduleStock->reference = $receipt['reference'];

        $schedule->schedule_stock()->save($scheduleStock);
      }

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
      Schedule::findOrfail($id)->delete();

      return $this->response->noContent();
    }
}
