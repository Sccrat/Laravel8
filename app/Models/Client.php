<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['name', 'address', 'phone', 'email', 'active', 'identification', 'client_id', 'is_branch', 'city_id', 'company_id', 'social_reason', 'address_delivery', 'gln_code', 'contact_name',
        'customer_names',
        'customer_last_names',
        'customer_street',
        'customer_street_2',
        'customer_country_id',
        'customer_state_id',
        'customer_city_id',
        'customer_zip_code',

        'shipping_names',
        'shipping_last_names',
        'shipping_street',
        'shipping_street_2',
        'shipping_country_id',
        'shipping_state_id',
        'shipping_city_id',
        'shipping_zip_code',
        'third',
        'third_type',


        'type',
        'phone_2',
        'price_type',
        'contact_name_1',
        'contact_phone_1',
        'contact_email_1',
        'contact_name_2',
        'contact_phone_2',
        'contact_email_2',
        'branch_office',
        'company_name',
        'shipping_company_name',
        'customer_state',
        'shipping_state',
        'sector',
        'responsible',
        'is_vendor'
    ];

    protected $table = 'wms_clients';


    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }

    public function city()
    {
        return $this->belongsTo('App\Models\City');
    }

    public function customerCity()
    {
        return $this->hasOne('App\Models\City', 'id', 'customer_city_id');
    }

    public function products()
    {
        return $this->hasMany('App\Models\Product');
    }

    public function document()
    {
        return $this->hasMany('App\Models\Document', 'client', 'id');
    }

    public function detail()
    {
        return $this->hasMany('App\Models\DocumentDetail');
    }
}
