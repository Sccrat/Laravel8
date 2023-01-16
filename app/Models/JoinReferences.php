<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JoinReferences extends Model
{
    protected $table = 'wms_join_references';

    protected $fillable = ['product_id_target', 'product_id_source', 'active'];

    public function productSource()
	{
	      return $this->belongsTo('App\Models\Product','product_id_source');
    }
}
