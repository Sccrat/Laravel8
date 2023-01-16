<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class UserController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $company = $request->input('company_id');
      $users = User::with('person', 'role')->where('company_id', $company)->orderBy('name')->get();

      return $users->toArray();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\UserRequest $request)
    {
      $data = $request->all();
      $data['password'] = bcrypt($data['password']);

      //Get the company
      // $company = Company::first();
      $data['company_id'] = $request->input('company_id');

      return User::create($data);
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
        $user = User::with('person')->findOrFail($id);
        return $user->toArray();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\UserUpdateRequest $request, $id)
    {
      $data = $request->all();
      $user = User::findOrFail($id);

      $user->name = array_key_exists('name', $data) ? $data['name'] : $user->name;
      $user->email = array_key_exists('email', $data) ? $data['email'] : $user->email;
      $user->username = array_key_exists('username', $data) ? $data['username'] : $user->username;
      $user->password = array_key_exists('password', $data) ?  bcrypt($data['password']) : $user->password;
      $user->role = array_key_exists('role', $data) ?  $data['role'] : $user->role;
      $user->role_id = array_key_exists('role_id', $data) ?  $data['role_id'] : $user->role_id;
      $user->personal_id = array_key_exists('personal_id', $data) ?  $data['personal_id'] : $user->personal_id;
      $user->active = array_key_exists('active', $data) ?  $data['active'] : $user->active;

      $user->socket_id = array_key_exists('socket_id', $data) ?  $data['socket_id'] : $user->socket_id;

      $user->save();

      return $user->toArray();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $structure = User::findOrFail($id);
      $structure->active = false;
      $structure->save();
      return $this->response->noContent();
    }
}
