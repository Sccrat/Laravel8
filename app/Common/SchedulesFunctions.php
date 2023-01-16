<?php

namespace App\Common;
use DB;
use App\Models\Stock;
use App\Common\Settings;
use App\Models\Charge;
use App\Enums\ScheduleType;
use App\Enums\ScheduleStatus;
use App\Enums\ScheduleAction;
use App\Models\Schedule;
use App\Models\Warehouse;

/**
 * Custom class for confoguration settings stored in the table wms_settings
 */
class SchedulesFunctions
{
        //Recibe la referencia, y con ella consulta el stock y de acuerdo a en que bodegas lo encuentra
    //consulta el lider de dicha bodega, este usuario se devuleve para posteriormente insertar la tarea
    // Return Array de usuarios
    public static function getLeadersWarehouseFromReference($reference,$context)
    {

            $findStock = Stock::with(
                    'product','zone_position.zone.warehouse'
                    ,'ean128','ean14','ean13'
                    );

            if(isset($reference)) {
              $findStock = $findStock->whereHas('product', function ($q) use ($reference) {
                $q->where('reference', $reference);
              });
            }


            $findStock  = $findStock->get()->toArray();
            if (empty($findStock)) {
                return $context->response->error('storage_pallet_product_no_found', 404);
            }

            // Despues de consultar los elementos existentes con la referencia buscada se identifica cada una de las bodegas implicadas
            $warehouses = array();

            foreach ($findStock as $keyStock => $valueStock) {
              $warehouse_id = $valueStock['zone_position']['zone']['warehouse_id'];
              $warehouseObj = $valueStock['zone_position']['zone']['warehouse'];
              if (!array_key_exists($warehouse_id, $warehouses)) {
                $warehouseObj['stockInfo'][] = $valueStock;
                $warehouses[$warehouse_id] = $warehouseObj;
              }else{
                $warehouses[$warehouse_id]['stockInfo'][] = $valueStock;
              }
            }

            if (empty($warehouses)) {
                return $context->response->error('storage_pallet_warehouse_unit_no_found', 404);
            }




            // Obtenemos el parametro de configuracion del cargo lider de la bodega
            $settingsObj = new Settings();


            // TODO : usar un ennum
            $chargeUserName = $settingsObj->get('leader_charge');

            if (empty($chargeUserName)) {
              return $context->response->error('not_found_charge_warehouse', 404);
            }

            $users = array();

            foreach ($warehouses as $keyWare => $warehouse) {
                // INICIO - consulta para obtener el jefe de la bodega para realizar la notificacion y asignacion de la tarea

                // Capturamos la bodega de la cual encontraremos su lider
                $warehouse_id = $warehouse['id'];
                // Consultamos el cargo configurado como lider de bodega y obtenemos los usuarios relacionados al dicho cargo
                $chargeUser = Charge::with(
                  array(
                        'personal'=>function ($q) use ($warehouse_id){
                            // hacemos el filtro para solo trauer los lideres de la bodga seleccionada
                            $q->where('warehouse_id',$warehouse_id);
                            // Y que dicha persona tenga un usuario del sistema relacionado ya que de alli sacamos el correo para hacer la notificacion para posteriormente realizar la tarea
                            $q->has('user');
                        },
                        'personal.user'
                        )
                  )->where('name',$chargeUserName)->first();
                if (empty($chargeUser)) {
                      return $context->response->error('no_found_leader_warehouse', 404);
                }



                // Obtenemos la primera persona que cumpla con las condicinoes de tener el cargo lider, pertencer a la bodega seleccionada y que tenga un usuario relacionado
                $user = $chargeUser->personal->first();
                if (empty($user)) {
                      return $context->response->error('no_found_leader_warehouse', 404);
                }

                $user['stockInfo'] = $warehouse['stockInfo'];
                array_push($users, $user);
                // FIN
            }


            return $users;
    }

