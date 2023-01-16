<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptType extends Model
{
    protected $table = 'wms_receipt_types';

    protected $fillable = ['name', 'active'];

    public function documents()
    {
      return $this->hasMany('App\Models\Document');
    }

    public function schedule_receipt()
    {
      return $this->hasMany('App\Models\ScheduleReceipt');
    }
}
