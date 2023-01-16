<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use App\Models\DocumentDetailCount;
use App\Models\DocumentDetail;
use App\Models\DocumentDetailMultipleCount;
use App\Models\EanCode14Detail;
use App\Http\Controllers\Controller;
use App\Enums\DocumentDetailStatus;


class DocumentDetailCountController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $idDocument = $request->input('id_document');

        $count = DocumentDetailCount::with('documentDetail');

        if(isset($idDocument)) {
            $count = $count->whereHas('documentDetail', function ($query) use ($idDocument)
            {
                return $query->where('document_id',$idDocument);
            });
        }

        return $count->get()->toArray();
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
        $res = [];
        DB::transaction(function () use ($data,&$res)
        {
            foreach ($data as $key => $detail) {
                if (!is_array($detail)) {
                  continue;
                }


                if (!empty($detail['insertCount'])) {
                    foreach ($detail['insertCount'] as $keyC => $count) {
                        if (empty($count['quanty']) && $count['quanty'] != '0') {
                            $count['quanty'] = NULL;
                        }
                        if ($count['quanty'] == '0') {
                          $count['quanty1'] = $count['quanty'];
                        }

                        if ($detail['product_ean14']) {
                          $count['code_ean14'] = $detail['product_ean14']['code_ean14'];
                        }
                        $createdCount = DocumentDetailCount::create($count);
                        $createdCount->detailMultipleCount()->createMany($count['detail_multiple_count']);

                        $createdCount['cartons'] = $detail['quanty_received'];
                        $createdCount['unit']    = $detail['quanty'];
                        $createdCount['detail_multiple_count']    = $createdCount->detailMultipleCount()->with('product')->get()->toArray();

                        $createdCount['ean14']    = empty($count['ean14'])?NULL:$count['ean14'];

                        if (!empty($count['quarantine'])) {
                            $createdCount['quarantine']    = $count['quarantine'];
                        }
                        $damagedCountArr = [];
                        if (!empty($count['damaged_count'])) {
                            foreach ($count['damaged_count'] as $keyDamaged => $damagedCount) {
                                $damagedCount['count_parent'] = $createdCount['id'];
                                $damagedCount['quanty1'] = $damagedCount['quanty'];
                                $createdDamagedCount = DocumentDetailCount::create($damagedCount);
                                $damagedCountArr[] = $createdDamagedCount;
                            }
                        }
                        $createdCount['damagedCount']    = $damagedCountArr;

                        array_push($data[$key]['detailCartons'], $createdCount);
                    }
                }
                $detailModel = DocumentDetail::findOrFail($detail['id']);
                $detailModel->count_status = $detail['count_status'];
                if ($detailModel->count_status == 1) {
                    $detailModel->status = DocumentDetailStatus::Closed;
                }
                $detailModel->save();
                array_push($res, $data[$key]);
            }
        });

        return $res;
    }

    public function storeMultiple(Request $request)
    {
      $data = $request->all();
      $res = [];
      DB::transaction(function () use ($data,&$res)
      {
          foreach ($data as $key => $detail) {
              if (!is_array($detail)) {
                continue;
              }
              $detailModel = DocumentDetail::findOrFail($detail['id']);
              $detailModel->count_status = $detail['count_status'];
              $detailModel->detailMultipleCount()->createMany($detail['detail_multiple_count_insert']);

              $detailModel->save();
              $data[$key]['detail_multiple_count'] = $detailModel->detailMultipleCount()->with('product')->get()->toArray();
              array_push($res, $data[$key]);
          }
        });

        return $res;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
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
      $data = $request->all();
      // if (empty($data['quanty'] && $data['quanty'] != '0')) {
      //       $data['quanty'] = NULL;
      // }
      if (!isset($data['quanty1'])) {
            $data['quanty1'] = NULL;
      }else{
            // $data['quanty'] = $data['quanty1'];
      }

      if (!isset($data['quanty2'])) {
            $data['quanty2'] = NULL;
      }else{
            // $data['quanty'] = $data['quanty2'];
      }

      if (!isset($data['quanty3'])) {
            $data['quanty3'] = NULL;
      }else{
            // $data['quanty'] = $data['quanty3'];
      }

      if (!empty($data['adjust'])) {
            $data['quanty'] = $data['adjust'];
      }




      $documents = DocumentDetailCount::findOrFail($id);

      $quantyDetail = empty($documents->documentDetail)?'':$documents->documentDetail->quanty;

      if (!empty($quantyDetail)) {
          if ($quantyDetail != $data['quanty']) {
              $data['has_error'] = true;
          }else{
              $data['has_error'] = false;
          }
      }


      // $ean14Quanty = empty($documents->ean14->quanty)?'':$documents->ean14->quanty;

      // if (!empty($ean14Quanty)) {
      //     if ($ean14Quanty != $data['quanty']) {
      //         $data['has_error'] = true;
      //     }else{
      //         $data['has_error'] = false;
      //     }
      // }



      $documents->quanty = array_key_exists('quanty', $data) ? $data['quanty'] : $documents->quanty;
      $documents->quanty1 = array_key_exists('quanty1', $data) ? $data['quanty1'] : $documents->quanty1;
      $documents->quanty2 = array_key_exists('quanty2', $data) ? $data['quanty2'] : $documents->quanty2;
      $documents->quanty3 = array_key_exists('quanty3', $data) ? $data['quanty3'] : $documents->quanty3;
      $documents->has_error = array_key_exists('has_error', $data) ? $data['has_error'] : $documents->has_error;
      $documents->quarantine = array_key_exists('quarantine', $data) ? $data['quarantine'] : $documents->quarantine;
      $documents->save();


      if (!empty($data['adjust'])) {
        if ($documents->ean14) {
          $documents->ean14->quanty = $documents->quanty;
          $documents->ean14->save();

        }
         //change
         EanCode14Detail::where('ean_code14_id', $documents->ean14_id)->update(['quanty' => $documents->quanty]);
      }

      return $documents;
    }

    public function updateMultiple(Request $request, $id)
    {
      $data = $request->all();
      // if (empty($data['quanty'] && $data['quanty'] != '0')) {
      //       $data['quanty'] = NULL;
      // }
      if (!isset($data['quanty1'])) {
            $data['quanty1'] = NULL;
      }else{
            // $data['quanty'] = $data['quanty1'];
      }

      if (!isset($data['quanty2'])) {
            $data['quanty2'] = NULL;
      }else{
            // $data['quanty'] = $data['quanty2'];
      }

      if (!isset($data['quanty3'])) {
            $data['quanty3'] = NULL;
      }else{
            // $data['quanty'] = $data['quanty3'];
      }

      if (!empty($data['adjust'])) {
            $data['quanty'] = $data['adjust'];
      }




      $documentCountRf = DocumentDetailMultipleCount::findOrFail($id);

      // $quantyDetail = empty($documentCountRf->documentDetail)?'':$documentCountRf->documentDetail->quanty;

      // if (!empty($quantyDetail)) {
      //     if ($quantyDetail != $data['quanty']) {
      //         $data['has_error'] = true;
      //     }else{
      //         $data['has_error'] = false;
      //     }
      // }


      if (!empty($data['adjust'])){
        $documentCountRf->quanty = array_key_exists('quanty', $data) ? $data['quanty'] : $documentCountRf->quanty;
      }
      $documentCountRf->quanty1 = array_key_exists('quanty1', $data) ? $data['quanty1'] : $documentCountRf->quanty1;
      $documentCountRf->quanty2 = array_key_exists('quanty2', $data) ? $data['quanty2'] : $documentCountRf->quanty2;
      $documentCountRf->quanty3 = array_key_exists('quanty3', $data) ? $data['quanty3'] : $documentCountRf->quanty3;
      $documentCountRf->save();


      if (!empty($data['adjust'])) {

        if(!empty($documentCountRf->documentDetailCount->ean14)){
             $sumQuanty = 0;
             foreach ($documentCountRf->documentDetailCount->ean14->detail as $key2 => $detail) {
                if ($detail->product_id === $documentCountRf->product_id) {
                  $detail->quanty = $documentCountRf->quanty;
                  $detail->save();
                }
                $sumQuanty += (int)$detail->quanty;
             }
             $documentCountRf->documentDetailCount->ean14->quanty = $sumQuanty;
             $documentCountRf->documentDetailCount->ean14->save();
        }

      }

      return $documentCountRf;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $documentdetailModel = DocumentDetailCount::findOrFail($id);
        $documentdetailModel->delete();

       return $this->response->noContent();
    }

    public function storeChildCount(Request $request)
    {
        $data = $request->all();
        $documentdetailModel = DocumentDetailCount::create($data);

        return $documentdetailModel;
    }
}
