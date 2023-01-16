<?php
namespace App\Common;

use App\Models\User;

class UserCommon
{

  public static function getUsernameById($id)
  {
    $username = '';
    if(!empty($id)){
      $user = User::find($id);
      if (!empty($user)) {
        $username = $user->username;
      }
    }
    return $username;
  }
}
