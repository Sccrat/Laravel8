<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    protected $table = 'wms_machines';

    protected $fillable = ['name', 'code', 'description', 'status', 'zone_id', 'machine_type_id', 'responsable_id', 'width', 'height', 'depth', 'weight', 'distribution_center_id', 'warehouse_id','company_id'];

    public function zone()
    {
      return $this->belongsTo('App\Models\Zone');
    }

    public function warehouse()
    {
      return $this->belongsTo('App\Models\Warehouse');
    }

    public function distribution_center()
    {
      return $this->belongsTo('App\Models\DistributionCenter');
    }

    public function machine_type()
    {
        return $this->belongsTo('App\Models\MachineType');
    }

    public function person()
    {
      return $this->belongsTo('App\Models\Person', 'responsable_id');
    }

    public function machine_features()
    {
      return $this->hasMany('App\Models\MachineFeature');
    }
}
