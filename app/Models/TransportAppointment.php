<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportAppointment extends Model
{
    protected $table = 'wms_clients';

    public $timestamps = false;

    protected $fillable = ['date_start', 'date_end', 'client_id', 'sector', 'phone', 'document_id', 'driver_id', 'vehicle_id'];

    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }

    public function city()
    {
        return $this->belongsTo('App\Models\City');
    }

    public function driver()
    {
        return $this->belongsTo('App\Models\Driver');
    }

    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle');
    }
}
