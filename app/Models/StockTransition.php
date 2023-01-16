<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransition extends Model
{
    protected $table = 'wms_stock_transition';
    protected $fillable = ['zone_position_id', 'code128_id','code14_id','product_id','quanty','action','concept','warehouse_id','user_id','code_ean14','document_detail_id','quanty_14'];

    public function product()
	{
	  return $this->belongsTo('App\Models\Product');
	}

	public function zone_position()
	{
	  return $this->belongsTo('App\Models\ZonePosition');
	}

	public function ean128()
	{
	  return $this->belongsTo('App\Models\EanCode128','code128_id');
	}
	public function document_detail()
  {
    return $this->belongsTo('App\Models\DocumentDetail','document_detail_id','id');
  }
	public function ean14()
	{
	  return $this->belongsTo('App\Models\EanCode14','code_ean14','id');
	}
	public function ean13()
	{
	  return $this->belongsTo('App\Models\Product','product_id');
	}
  public function schedule_transition()
	{
	  return $this->hasMany('App\Models\ScheduleTransition', 'transition_id');
	}
  public function stock_picking_config_product()
	{
	  return $this->belongsTo('App\Models\StockPickingConfigProduct', 'product_id');
	}

}
