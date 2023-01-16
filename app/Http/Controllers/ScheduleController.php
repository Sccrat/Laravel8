<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\ScheduleTransformDetail;
use App\Enums\ScheduleType;
use App\Enums\ScheduleStatus;
use App\Enums\ScheduleAction;
use App\Models\ScheduleReceipt;
use App\Models\ScheduleTransform;
use App\Models\ScheduleCountPosition;
use App\Models\ScheduleReceiptAdditional;
use App\Common\SchedulesFunctions;
use App\Transformers\ScheduleTransformer;
use App\Models\ScheduleValidateAdjust;
use DB;

class ScheduleController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $companyId = $request->input('company_id');
      $wId = $request->input('warehouse_id');
      $schedule_type = $request->input('schedule_type');
      $schedules = new Schedule;
      if ($schedule_type == ScheduleType::Receipt) {
        $schedules = Schedule::with('schedule_personal.persona.user','schedule_documents.document.clientdocument', 'schedule_comments', 'schedule_receipt.receipt_type', 'schedule_receipt.warehouse', 'schedule_receipt.responsible.user','schedule_receipt.receiptsAdditional.product','schedule_receipt.receiptsAdditional.document');
        if(isset($wId)) {
          $schedules = $schedules->whereHas('schedule_receipt', function($query) use ($wId)
          {
            $query->where('warehouse_id','=', $wId);
          });
        }
      }
      if ($schedule_type == ScheduleType::Dispatch) {
        $schedules = Schedule::with('schedule_personal.persona.user','schedule_documents.document', 'schedule_comments', 'schedule_dispatch', 'schedule_dispatch.warehouse', 'schedule_dispatch.responsible.user');
        if(isset($wId)) {
          //Get the warehouses for receipts
          $schedules = $schedules->whereHas('schedule_dispatch', function($query) use ($wId)
          {
            $query->where('warehouse_id','=', $wId);
          });
        }
      }

      if($schedule_type == ScheduleType::Stock) {
        $schedules = Schedule::with('schedule_stock');
        if(isset($wId)) {
          $schedules = $schedules->whereHas('schedule_stock', function($query) use ($wId)
          {
            $query->where('warehouse_id','=', $wId);
          });
        }

      }

      if(isset($schedule_type) && $schedule_type != ScheduleType::Dispatch) {
        $schedules = $schedules->where('schedule_type', $schedule_type);
      }
      $schedules = $schedules->where('company_id', $companyId)->get();
      return $schedules->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *{
      *    "start_date":"2015-01-01 16:50:00",
      *    "end_date":"2015-02-02 05:30:55",
      *    "name":"Test dos",
      *    "personal": [{ "persona_id": 1}, { "persona_id": 2}],
      *    "machines": [{ "machine_id": 1}],
      *    "orders": [{ "order_id": 1}]
      *}
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $schedule = [];
      DB::transaction(function () use($request,&$schedule) {
        $data = $request->all();
        $data['status'] = ScheduleStatus::Process;
        $schedule = Schedule::create($data);

        if($data['schedule_type'] === ScheduleType::Receipt) {
          $scheduleReceipt = new ScheduleReceipt($data['schedule_receipt']);
          //$dataReceipt = $data['schedule_receipt'];
          $schedule->schedule_receipt()->save($scheduleReceipt);

          $userLeader = SchedulesFunctions::getLeaderByWarehouse($schedule->schedule_receipt->warehouse->id,$this,$data['company_id']);
          $taskName = 'Recibo Contenedor : Gestionar recibo ' . $data['name'] ." ". $schedule->schedule_receipt->warehouse->name ;
          $taskSchedule = [
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'name' => $taskName,
            'schedule_type' => ScheduleType::Task,
            'status' => ScheduleStatus::Process,
            'notified' => false,
            'user_id' => $userLeader->user->id,
            'schedule_action' => ScheduleAction::Assign,
            'parent_schedule_id' => $schedule->id,
            'company_id' => $data['company_id']
          ];
          Schedule::create($taskSchedule);
          // var_dump($userLeader);die;
        }

        // Save the personal
      //  if(array_key_exists('schedule_personal', $data)) {
          //$schedule->schedule_personal()->createMany($data['schedule_personal']);
          //Create the task for each personal
          $taskName = '';
          if($data['schedule_type'] === ScheduleType::Receipt) {
            //Prepare the task for the personal
            $taskName = 'Recibo Contenedor : Recibir contenedor ' . $data['name'] ." ". $schedule->schedule_receipt->warehouse->name ;
            $taskSchedules = [];
            foreach ($data['schedule_personal'] as $row) {
              $user = $row['user'];
              $taskSchedules[] = [
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'name' => $taskName,
                'schedule_type' => ScheduleType::Task,
                'status' => ScheduleStatus::Process,
                'notified' => false,
                'user_id' => $user['id'],
                'schedule_action' => ScheduleAction::Receipt,
                'parent_schedule_id' => $schedule->id,
                'company_id' => $data['company_id']
              ];

              //Send the notification
              $email = $user['email'];
              $name = $row['name'] . ' ' . $row['last_name'];
              //TODO: uncomment for production
              // Mail::queue('emails.task', ['task' => $taskName], function ($mail) use ($email, $name) {
              //     $mail->from('alertas@wms.com', 'Wms alertas');
              //     $mail->to($email, $name)->subject('Nueva Tarea');
              // });

            }

            //Insert the task to the personal
            Schedule::insert($taskSchedules);
          }



        //Save the machines
        if(array_key_exists('schedule_machines', $data)) {
          $schedule->schedule_machines()->createMany($data['schedule_machines']);
        }

        //Save the orders
        if(array_key_exists('schedule_documents', $data)) {
          $schedule->schedule_documents()->createMany($data['schedule_documents']);
        }
        if(array_key_exists('schedule_count', $data)) {
          $schedule->schedule_count()->createMany($data['schedule_count']);
        }

        if (!empty($data['notified'])) {
            $user = $schedule->user;
            if ($user) {
              $email = $user->email;
              $name = $user->person->name . ' ' . $user->person->last_name;
              //TODO: uncomment for production
              // Mail::queue('emails.task', ['task' => $taskName], function ($mail) use ($email, $name) {
              //     $mail->from('alertas@wms.com', 'Wms alertas');
              //     $mail->to($email, $name)->subject('Nueva Tarea');
              // });
            }
        }

      });
        return $this->response->item($schedule, new ScheduleTransformer)->setStatusCode(201);

      // return $this->response->created($schedule->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id,Request $request)
    {
      $data = $request->all();
      $transformInfo = array_key_exists('transform', $data) ? true: false;
      $unjoin = array_key_exists('unjoin', $data) ? true: false;
      $count = array_key_exists('count', $data) ? true: false;
      $receipt = array_key_exists('receipt', $data) ? true: false;
      if ($transformInfo) {
        $relationships = array('schedule_transform.warehouse','schedule_documents', 'schedule_documents.document', 'schedule_machines', 'schedule_personal', 'schedule_receipt', 'schedule_comments',
          'parent_schedule.schedule_transform.scheduleTransformDetail.zone_position',
          'parent_schedule.schedule_transform.scheduleTransformDetail.ean128',
          'parent_schedule.schedule_transform.scheduleTransformDetail.ean14.child_codes.product',
          'parent_schedule.schedule_transform.scheduleTransformDetail.ean14.child_codes.pallet.ean128',
          'parent_schedule.schedule_transform.scheduleTransformDetail.ean14.child_codes.stock',
          'parent_schedule.schedule_transform.scheduleTransformDetail.product',
          'parent_schedule.schedule_transform.scheduleTransformDetail.product.joinReferences.productSource',
          'scheduleTransformResult.product',
          'scheduleTransformResultPackaging.container',
          'scheduleTransformResultPackaging.product',
          'schedule_transform_validate_adjust.schedule_transform_result_packaging.ean14',
          'schedule_transform_validate_adjust.schedule_transform_result_packaging.product',
          'schedule_transform_validate_adjust.schedule_transform_result_packaging.container.container_type',
          'schedule_transform_validate_adjust.schedule_transform_result_packaging.scheduleTransformPackagingCount',
          'parent_schedule.schedule_transform.scheduleTransformResultPackaging.container',
          'parent_schedule.schedule_transform.scheduleTransformResultPackaging.product',
          'parent_schedule.schedule_transform.scheduleTransformResultPackaging.ean14.product',
          'parent_schedule.schedule_transform.scheduleTransformResultPackaging.ean14.pallet.ean128',
          );
      }else if ($receipt) {
        $relationships = array('schedule_documents.document', 'schedule_comments', 'schedule_receipt.receipt_type', 'schedule_receipt.warehouse', 'schedule_receipt.responsible.user','schedule_receipt.receiptsAdditional.product','schedule_receipt.receiptsAdditional.document');
      }else if ($unjoin) {
        $relationships = array('schedule_unjoin.warehouse','schedule_documents', 'schedule_documents.document', 'schedule_machines', 'schedule_personal', 'schedule_receipt', 'schedule_comments',
          'parent_schedule.schedule_unjoin.scheduleUnjoinDetail.zone_position',
          'parent_schedule.schedule_unjoin.scheduleUnjoinDetail.ean128',
          'parent_schedule.schedule_unjoin.scheduleUnjoinDetail.ean14.child_codes.product',
          'parent_schedule.schedule_unjoin.scheduleUnjoinDetail.ean14.child_codes.pallet.ean128',
          'parent_schedule.schedule_unjoin.scheduleUnjoinDetail.ean14.child_codes.stock',
          'parent_schedule.schedule_unjoin.scheduleUnjoinDetail.product.joinReferences.productSource',
          );
      }else if($count){
        $relationships = array('schedule_documents', 'schedule_documents.document',  'schedule_count.product','schedule_count.documentDetail.detailMultiple.product');
      }else{
        $relationships = array('schedule_transform.warehouse','schedule_restock.warehouse','schedule_unjoin.warehouse','schedule_documents', 'schedule_documents.document', 'schedule_machines', 'schedule_personal', 'schedule_receipt', 'schedule_comments','child_schedules.user.person',
          'child_schedules.schedule_count.product',
          'schedule_transform.scheduleTransformDetail.zone_position',
          'schedule_transform.scheduleTransformDetail.ean128',
          'schedule_transform.scheduleTransformDetail.ean14.child_codes.product',
          'schedule_transform.scheduleTransformDetail.ean14.child_codes.pallet.ean128',
          'schedule_transform.scheduleTransformDetail.ean14.child_codes.stock',
          'schedule_transform.scheduleTransformDetail.product',
          'schedule_transform.scheduleTransformDetail.reasonCode',
          'schedule_transform.scheduleTransformResult.product',
          'schedule_transform.scheduleTransformResultPackaging.product',
          'schedule_transform.scheduleTransformResultPackaging.ean14',
          'schedule_transform.scheduleTransformResultPackaging.container.container_type',
          'schedule_transform.scheduleTransformResultPackaging.scheduleTransformPackagingCount'
          );
      }
      $schedules = schedule::with($relationships)->findOrFail($id);
      return $schedules->toArray();
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
      
      DB::transaction(function () use($request,$id) {
        $data = $request->all();
        $schedule = Schedule::findOrfail($id);

        

        $schedule->name = array_key_exists('name', $data) ? $data['name'] : $schedule->name;
        $schedule->start_date = array_key_exists('start_date', $data) ? $data['start_date'] : $schedule->start_date;
        $schedule->end_date = array_key_exists('end_date', $data) ? $data['end_date'] : $schedule->end_date;
        $schedule->status = array_key_exists('status', $data) ? $data['status'] : $schedule->status;
        $schedule->notified = array_key_exists('notified', $data) ? $data['notified'] : $schedule->notified;
        // $schedule->seal = array_key_exists('seal', $data) ? $data['seal'] : $schedule->seal;
        // $schedule->officer = array_key_exists('officer', $data) ? $data['officer'] : $schedule->officer;

        if (array_key_exists('status', $data) &&
          ($data['status']===ScheduleStatus::Closed ||
            $data['status']===ScheduleStatus::TheoricClosed)
          ) {
            if ($data['schedule_type'] === ScheduleType::Receipt && array_key_exists('schedule_type', $data)) {
                // SchedulesFunctions::createTaskCountunits($id,$data['company_id'],$data['flag']);
                $childUpdate = $schedule->child_schedules()->get();
                foreach ($childUpdate as $key => $value) {
                  $value->status = ScheduleStatus::Closed;
                  $value->save();
                }
            }
        }

        if(array_key_exists('start_date', $data) || array_key_exists('end_date', $data) || array_key_exists('status', $data)) {
          $schedule->save();
        }

        if($data['schedule_type'] === ScheduleType::Receipt && array_key_exists('schedule_receipt', $data)) {
          
          $scheduleReceipt = $schedule->schedule_receipt;
          $receipt = $data['schedule_receipt'];
          $scheduleReceipt->officer_name = array_key_exists('officer_name', $receipt) ? $receipt['officer_name'] : $scheduleReceipt->officer_name;
          $scheduleReceipt->city = array_key_exists('city', $receipt) ? $receipt['city'] : $scheduleReceipt->city;
          $scheduleReceipt->officer_phone = array_key_exists('officer_phone', $receipt) ? $receipt['officer_phone'] : $scheduleReceipt->officer_phone;
          $scheduleReceipt->driver_identification = array_key_exists('driver_identification', $receipt) ? $receipt['driver_identification'] : $scheduleReceipt->driver_identification;
          $scheduleReceipt->driver_name = array_key_exists('driver_name', $receipt) ? $receipt['driver_name'] : $scheduleReceipt->driver_name;
          $scheduleReceipt->driver_phone = array_key_exists('driver_phone', $receipt) ? $receipt['driver_phone'] : $scheduleReceipt->driver_phone;
          $scheduleReceipt->vehicle_plate = array_key_exists('vehicle_plate', $receipt) ? $receipt['vehicle_plate'] : $scheduleReceipt->vehicle_plate;
          $scheduleReceipt->company = array_key_exists('company', $receipt) ? $receipt['company'] : $scheduleReceipt->company;
          $scheduleReceipt->company_phone = array_key_exists('company_phone', $receipt) ? $receipt['company_phone'] : $scheduleReceipt->company_phone;
          $scheduleReceipt->receipt_type_id = array_key_exists('receipt_type_id', $receipt) ? $receipt['receipt_type_id'] : $scheduleReceipt->receipt_type_id;
          $scheduleReceipt->seal = array_key_exists('seal', $receipt) ? $receipt['seal'] : $scheduleReceipt->seal;
          $scheduleReceipt->officer = array_key_exists('officer', $receipt) ? $receipt['officer'] : $scheduleReceipt->officer;
          $scheduleReceipt->provider = array_key_exists('provider', $receipt) ? $receipt['provider'] : $scheduleReceipt->provider;
          $scheduleReceipt->client = array_key_exists('client', $receipt) ? $receipt['client'] : $scheduleReceipt->client;
          $scheduleReceipt->bl = array_key_exists('bl', $receipt) ? $receipt['bl'] : $scheduleReceipt->bl;
          $scheduleReceipt->container_number = array_key_exists('container_number', $receipt) ? $receipt['container_number'] : $scheduleReceipt->container_number;
          $scheduleReceipt->container_weight = array_key_exists('container_weight', $receipt) ? $receipt['container_weight'] : $scheduleReceipt->container_weight;
          $scheduleReceipt->responsible_id = array_key_exists('responsible_id', $receipt) ? $receipt['responsible_id'] : $scheduleReceipt->responsible_id;

          $scheduleReceipt->warehouse_id = array_key_exists('warehouse_id', $receipt) ? $receipt['warehouse_id'] : $scheduleReceipt->warehouse_id;
          $scheduleReceipt->zone_id = array_key_exists('zone_id', $receipt) ? $receipt['zone_id'] : $scheduleReceipt->zone_id;



          $scheduleReceipt->validation_status = array_key_exists('validation_status', $receipt) ? $receipt['validation_status'] : $scheduleReceipt->validation_status;

          if ($scheduleReceipt->validation_status !== ScheduleStatus::Process) {
            $childUpdate = $schedule->child_schedules()->where('schedule_action',ScheduleAction::Validate)->get();
            foreach ($childUpdate as $key => $value) {
              $value->status = ScheduleStatus::Closed;
              $value->save();
            }
          }

          if ($scheduleReceipt->validation_status == ScheduleStatus::Process) {

            $childUpdate = $schedule->child_schedules()->where('schedule_action',ScheduleAction::Authorize)->get();
            foreach ($childUpdate as $key => $value) {
              $value->status = ScheduleStatus::Closed;
              $value->save();
            }

          }


          //$dataReceipt = $data['schedule_receipt'];
          $schedule->schedule_receipt()->save($scheduleReceipt);
        }

        if($data['schedule_type'] === ScheduleType::Dispatch && array_key_exists('schedule_receipt', $data)) {

          // return 'sisas';

          
          $scheduleReceipt = $schedule->schedule_receipt;
          return $scheduleReceipt;
          $receipt = $data['schedule_receipt'];

          // $scheduleReceipt->seal = array_key_exists('seal', $receipt) ? $receipt['seal'] : $scheduleReceipt->seal;

          if ($scheduleReceipt->validation_status !== ScheduleStatus::Process) {
            $childUpdate = $schedule->child_schedules()->where('schedule_action',ScheduleAction::Validate)->get();
            foreach ($childUpdate as $key => $value) {
              $value->status = ScheduleStatus::Closed;
              $value->save();
            }
          }

          if ($scheduleReceipt->validation_status == ScheduleStatus::Process) {

            $childUpdate = $schedule->child_schedules()->where('schedule_action',ScheduleAction::Authorize)->get();
            foreach ($childUpdate as $key => $value) {
              $value->status = ScheduleStatus::Closed;
              $value->save();
            }

          }


          //$dataReceipt = $data['schedule_receipt'];
          $schedule->schedule_dispatch()->save($scheduleReceipt);
        }

        // Save the personal
        if(array_key_exists('schedule_personal', $data)) {
          DB::table('wms_schedule_personal')->where('schedule_id', '=', $id)->delete();
          $childUpdate = $schedule->child_schedules()->where('schedule_action',ScheduleAction::Receipt)->get();
          foreach ($childUpdate as $key => $value) {
            $value->delete();
          }
          $schedule->schedule_personal()->createMany($data['schedule_personal']);

          //Prepare the task for the personal
          $taskName = 'Recibo Contenedor : Recibir contenedor ' . $data['name'] ." ". $schedule->schedule_receipt->warehouse->name ;
          $taskSchedules = [];
          foreach ($data['schedule_personal'] as $row) {
            $user = $row['user'];
            $taskSchedules[] = [
              'start_date' => $data['start_date'],
              'end_date' => $data['end_date'],
              'name' => $taskName,
              'schedule_type' => ScheduleType::Task,
              'status' => ScheduleStatus::Process,
              'notified' => false,
              'user_id' => $user['id'],
              'schedule_action' => ScheduleAction::Receipt,
              'parent_schedule_id' => $schedule->id,
            ];

            //Send the notification
            $email = $user['email'];
            $name = $row['name'] . ' ' . $row['last_name'];
            //TODO: uncomment for production
            // Mail::queue('emails.task', ['task' => $taskName], function ($mail) use ($email, $name) {
            //     $mail->from('alertas@wms.com', 'Wms alertas');
            //     $mail->to($email, $name)->subject('Nueva Tarea');
            // });
          }
          //Insert the task to the personal
          Schedule::insert($taskSchedules);
        }

        //Save the machines
        if(array_key_exists('schedule_machines', $data)) {
          DB::table('wms_schedule_machines')->where('schedule_id', '=', $id)->delete();
          $schedule->schedule_machines()->createMany($data['schedule_machines']);
        }

        //Save the orders
        if(array_key_exists('schedule_documents', $data)) {
          DB::table('wms_schedule_documents')->where('schedule_id', '=', $id)->delete();
          $schedule->schedule_documents()->createMany($data['schedule_documents']);
        }
      });

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
        $schedule = Schedule::findOrfail($id);
        $shedulesDeleted = $schedule->child_schedules()->get();
        foreach ($shedulesDeleted as $key => $value) {
          $value->delete();
        }
        $schedule->delete();

        return $this->response->noContent();
    }

    public function getSchedulesByWarehouseId($id)
    {
      // TODO: Traer todos los schedules por bodega

    }

    public function getCalendar(Request $request)
    {
      
      $companyId = $request->input('company_id');
      $wId = $request->input('warehouse_id');
      $scheduleType = $request->input('type');
      $schedules = DB::table('wms_schedules')
                    ->select('wms_schedules.id', 'start_date as start', 'end_date as end', 'wms_schedules.name as title', 'wms_schedules.status','wms_schedules.schedule_action')
                    ->where('company_id', $companyId);

      //Get the warehouses for receipts
      if(isset($wId) && $scheduleType == ScheduleType::Receipt) {
        // return 'entro';
        $schedules = $schedules->join('wms_schedule_receipts', 'wms_schedules.id', '=','wms_schedule_receipts.schedule_id') ->where('wms_schedule_receipts.warehouse_id', $wId);
      }

      //Get warehouses for stocks
      if(isset($wId) && $scheduleType == ScheduleType::Stock) {
        
        $schedules = $schedules->join('wms_schedule_stocks', 'wms_schedules.id', '=','wms_schedule_stocks.schedule_id') ->where('wms_schedule_stocks.warehouse_id', $wId);
      }

      if(isset($scheduleType)) {
        $schedules = $schedules->where('schedule_type', $scheduleType);
      }
      $schedules = $schedules->orderBy('end')->get();
      return $schedules;
    }


    public function sendMail(Request $request, $id)
    {

      $data = $request->all();
      $schedule = Schedule::findOrfail($id);

      $schedule->notified = array_key_exists('notified', $data) ? $data['notified'] : $schedule->notified;

      $schedule->save();

      Mail::send('emails.test', ['mensajini' => $data['name']], function ($m) {
          $m->from('alertas@wms.com', 'Wms alertas');

          $m->to('mauriciocorreamolina90@gmail.com', 'Fabian Marin')->subject('Laravel mail');
      });
    }

    public function saveTransformDetail(Request $request,$id)
    {
      $data = $request->all();
      $detailModel = ScheduleTransformDetail::find($id);

      $detailModel->detail_status = !empty($data['detail_status'])?$data['detail_status']:$detailModel->detail_status;
      $detailModel->reason_code_id = !empty($data['reason_code_id'])?$data['reason_code_id']:$detailModel->reason_code_id;
      $detailModel->save();
    }

    public function getTransformData(Request $request,$id)
    {
        $transform = ScheduleTransform::findOrFail($id)->where('id',$id)->with('scheduleTransformResultPackaging');

        return $transform->get();
    }

    public function saveReceiptAdditionalReferences(Request $request)
    {
      $data = $request->all();

      $idAdditional = array_key_exists('id', $data) ? $data['id'] : NULL;

      if (!$idAdditional) {
        $additionalModel = new ScheduleReceiptAdditional;
      }else{
        $additionalModel = ScheduleReceiptAdditional::findOrfail($idAdditional);
      }

      $additionalModel->product_id = array_key_exists('product_id', $data) ? $data['product_id'] : $additionalModel->product_id;
      $additionalModel->document_id = array_key_exists('document_id', $data) ? $data['document_id'] : $additionalModel->document_id;

      $additionalModel->approve_additional = array_key_exists('approve_additional', $data) ? $data['approve_additional'] : $additionalModel->approve_additional;
      $additionalModel->schedule_receipt_id = array_key_exists('schedule_receipt_id', $data) ? $data['schedule_receipt_id'] : $additionalModel->schedule_receipt_id;

      $additionalModel->active = array_key_exists('active', $data) ? $data['active'] : $additionalModel->active;

      $additionalModel->save();
      return $additionalModel;
    }

    public function deleteReceiptAdditionalReferences($id)
    {
      $additionalModel = ScheduleReceiptAdditional::findOrfail($id);
      $additionalModel->delete();
      return $additionalModel;
    }

    public function getAdditionalReferencesBySchedule($id)
    {
      $additionals = schedule::with('schedule_receipt.receiptsAdditional.product','schedule_receipt.receiptsAdditional.document')->findOrFail($id)->toArray();
      return $additionals['schedule_receipt']['receipts_additional'];
    }

    public function saveValidateAdjustSchedule(Request $request)
    {
      $data = $request->all();
      $docId = $data['document_id'];
      $docDetailId = $data['document_detail_id'];
      $companyId = $data['company_id'];

      $scheduleTypeValidate = ScheduleType::Validate;
      $scheduleStatusProcess = ScheduleStatus::Process;

      $scheduleValidateAdjust = [
        'document_detail_id' => $docDetailId
      ];

      /*
        Busco si el documento tiene una tarea asignada con las siguientes condiciones:
        schedule_type = validate_adjust
        status = process
      */
      $schedule = ScheduleValidateAdjust::whereHas('schedule', function ($q) use ($scheduleTypeValidate, $scheduleStatusProcess)
      {
        $q->where('schedule_type', $scheduleTypeValidate)->where('status', $scheduleStatusProcess);
      })
      ->whereHas('document_detail', function ($q) use ($docId)
      {
        $q->where('document_id', $docId);
      })
      ->first();

      // Si no existe => creo una tarea nueva.
      if(empty($schedule)) {
        $newSchedule = $data['schedule'];
        $cediCharge = SchedulesFunctions::getCediBossByWarehouse($newSchedule['warehouse_id'],$this,$companyId);
        $newSchedule['user_id'] = $cediCharge->user->id;
        $newSchedule['company_id'] = $companyId;

        $idNewSchedule = Schedule::create($newSchedule)->id;

        $scheduleValidateAdjust['schedule_id'] = $idNewSchedule;
      }
      // Si existe => agrego la referencia a validar a la tarea existen.
      else {
        $scheduleValidateAdjust['schedule_id'] = $schedule['schedule_id'];
      }

      // Relaciono la referencia a la tarea principal
      ScheduleValidateAdjust::create($scheduleValidateAdjust);

      return $this->response->created();
    }
}
