<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'wms_documents';

    public $timestamps = false;
    // Old values
    // protected $fillable = ['order_number', 'number', 'order_internal', 'remision', 'agent', 'date', 'start_date', 'final_date', 'code', 'identification', 'bill_number', 'total_boxes', 'list', 'phone_number', 'city', 'zone', 'client', 'address', 'seller', 'pay_method', 'sell_type', 'document', 'delivery_site', 'delivery_address', 'assistant_code', 'delivery_time', 'client_name', 'status', 'quanty', 'sub_total', 'iva', 'ret_fuente', 'ret_iva', 'total', 'trm', 'weight', 'discount', 'observations', 'url_document', 'active', 'receipt_type_id', 'is_partial','is_special','count_status','has_error'];

    protected $fillable = ['total_cost', 'total_benefit', 'type', 'number', 'date', 'total_boxes', 'city', 'client', 'seller', 'document_type', 'status', 'quanty', 'total', 'url_document', 'active', 'receipt_type_id', 'is_partial', 'is_special', 'count_status', 'has_error', 'min_date', 'max_date', 'company_id', 'warehouse_origin', 'warehouse_destination', 'observation', 'address', 'type', 'state', 'country', 'external_number', 'facturation_number', 'send_date', 'facturation_date', 'transportation_company', 'guia_transp', 'consecutive_dispatch', 'fmm_authorization', 'datefmm_authorization', 'departure_date', 'sizfra_document_dispatch', 'sizfra_merchandise_form', 'group', 'master_guide'];


    public function detail()
    {
        return $this->hasMany('App\Models\DocumentDetail');
    }

    public function clientdocument()
    {
        return $this->belongsTo('App\Models\Client', 'client', 'id');
    }

    public function code_packing()
    {
        return $this->hasMany('App\Models\Eancodes14Packing', 'document_id', 'id');
    }

    public function receipt_type()
    {
        return $this->belongsTo('App\Models\ReceiptType');
    }

    public function scheduleDocument()
    {
        return $this->hasMany('App\Models\ScheduleDocument');
    }

    public function schedule_document_receipt()
    {
        return $this->hasOne('App\Models\ScheduleDocument');
    }

    public function detail14()
    {
        return $this->hasMany('App\Models\EanCode14Detail');
    }

    public function ean14()
    {
        return $this->hasMany('App\Models\EanCode14', 'document_id', 'id');
    }

    public function enlistplan()
    {
        return $this->hasMany('App\Models\EnlistProducts', 'document_id', 'id');
    }
}
