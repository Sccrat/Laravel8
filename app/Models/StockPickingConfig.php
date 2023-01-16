<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockPickingConfig extends Model
{

    protected $table = 'wms_stock_picking_config';
  	protected $fillable = ['warehouse_id','zone_position_id','active','min_stock','stock_secure','schedule_id'];


    public function stock_picking_config_product()
    {
        return $this->hasMany('App\Models\StockPickingConfigProduct');
    }

    public function zone_position()
    {
      return $this->belongsTo('App\Models\ZonePosition');
    }

}
