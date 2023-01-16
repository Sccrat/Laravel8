<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Models\Test;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Common\Settings;
use App\Enums\SizeKey;
use DB;
use GuzzleHttp\Client;

// use App\Models\Setting;

//use Dingo\Api\Routing\Helpers;
//use Illuminate\Routing\Controller;

class TestController extends BaseController
{

  //use Helpers;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // //test api call
        // //https://jsonplaceholder.typicode.com/posts

        // // Create a client with a base URI
        $client = new Client(['base_uri' => 'https://jsonplaceholder.typicode.com/']);
        // // Send a request to https://foo.com/api/test
        $response = $client->get('posts');

        // $res = json_encode($response->getBody());
        $body = $response->getBody();
        $jonson = json_decode($body, true);
        return $jonson;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         // // Create a client with a base URI
        $client = new Client(['base_uri' => 'https://jsonplaceholder.typicode.com/']);

        $body = [
            'title' => 'oelo',
            'body' => 'epa',
            'userId' => 1
        ];
        $response = $client->request('POST', '/posts', ['form_params' => $body]);

        return $response->getBody();
        //
        // $data = $request->all();

        // //return $this->response->item($data);
        // return Test::create($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        return Test::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $data = $request->all();
        $test = Test::findOrFail($id);

        $test->name = array_key_exists('name', $data) ? $data['name'] : $test->name;
        $test->phone = array_key_exists('phone', $data) ? $data['phone'] : $test->phone;
        $test->email = array_key_exists('email', $data) ? $data['email'] : $test->email;

        $test->save();

        return $test;
        //return $this->response->item($test);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $test = Test::findOrFail($id);
        $test->delete();

        return $this->response->noContent();
    }
}
