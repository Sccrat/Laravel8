<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EanCode14Serial extends Model
{
    protected $table = 'wms_ean_codes14_serial';

    protected $fillable = ['ean_codes14_id', 'serial','product_id','document_id'];
}
