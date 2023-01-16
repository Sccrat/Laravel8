<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormClients extends Model
{
    //admin_form_products
    protected $table = 'admin_form_clients';

    protected $fillable = ['field', 'label','placeholder', 'company_id'];

    public $timestamps = false;
}
