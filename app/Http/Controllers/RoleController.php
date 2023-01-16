<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RoleCompany;

class RoleController extends BaseController
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    $roles = Role::orderBy('name')->get();

    return $roles->toArray();
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

    $role = Role::create($data);

    return $role->toArray();
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
  }

  public function getRoleTemplate($roleId, $companyId)
  {
    //['role_id', 'menu_template', 'company_id'];
    $RoleCompany = RoleCompany::where('role_id', $roleId)->where('company_id', $companyId)->firstOrFail();

    return $RoleCompany->toArray();
  }

  public function storeRoleTemplate(Request $request)
  {
    $data = $request->all();

    $roleCompany = RoleCompany::updateOrCreate([
      'company_id' => $data['company_id'],
      'role_id' => $data['role_id']
    ], [
      'menu_template' => $data['menu_template']
    ]);

    return $this->response->noContent();
  }

  public function getRolesByCompany(Request $request)
  {
    $companyId = $request->input('company_id');
    $roles = Role::from("admin_roles")->join("admin_role_companies", "admin_roles.id", "admin_role_companies.role_id")->where('admin_role_companies.company_id', $companyId)
      ->selectRaw("admin_roles.id, admin_roles.name")
      // ->orderBy("admin_roles.name", "ASC")
      ->get();
    return $roles->toArray();
  }
}
