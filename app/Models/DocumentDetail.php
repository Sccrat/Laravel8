<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentDetail extends Model
{
    protected $table = 'wms_document_details';

    public $timestamps = false;

// OldValues
    // protected $fillable = ['document_id', 'number', 'reference', 'description', 'size', 'colour', 'code', 'unit', 'plu', 'pvp', 'ean', 'quanty', 'package', 'value', 'iva', 'discount', 'ret_fuente', 'ret_iva', 'total', 'cartons', 'quarantine','product_id','is_additional','damaged_cartons','weight'];

    protected $fillable = ['code_ean14','document_id', 'reference', 'description', 'size', 'colour', 'code', 'unit', 'ean', 'quanty', 'quanty_received', 'package', 'total', 'cartons', 'quarantine','product_id','is_additional','damaged_cartons','weight','approve_additional','status','code_ean14','batch','expiration_date','quanty_received_pallet','client_id','good','seconds','sin_conf','good_receive','seconds_receive','sin_conf_receive','new_quanty_receive','relocated_quanty'];

    public function document()
    {
      return $this->belongsTo('App\Models\Document');
    }

    public function product()
    {
      return $this->belongsTo('App\Models\Product');
    }

    public function product_ean14()
    {
      return $this->belongsTo('App\Models\ProductEan14','code_ean14','code_ean14');
    }

    public function detailCount()
    {
      return $this->hasMany('App\Models\DocumentDetailCount')->whereRaw('count_parent is null');
    }

    public function ean14()
    {
      return $this->hasMany('App\Models\EanCode14','document_id','document_id')->where('canceled',0);
    }
    public function pallet()
    {
      return $this->hasOne('App\Models\Pallet');
    }
    public function detailMultiple()
    {
      return $this->hasMany('App\Models\DocumentDetailMultiple');
    }

    public function stock()
    {
      return $this->belongsTo('App\Models\Stock','id','document_detail_id');
    }

    public function client()
    {
      return $this->belongsTo('App\Models\Client');
    }

    public function compound()
  {
    return $this->hasMany('App\Models\CompoundProduct','parent_product_id','product_id');
  }

  public function detail14()
  {
    return $this->hasMany('App\Models\EanCode14Detail');
  } 

}
