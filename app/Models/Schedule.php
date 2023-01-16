<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Common\ScheduleObserver;

class Schedule extends Model
{
    protected $table = 'wms_schedules';

    protected $fillable = ['start_date', 'end_date', 'name', 'zone_id', 'responsible_id', 'status', 'user_id', 'schedule_type','schedule_action','parent_schedule_id', 'company_id'];

    public function schedule_machines()
    {
      return $this->hasMany('App\Models\ScheduleMachine');
    }
    public function schedule_dispatch()
    {
      return $this->hasOne('App\Models\ScheduleDispatch');
    }

    public function schedule_personal()
    {
      return $this->hasMany('App\Models\SchedulePersonal');
    }

    public function schedule_documents()
    {
      return $this->hasMany('App\Models\ScheduleDocument');
    }

    public function schedule_comments()
    {
      return $this->hasMany('App\Models\ScheduleComment');
    }

    public function schedule_ean14()
    {
      return $this->hasMany('App\Models\ScheduleEAN14');
    }

    public function schedule_images()
    {
      return $this->hasMany('App\Models\ScheduleImage');
    }

    public function schedule_receipt()
    {
      return $this->hasOne('App\Models\ScheduleReceipt');
    }
    public function schedule_transform()
    {
      return $this->hasOne('App\Models\ScheduleTransform');
    }
    public function schedule_unjoin()
    {
      return $this->hasOne('App\Models\ScheduleUnjoin');
    }
    public function schedule_restock()
    {
      return $this->hasOne('App\Models\ScheduleRestock');
    }

    public function parent_schedule()
    {
      return $this->belongsTo('App\Models\Schedule','parent_schedule_id');
    }

    public function schedule_position()
    {
      return $this->belongsTo('App\Models\ScheduleCountPosition');
    }

    public function child_schedules()
    {
      return $this->hasMany('App\Models\Schedule','parent_schedule_id');
    }
    public function schedule_transition()
    {
      return $this->hasMany('App\Models\ScheduleTransition');
    }
    // public function orders()
    // {
    //   return $this->hasMany('App\Models\Order');
    // }

    public function responsible()
    {
      return $this->belongsTo('App\Models\Person', 'responsible_id', 'id');
    }

    public function user()
    {
      return $this->belongsTo('App\Models\User');
    }

    public function schedule_stock()
    {
      return $this->hasOne('App\Models\ScheduleStock');
    }

    public function schedule_count()
    {
      return $this->hasMany('App\Models\ScheduleCountDetail');
    }

    public function scheduleTransformResult()
    {
      return $this->hasMany('App\Models\ScheduleTransformResult','transform_task_id');
    }
    public function scheduleTransformResultPackaging()
    {
      return $this->hasMany('App\Models\ScheduleTransformResultPackaging');
    }

    public function schedule_validate_adjust()
    {
      return $this->hasMany('App\Models\ScheduleValidateAdjust');
    }

    public function schedule_transform_validate_adjust()
    {
      return $this->hasMany('App\Models\ScheduleTransformValidateAdjust');
    }

    public static function boot() {
        parent::boot();
        self::observe(new ScheduleObserver);
    }

    public static function insert($arrSchedules)
    {
        foreach ($arrSchedules as $key => $value) {
            Schedule::create($value);
        }
    }
}
