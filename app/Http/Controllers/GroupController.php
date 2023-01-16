<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Group;

class GroupController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $companyId = $request->input('company_id');
        $groups = Group::where('active', true)->where('company_id', $companyId)->orderBy('name')->get();
        return $groups->toArray();
    }

    public function getAllGroups(Request $request)
    {
      $companyId = $request->input('company_id');
        $groups = Group::where('company_id', $companyId)->orderBy('name')->get();
        return $groups->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $companyId = $request->input('company_id');
      $data = $request->all();
      $data['company_id'] = $companyId;
      Group::create($data);
      return $this->response->created();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $group = Group::findOrFail($id);
      return $group->toArray();
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
      $group = Group::findOrFail($id);

      $group->name = array_key_exists('name', $data) ? $data['name'] : $group->name;
      $group->active = array_key_exists('active', $data) ? $data['active'] : $group->active;

      $group->save();

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
      $group = Group::findOrFail($id);
      $group->active = false;
      $group->save();

      return $this->response->noContent();
    }
}
