<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');



$api->version('v1', ['namespace' => 'App\Http\Controllers'], function ($api) {
    $api->group(['middleware' => 'api.auth'], function ($api) {
    });

    $api->post('login', 'AuthController@login');
    $api->post('register', 'AuthController@register');
});
