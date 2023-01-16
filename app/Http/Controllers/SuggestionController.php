<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Common\Features;
use App\Models\Suggestion;
use Log;
// use App\Models\EanCode128;
// use App\Models\Warehouse;
// use App\Models\Zone;
// use App\Models\ZonePosition;
// use App\Models\User;
// use App\Enums\ScheduleType;
// use App\Enums\ScheduleStatus;
// use App\Models\Schedule;
// use DB;

class SuggestionController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $suggestions = Suggestion::with('zone_position');

        $code = $request->input('code');
        $codePosition = $request->input('codePosition');

        if(isset($code)) {
            $suggestions = $suggestions->where('code', $code);
        }

        if(isset($codePosition)) {
            $suggestions = $suggestions->whereHas('zone_position', function ($query) use ($codePosition){
              return $query->where('code', $codePosition);
            });
        }

        $suggestions = $suggestions->where('stored', 0);

        return $suggestions->get()->toArray();
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

        Suggestion::create($data);

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
      // try {
        $positions = Features::suggestPalletPosition($id);
        return $positions;
      // } catch (\Exception $e) {
      //   // An internal error with an optional message as the first parameter.
      //   Log::error('suggestPalletPosition '.$e->getMessage());
      //   return $this->response->errorInternal();
      // }
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
      $suggestion = Suggestion::findOrFail($id);

      // $suggestion->code = array_key_exists('code', $data) ? $data['code'] : $suggestion->code;
      $suggestion->reason_code_id = array_key_exists('reason_code_id', $data) ? $data['reason_code_id'] : $suggestion->reason_code_id;
      $suggestion->active;

      $suggestion->save();

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
        //
    }

    public function suggestPosition(Request $request)
    {
      $code128 = $request->input('code_128');
      Features::suggestPalletPosition($code128);
      // $docs = EanCode128::with('schedule_document.schedule.schedule_receipt')->where('code128', $code128)->where('canceled', false)->first();
      //
      // $receipt = $docs->schedule_document->schedule->schedule_receipt;
      // //Get the weight and the warehouse of the pallet
      // $weight = $docs->weight;
      // $warehouse = $receipt->warehouse_id;
      //
      // //Get the storage zone of the warehouse
      // $zone = ZonePosition::whereHas('zone', function ($q) use ($warehouse) {
      //           $q->where('warehouse_id', $warehouse)
      //             ->whereHas('zone_type', function ($query) {
      //               $query->where('is_storage', true);
      //             });
      //         })->whereNotIn('id', function ($q) {
      //           $q->select(DB::Raw('ifnull(`zone_position_id`,0)'))->from('wms_stock');
      //         })->whereNotIn('id', function ($q) {
      //           $q->select(DB::Raw('ifnull(`zone_position_id`,0)'))->from('wms_suggestions');
      //         })->first();
      //
      // //Insert the suggestion
      // $suggestion = [
      //   'code' => $code128,
      //   'zone_position_id' => $zone->id,
      //   'stored' => false
      // ];
      //
      // Suggestion::create($suggestion);
      //
      // //Insert the task
      // //Get the responsible user
      // $user = User::where('personal_id', $receipt->responsible_id)->first();
      // //$personalId = $receipt->responsible_id;
      // $task = [
      //   'name' => 'Almacenar ' . $code128 . ' en ' . $zone->description,
      //   'schedule_type' => ScheduleType::Store,
      //   'status' => ScheduleStatus::Process,
      //   'user_id' => $user->id
      // ];
      //
      // Schedule::create($task);

     return $this->response->noContent();
    }
}
