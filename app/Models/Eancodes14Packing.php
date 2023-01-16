<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eancodes14Packing extends Model
{
    public $timestamps = false;
    protected $table = 'wms_eancodes14_packing';

    protected $fillable = ['code_ean14', 'document_id', 'code128_id', 'quanty_14', 'stock_id', 'good', 'seconds', 'product_id', 'relocated'];

    public function ean14()
    {
        return $this->belongsTo('App\Models\ProductEan14', 'code_ean14', 'code_ean14');
    }

    public function document()
    {
        return $this->belongsTo('App\Models\Document', 'document_id');
    }

    public function stock()
    {
        return $this->belongsTo('App\Models\Stock', 'stock_id', 'id');
    }

    public function enlist_products()
    {
        return $this->belongsTo('App\Models\EnlistProducts', 'code_ean14', 'code_ean14');
    }

    public function ean128()
    {
        return $this->hasMany('App\Models\EanCode128', 'id', 'code128_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }
}