    public static function createTaskCountunits($taskReceiptId,$companyId,$flag)
    {
        // $user = User::where('personal_id', $receipt->responsible_id)->first();
        //$personalId = $receipt->responsible_id;
        $schedule = Schedule::findOrfail($taskReceiptId);
        $receiptInfo = $schedule->schedule_receipt;

        $settingsObj = new Settings($companyId);


        // TODO : usar un ennum


        if ($receiptInfo) {


            $chargeUserName = $settingsObj->get('leader_charge');

            if (empty($chargeUserName)) {
              return $context->response->error('not_found_charge_warehouse', 404);
            }


            $warehouse_id = $receiptInfo->warehouse_id;

            $chargeUser = Charge::with(
                array(
                    'personal'=>function ($q) use ($warehouse_id,$companyId){
                        // hacemos el filtro para solo trauer los lideres de la bodga seleccionada
                        $q->where('warehouse_id',$warehouse_id)->where('company_id',$companyId);
                        // Y que dicha persona tenga un usuario del sistema relacionado ya que de alli sacamos el correo para hacer la notificacion para posteriormente realizar la tarea
                        $q->has('user');
                    },
                    'personal.user'
                )
            )->where('name',$chargeUserName)->first();

            if (empty($chargeUser)) {
                  return $context->response->error('no_found_leader_warehouse', 404);
            }



            // Obtenemos la primera persona que cumpla con las condicinoes de tener el cargo lider, pertencer a la bodega seleccionada y que tenga un usuario relacionado
            $user = $chargeUser->personal->first()->user;
            if (empty($user)) {
                  return $context->response->error('no_found_leader_warehouse', 404);
            }

            // if ($flag) {
            //   $taskName = 'Recibo Contenedor : Contar unidades contenedor ' . $schedule->name ." ". $schedule->schedule_receipt->warehouse->name ;
            //   $task = [
            //     'start_date' => $schedule->end_date,
            //     'name' => $taskName,
            //     'schedule_type' => ScheduleType::Count,
            //     'status' => ScheduleStatus::Process,
            //     'schedule_action' => ScheduleAction::Assign,
            //     'user_id' => $user->id,
            //     'company_id' =>$companyId
            //   ];

            //   $newSchedule = Schedule::create($task);

            //   $documentsSchedule = $schedule->schedule_documents()->get()->toArray();
            //   $newSchedule->schedule_documents()->createMany($documentsSchedule);

            //   $personalSchedule = $schedule->schedule_personal()->get();
            //   // $newSchedule->schedule_personal()->createMany($documentsSchedule);

            //   $taskName = 'Recibo Contenedor : Contar unidades  Pendiente Asignación';
            //   $taskSchedules = [];


            //   foreach ($personalSchedule as $row) {
            //       if (!empty($row->persona->user)) {
            //           $user = $row->persona->user;
            //           $taskSchedules[] = [
            //             'name' => $taskName,
            //             'schedule_type' => ScheduleType::Count,
            //             'status' => ScheduleStatus::Pendding,
            //             'notified' => false,
            //             'user_id' => $user->id,
            //             'schedule_action' => '',
            //             'parent_schedule_id' => $newSchedule->id,
            //           ];

            //           //Send the notification
            //           $email = $user['email'];
            //           $name = $row['name'] . ' ' . $row['last_name'];
            //           //TODO: uncomment for production
            //           // Mail::queue('emails.task', ['task' => $taskName], function ($mail) use ($email, $name) {
            //           //     $mail->from('alertas@wms.com', 'Wms alertas');
            //           //     $mail->to($email, $name)->subject('Nueva Tarea');
            //           // });
            //       }
            //   }
            //   //Insert the task to the personal
            //   Schedule::insert($taskSchedules);
            // }



        }
    }

    public static function getLeaderByWarehouse($warehouse_id,$context,$companyId)
    {
        // Obtenemos el parametro de configuracion del cargo lider de la bodega
        $settingsObj = new Settings($companyId);


        // TODO : usar un ennum
        $chargeUserName = $settingsObj->get('leader_charge');

        if (empty($chargeUserName)) {
          return $context->response->error('not_found_charge_warehouse', 404);
        }

        // Consultamos el cargo configurado como lider de bodega y obtenemos los usuarios relacionados al dicho cargo
        $chargeUser = Charge::with(
          array(
                'personal'=>function ($q) use ($warehouse_id,$companyId){
                    // hacemos el filtro para solo trauer los lideres de la bodga seleccionada
                    $q->where('warehouse_id',$warehouse_id)->where('company_id',$companyId);
                    // Y que dicha persona tenga un usuario del sistema relacionado ya que de alli sacamos el correo para hacer la notificacion para posteriormente realizar la tarea
                    $q->has('user');
                },
                'personal.user'
                )
          )->where('name',$chargeUserName)->where('company_id',$companyId)->first();
        if (empty($chargeUser)) {
              return $context->response->error('no_found_leader_warehouse', 404);
        }



        // Obtenemos la primera persona que cumpla con las condicinoes de tener el cargo lider, pertencer a la bodega seleccionada y que tenga un usuario relacionado
        $user = $chargeUser->personal->first();
        if (empty($user)) {
              return $context->response->error('no_found_leader_warehouse', 404);
        }

        return $user;
    }

    public static function getCediBossByWarehouse($warehouse_id,$context,$companyId)
    {
        // Obtenemos el parametro de configuracion del jefe del cedi
        $settingsObj = new Settings($companyId);

        // TODO : usar un ennum
        $chargeUserName = $settingsObj->get('cedi_charge');

        if (empty($chargeUserName)) {
            return $context->response->error('not_found_charge_warehouse', 404);
        }

        $cediId = Warehouse::where('id', $warehouse_id)->value('distribution_center_id');

        // Consultamos el cargo configurado como lider de bodega y obtenemos los usuarios relacionados al dicho cargo
        $chargeUser = Charge::with(
            [
                'personal'=>function ($q) use ($cediId){
                    // hacemos el filtro para solo trauer los lideres de la bodga seleccionada
                    $q->where('distribution_center_id',$cediId);
                    // Y que dicha persona tenga un usuario del sistema relacionado ya que de alli sacamos el correo para hacer la notificacion para posteriormente realizar la tarea
                    $q->has('user');
                },
                'personal.user'
            ]
        )->where('name',$chargeUserName)->first();

        if (empty($chargeUser)) {
            return $context->response->error('no_found_leader_warehouse', 404);
        }

        // Obtenemos la primera persona que cumpla con las condicinoes de tener el cargo lider, pertencer a la bodega seleccionada y que tenga un usuario relacionado
        $user = $chargeUser->personal->first();
        if (empty($user)) {
              return $context->response->error('no_found_leader_warehouse', 404);
        }

        return $user;
    }
}
