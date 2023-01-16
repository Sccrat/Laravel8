<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentDetailCount extends Model
{
	  protected $fillable = ['id','document_detail_id','quanty','is_additional','has_error','quanty1','quanty2','quanty3','quarantine','damaged','count_parent','ean14_id','code_ean14'];

    protected $table = 'wms_document_detail_count';

    public function documentDetail()
  	{
    	return $this->belongsTo('App\Models\DocumentDetail');
  	}
    public function child_count()
    {
      return $this->hasMany('App\Models\DocumentDetailCount','count_parent');
    }
    public function ean14()
    {
      return $this->belongsTo('App\Models\EanCode14');
    }

    public function detailMultipleCount()
    {
      return $this->hasMany('App\Models\DocumentDetailMultipleCount');
    }
}
