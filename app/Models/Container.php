<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
  protected $table = 'wms_containers';

  // protected $fillable = ['name', 'width', 'height', 'depth', 'weight', 'active', 'content_type', 'description', 'container_type_id', 'container_clasification_id'];
  protected $fillable = ['name', 'width', 'height', 'depth', 'weight', 'active', 'content_type', 'description', 'container_type_id', 'container_clasification_id', 'code', 'company_id'];

  public function container()
  {
    return $this->belongsTo('App\Models\Container');
  }
  public function container_type()
  {
      return $this->belongsTo('App\Models\ContainerType');
  }
  public function container_clasification()
  {
      return $this->belongsTo('App\Models\ContainerClasification');
  }

  public function container_features()
  {
    return $this->hasMany('App\Models\ContainerFeature');
  }
}
