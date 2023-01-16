<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'wms_orders';

    protected $fillable = ['number', 'date', 'start_date', 'final_date', 'code', 'identification', 'bill_number', 'list', 'phone_number', 'city', 'client', 'address', 'seller', 'pay_method', 'document', 'delivery_site', 'delivery_address', 'client_name', 'quanty', 'total', 'active'];

    public function schedules()
    {
      return $this->hasMany('App\Models\Schedule');
    }

    public function order_detail()
    {
      return $this->hasMany('App\Models\OrderDetail', 'order_number', 'number');
    }
}
