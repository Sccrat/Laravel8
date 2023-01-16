<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    public $timestamps = false;

    protected $table = 'wms_services';

    protected $fillable = ['description','remark', 'name', 'item_number', 'vendor_name', 'unit_cost', 'retail', 'special', 'dealer', 'distribuitor', 'prefer','vendor_id'];

    public function vendor(){
        return $this->hasOne('App\Models\Client','id','vendor_id');
    }

}
