<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;

use Authorizer;
use Auth;
// use App\Http\Requests;
// use Illuminate\Http\Request;
// use Dingo\Api\Routing\Helper;
// use App\Http\Controllers\Controller;

class OAuthController extends BaseController
{
  //use Helper;

  public function authorizeClient()
  {
    return $this->response->array(Authorizer::issueAccessToken());
  }

  public function authorizePassword($username, $password)
  {

    // Get the company and the user
    $userCredentiasl = explode("|", $username);

    if(filter_var($userCredentiasl[0], FILTER_VALIDATE_EMAIL)) {
        // valid address
        $credentials = [
          'email' => $userCredentiasl[0],
          'password' => $password,
          'company_id' => $userCredentiasl[1]
        ];
    }
    else {
        // invalid address
        $credentials = [
          'username' => $userCredentiasl[0],
          'password' => $password,
          'company_id' => $userCredentiasl[1]
        ];
    }

    $credentials['active'] = 1;

    if (Auth::once($credentials)) {
      return Auth::user()->id;
    }

    return false;
  }
}
