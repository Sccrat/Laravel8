<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentDetailMultipleCount extends Model
{
    
    protected $fillable = ['document_detail_count_id','product_id','quanty','quanty1','quanty2','quanty3'];

	protected $table = 'wms_document_detail_multiples_count';

    public function product()
    {
      return $this->belongsTo('App\Models\Product');
    }
    
    public function documentDetailCount()
	{
	  return $this->belongsTo('App\Models\DocumentDetailCount');
	}


}
