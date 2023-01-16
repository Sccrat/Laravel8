<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\User;
use App\Enums\Status;
use App\Models\Schedule;
use App\Models\ScheduleCountPosition;
use App\Enums\ScheduleType;
use App\Enums\ScheduleAction;
use App\Enums\ScheduleStatus;
use App\Common\Settings;
use DateTimeZone;
use Carbon\Carbon;
use DB;

class PersonController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $charge = $request->input('charge');
      $group = $request->input('group');
      $cedi = $request->input('cedi');
      $user = $request->input('user');
      $task = $request->input('task');
      $companyId = $request->input('company_id');
      $personal = Person::with('group','zone', 'warehouse', 'distribution_center','charge', 'user.role', 'secondary_group')
                  ->where('company_id', $companyId);
      if(isset($charge)) {
        $personal = $personal->whereHas('charge', function ($query) use ($charge, $cedi)
        {
          $query->where('name', urldecode($charge));
        })->where('distribution_center_id', $cedi);
      }

      if(isset($group)) {
        // $personal = $personal->whereHas('group', function ($query) use ($group, $cedi)
        // {
        //   $query->where('name', urldecode($group));
        // })->where('distribution_center_id', $cedi);
        $personal = $personal->where(function ($q) use ($group, $cedi)
        {
          $q->whereHas('group', function ($query) use ($group, $cedi)
          {
            $query->where('name', urldecode($group));
          })->orWhereHas('secondary_group', function ($query) use ($group, $cedi)
          {
            $query->where('name', urldecode($group));
          });
        });
      }

      if(isset($user)) {
        $personal = $personal->whereNotIn('id', function ($q) {
          $q->select(DB::Raw('ifnull(`personal_id`,0)'))->from('admin_users');
        });
      }


      if(isset($task)) {
        $personal = $personal->whereIn('id', function ($q) {
          $q->select('personal_id')->from('admin_users');
        });
      }

      $personal = $personal->orderBy('name')->get();
      return $personal->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\PersonalRequest $request)
    {
      $data = $request->all();
      Person::create($data);
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
      // $person = DB::table('wms_personal')
      //           ->join('wms_zones', 'wms_zones.id', '=', 'wms_personal.zone_id')
      //           ->join('wms_warehouses', 'wms_warehouses.id', '=', 'wms_zones.warehouse_id')
      //           ->select('wms_personal.id','wms_personal.name', 'wms_personal.last_name','wms_personal.identification', 'wms_personal.status', 'wms_warehouses.name as warehouse_name' ,'wms_personal.zone_id', 'wms_personal.group_id', 'wms_zones.warehouse_id', 'wms_zones.name as zone_name' ,'wms_personal.charge_id', 'wms_personal.distribution_center_id')
      //           ->where('wms_personal.id',$id)
      //           ->first();
      //
      // return (array)$person;
      $person = Person::findOrFail($id);
      // $person = Person::with('work_area.structure_area')->findOrFail($id);
      return $person->toArray();
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
      $person = Person::findOrFail($id);

      $person->name = array_key_exists('name', $data) ? $data['name'] : $person->name;
      $person->last_name = array_key_exists('last_name', $data) ? $data['last_name'] : $person->last_name;
      $person->identification = array_key_exists('identification', $data) ? $data['identification'] : $person->identification;
      $person->status = array_key_exists('status', $data) ? $data['status'] : $person->status;
      $person->zone_id = array_key_exists('zone_id', $data) ? $data['zone_id'] : $person->zone_id;
      $person->group_id = array_key_exists('group_id', $data) ? $data['group_id'] : $person->group_id;
      $person->charge_id = array_key_exists('charge_id', $data) ? $data['charge_id'] : $person->charge_id;
      $person->is_leader = array_key_exists('is_leader', $data) ? $data['is_leader'] : $person->is_leader;
      $person->warehouse_id = array_key_exists('warehouse_id', $data) ? $data['warehouse_id'] : $person->warehouse_id;
      $person->distribution_center_id = array_key_exists('distribution_center_id', $data) ? $data['distribution_center_id'] : $person->distribution_center_id;
      $person->secondary_group_id = array_key_exists('secondary_group_id', $data) ? $data['secondary_group_id'] : $person->secondary_group_id;
      $person->group_id = array_key_exists('group_id', $data) ? $data['group_id'] : $person->group_id;
      $person->vinculation_type_id = array_key_exists('vinculation_type_id', $data) ? $data['vinculation_type_id'] : $person->vinculation_type_id;

      $person->save();

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
      $person = Person::findOrFail($id);
      $person->status = Status::Inactive;
      $person->save();

      return $this->response->noContent();
    }

    public function getByGroup($id)
    {
      $personal = Person::with('group','zone', 'warehouse', 'distribution_center')
      ->where('group_id', $id)
      ->orderBy('name')->get();
      return $personal->toArray();
    }

    public function createSchedule(Request $request)
    {
      $data = $request->all();
      $usuario = $data['user_id'];
      $Position = $data['zone_position_id'];
      $warehouseId = $data['warehouse_id'];

      $settingsObj = new Settings();
      $chargeUserName = $settingsObj->get('leader_charge');
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


      $taskSchedulesLeader = [
             'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0].' '.explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
             'name' => 'Eliminación configuración :'.' '.$Position,
             'schedule_action' => ScheduleAction::Store,
             'status' => ScheduleStatus::Process,
             'user_id' => $user->id
           ];

           $scheduleLeader = Schedule::create($taskSchedulesLeader);


      $taskSchedules = [
             'start_date' => $datetime = explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[0].' '.explode(" ", Carbon::now(new DateTimeZone('America/Bogota')))[1],
             'name' => 'Contar Unidades zona de picking :'.$Position,
             'schedule_type' => ScheduleType::Restock,
             'schedule_action' => ScheduleAction::Picking,
             'parent_schedule_id' => $scheduleLeader->id,
             'status' => ScheduleStatus::Process,
             'user_id' => $usuario
           ];

     $schedule = Schedule::create($taskSchedules);

        $data['schedule_id'] = $schedule->id;
        $schedulePosition = ScheduleCountPosition::create($data);


     return $this->response->created();
    }

    public function setLeader($id)
    {
      //Get the person
      $person = Person::findOrFail($id);

      //Make everyone as no leader
      DB::table('wms_personal')
            ->where('group_id', $person->group_id);
            // ->update(['is_leader' => false]);

      //set the person as leader and save changes
      $person->is_leader = true;
      $person->save();

      //Response
      return $this->response->noContent();
    }

    public function getPersonalByZone($id)
    {
      $personal = Person::with('zone','group','distribution_center','warehouse')
      ->where('zone_id',$id)
      ->orderBy('name')
      ->get();
      return $personal->toArray();
    }

    public function getPersonalByDistributionCenter($id)
    {
      $personal = Person::with('distribution_center','charge')
      ->where('distribution_center_id', $id)
      ->whereNull('warehouse_id')
      ->whereNull('zone_id')
      ->orderBy('name')
      ->get();
      return $personal->toArray();
    }

    // ESTE MÉTODO TRAE LAS PERSONAS ASIGNADAS SOLAMENTE A LA BODEGA (NO INCLUYE LAS ZONAS)
    public function getPersonalByWarehouse($id)
    {
      $personal = Person::with('warehouse','distribution_center','group')
      ->where('warehouse_id', $id)
      ->whereNull('zone_id')
      ->orderBy('name')
      ->get();
      return $personal->toArray();
    }

    // ÉSTE MÉTODO TRAE TODAS LAS PERSONAS DE LA BODEGA (INCLUYE ZONAS)
    public function getAllPersonalByWarehouse($id)
    {
      $personal = Person::with('warehouse','group','user')
      ->where('warehouse_id', $id)
      ->orderBy('name')
      ->get();
      return $personal->toArray();
    }

    public function getAllPersonal(Request $request)
    {
      $personal = $request->input('personalname');
      $allPersonal = User::with('company','person','person.user');
      if (!empty($personal)) {
        $allPersonal->where('name','LIKE','%'.$personal.'%');
      }
      return $allPersonal->orderBy('name')->get()->toArray();
    }

    public function getPersonalGroupCharge(Request $request, $id)
    {
      $group = $request->input('group');
      // personal por bodega
      $warehouse = DB::table('wms_personal')
      ->join('wms_charges', 'wms_charges.id', '=', 'wms_personal.charge_id')
      ->join('wms_warehouses', 'wms_warehouses.id', '=', 'wms_personal.warehouse_id')
      ->where('wms_personal.distribution_center_id', $id)
      ->whereNotNull('warehouse_id')
      ->whereNull('zone_id')
      ->select(DB::raw('"warehouse" as type'),'wms_personal.warehouse_id','wms_personal.distribution_center_id','wms_personal.zone_id','charge_id','wms_charges.name as charge_name','wms_warehouses.name as name',DB::raw('COUNT(charge_id) as total'))
      ->orderBy('name','charge_name')
      ->groupBy('charge_id', 'warehouse_id');

      if (isset($group)) {
        $warehouse = $warehouse->where('wms_personal.group_id',$group);
      }

      // personal por zona
      $zone = DB::table('wms_personal')
      ->join('wms_charges', 'wms_charges.id', '=', 'wms_personal.charge_id')
      ->join('wms_zones', 'wms_zones.id', '=', 'wms_personal.zone_id')
      ->where('distribution_center_id', $id)
      ->whereNotNull('wms_personal.warehouse_id')
      ->whereNotNull('wms_personal.zone_id')
      ->select(DB::raw('"zone" as type'),'wms_personal.warehouse_id','wms_personal.distribution_center_id','wms_personal.zone_id','charge_id','wms_charges.name as charge_name','wms_zones.name as name',DB::raw('COUNT(charge_id) as total'))
      ->orderBy('name','charge_name')
      ->groupBy('charge_id', 'warehouse_id', 'zone_id');

      if (isset($group)) {
        $zone = $zone->where('wms_personal.group_id',$group);
      }

      $cedi = DB::table('wms_personal')
      ->join('wms_charges', 'wms_charges.id', '=', 'wms_personal.charge_id')
      ->join('wms_distribution_centers', 'wms_distribution_centers.id', '=', 'wms_personal.distribution_center_id')
      ->where('distribution_center_id', $id)
      ->whereNull('warehouse_id')
      ->whereNull('zone_id')
      ->unionAll($warehouse)->unionAll($zone)
      ->select(DB::raw('"cedi" as type'),'wms_personal.warehouse_id','wms_personal.distribution_center_id','wms_personal.zone_id','charge_id','wms_charges.name as charge_name','wms_distribution_centers.name as name',DB::raw('COUNT(charge_id) as total'))

      ->groupBy('charge_id');
      // ->get();

      if (isset($group)) {
        $cedi = $cedi->where('wms_personal.group_id',$group);
      }

      $personal = $cedi->get();
      return $personal;
    }
}
