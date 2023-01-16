<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
  use  Notifiable;

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'admin_users';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = ['name', 'email', 'password', 'company_id', 'username', 'role', 'personal_id', 'socket_id', 'role_id'];

  /**
   * The attributes excluded from the model's JSON form.
   *
   * @var array
   */
  protected $hidden = ['password', 'remember_token'];

  public function company()
  {
    return $this->belongsTo('App\Models\Company');
  }

  public function person()
  {
    return $this->belongsTo('App\Models\Person', 'personal_id', 'id');
  }

  public function role()
  {
    return $this->belongsTo('App\Models\Role');
  }

  public function getJWTIdentifier()
  {
    return $this->getKey();
  }

  public function getJWTCustomClaims()
  {
    return [];
  }
}
