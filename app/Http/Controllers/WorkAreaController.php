<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkArea;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class WorkAreaController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $workAreas = WorkArea::orderBy('name')->get();
        return $workAreas->toArray();
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
      WorkArea::create($data);
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
      // $workArea = WorkArea::with('structure_area')->findOrFail($id);
      // return $workArea->toArray();
      $workArea = WorkArea::where('structure_area_id',$id)->first();
      return $workArea->toArray();
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
      $workArea = WorkArea::findOrFail($id);

      $workArea->name = array_key_exists('name', $data) ? $data['name'] : $workArea->name;
      $workArea->active = array_key_exists('active', $data) ? $data['active'] : $workArea->active;
      $workArea->from_row = array_key_exists('from_row', $data) ? $data['from_row'] : $workArea->from_row;
      $workArea->to_row = array_key_exists('to_row', $data) ? $data['to_row'] : $workArea->to_row;
      $workArea->from_position = array_key_exists('from_position', $data) ? $data['from_position'] : $workArea->from_position;
      $workArea->to_position = array_key_exists('to_position', $data) ? $data['to_position'] : $workArea->to_position;
      $workArea->structure_area_id = array_key_exists('structure_area_id', $data) ? $data['structure_area_id'] : $workArea->structure_area_id;

      $workArea->save();

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
      $workArea = WorkArea::findOrFail($id);
      $workArea->active = false;
      $workArea->save();

      return $this->response->noContent();
    }

    public function getByStructure($id)
    {
      // $workAreas = DB::table('')
      $workAreas = WorkArea::whereHas('structure_area', function ($query) use ($id)
      {
        $query->where('structure_id', $id);
      })
      ->get();

      return $workAreas->toArray();
    }
}
