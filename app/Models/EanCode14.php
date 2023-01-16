<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Common\Ean14Observer;

class EanCode14 extends Model
{
  protected $table = 'wms_ean_codes14';

  protected $fillable = ['document_detail_id', 'code14', 'code13', 'canceled', 'quanty', 'container_id', 'reason_code_id', 'code_parent_id', 'product_id', 'damaged', 'quarantine', 'stored', 'company_id', 'facturation_number', 'status', 'consecutive', 'schedule_id', 'weight', 'document_id', 'observation_auditor', 'observation_driver', 'quanty_received_pallet', 'master', 'zone_position_id'];

  public function pallet()
  {
    return $this->hasOne('App\Models\Pallet', 'code14_id');
  }

  public function transformDetail()
  {
    return $this->hasOne('App\Models\ScheduleTransformDetail', 'code14_id');
  }

  public function scheduleTransformResultPackaging()
  {
    return $this->hasOne('App\Models\ScheduleTransformResultPackaging', 'ean14_id');
  }

  public function unjoinDetail()
  {
    return $this->hasOne('App\Models\ScheduleUnjoinDetail', 'code14_id');
  }

  public function stock()
  {
    return $this->hasMany('App\Models\Stock', 'code14_id');
  }

  public function documentDetail()
  {
    return $this->belongsTo('App\Models\DocumentDetail');
  }
  public function product()
  {
    return $this->belongsTo('App\Models\Product');
  }

  public function child_codes()
  {
    return $this->hasMany('App\Models\EanCode14', 'code_parent_id');
  }

  public function detail()
  {
    return $this->hasOne('App\Models\EanCode14Detail', 'ean_code14_id', 'id');
  }

  public function serial()
  {
    return $this->hasMany('App\Models\EanCode14Serial', 'ean_codes14_id');
  }

  public function parent_code()
  {
    return $this->belongsTo('App\Models\EanCode14', 'code_parent_id');
  }

  public function countDocument()
  {
    return $this->hasOne('App\Models\DocumentDetailCount', 'ean14_id');
  }

  public function document()
  {
    return $this->belongsTo('App\Models\Document', 'document_id');
  }

  public function detail_m()
  {
    return $this->hasMany('App\Models\EanCode14Detail');
  }

  public function schedule_ean14()
  {
    return $this->hasOne('App\Models\ScheduleEAN14', 'ean14_id');
  }
  public function detalles()
  {
    return $this->hasMany('App\Models\EanCode14Detail', 'ean_code14_id', 'id');
  }

  // public static function boot() {
  //       parent::boot();
  //       self::observe(new Ean14Observer);
  //   }
}
