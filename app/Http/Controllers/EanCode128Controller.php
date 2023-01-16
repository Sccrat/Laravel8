<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\EanCode128;
use App\Common\Codes;
use App\Models\Pallet;
use DB;

class EanCode128Controller extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $receiptZone = $request->input('receipt');
//'schedule_document.schedule.schedule_receipt'
      $pallet = Pallet::with('ean128.schedule_document.schedule.schedule_receipt.warehouse', 'code14');


      if(isset($receiptZone)) {
        $pallet = $pallet->whereNotIn('code128_id', function($q) {
          $q->select(DB::Raw('ifnull(`code128_id`,0)'))->from('wms_stock');
        });
      }

      $pallet = $pallet->get();

      return $pallet->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function store(Request $request)
     {

     }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function show($id)
     {
       $code128 = Codes::GetCode128($id);
       return $code128;
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

        $ean128 = EanCode128::findOrFail($id);
        $ean128->weight = array_key_exists('weight', $data) ? $data['weight'] : $ean128->weight;
        $ean128->height = array_key_exists('height', $data) ? $data['height'] : $ean128->height;        
        $ean128->save();

        return $ean128->toArray();
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
}
