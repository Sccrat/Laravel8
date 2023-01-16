<?php

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    use Helpers;
    public $res = [
      "respuesta" => "",
      "exito" => 1,
      "mensaje" => ""
    ];
}
