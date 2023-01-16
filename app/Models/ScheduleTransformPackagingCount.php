<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleTransformPackagingCount extends Model
{
   protected $table = 'wms_schedule_transform_packaging_count';


  protected $fillable = ['schedule_id','schedule_transform_id','count_quanty','count_index'];

}
