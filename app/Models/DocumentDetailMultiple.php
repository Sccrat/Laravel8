<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentDetailMultiple extends Model
{
    
    protected $fillable = ['document_detail_id','product_id','quanty'];

	protected $table = 'wms_document_detail_multiples';

	public $timestamps = false;

    public function product()
    {
      return $this->belongsTo('App\Models\Product');
    }
}
