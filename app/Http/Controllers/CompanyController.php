<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Common\Settings;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Common\SettingsConstant;

class CompanyController extends BaseController
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    $companies = Company::orderBy('name')->get();
    return $companies->toArray();
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $data = $request->all();
    $company = Company::create($data);
    $companyId = $company->id;

    //Insert the settings SettingsConstant
    $settings = new SettingsConstant();
    $settings->InsertSettings($companyId);

    //Get the admin role
    $settingsObj = new Settings($companyId);
    $adminRole = $settingsObj->get('admin_role');

    //Create an user for the company $var = preg_replace("/[^A-Za-z0-9]/", "", $var);
    $username = preg_replace("/[^A-Za-z0-9]/", "", strtolower($company->name));
    $roleId = Role::where('name', $adminRole)->value('id');
    $data = [
      'name' => $company->name,
      'email' => $username . '@' . $username . '.com',
      'username' => $username,
      'password' => bcrypt($username . date("Y")),
      'company_id' => $companyId,
      'role_id' => $roleId
    ];

    $user = User::create($data);

    return $company->toArray();
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    $company = Company::findOrFail($id);
    return $company->toArray();
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
    $data = $request->all();
    $company = Company::findOrFail($id);

    $company->name = array_key_exists('name', $data) ? $data['name'] : $company->name;

    $company->save();

    return $this->response->noContent();
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $company = Company::findOrFail($id);

    $company->active = false;

    $company->save();

    return $this->response->noContent();
  }
}
