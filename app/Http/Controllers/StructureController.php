<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Structure;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

class StructureController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      //
      // $structures = Structure::with('city')->where('active', true)->get();
      // return $this->response->array($structures->toArray());
      $structures = DB::table('wms_structures')
            ->join('cities', 'cities.id', '=', 'wms_structures.city_id')
            ->join('countries', 'cities.country_code', '=', 'countries.code')
            ->join('wms_structure_types', 'wms_structures.structure_type_id', '=', 'wms_structure_types.id')
            ->leftJoin('wms_structures as wm', 'wm.id', '=', 'wms_structures.parent_id')
            ->select('wms_structures.*',
                      'wms_structures.name',
                      'wms_structure_types.name as structure_type',
                      'wms_structure_types.parent_required',
                      'wms_structure_types.configurable',
                      DB::raw(' CONCAT(cities.name," - ",countries.name) as location'),
                      DB::raw('(SELECT COUNT(wms_structure_areas.id) FROM wms_structure_areas WHERE wms_structure_areas.structure_id = wms_structures.id) as has_areas'),
                      'wm.name as parent_name')
            //->where('wms_structures.active', true)
            //->orderBy('name', 'structure_type')
            //ORDER BY COALESCE(parent, id), parent IS NOT NULL, id
            ->orderByRaw('COALESCE(wms_structures.parent_id, wms_structures.id), wms_structures.parent_id IS NOT NULL, wms_structures.id')
            ->get();
      return $structures;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\StructureRequest $request)
    {
      $data = $request->all();
      $data['city_id'] = $data['city']['id'];
      //get father code
      // $parentCode = '';
      // if(array_key_exists('parent_id', $data)) {
      //   $parentCode = DB::table('wms_structures')->where('id', $data['parent_id'])->pluck('real_code');
      // }
      //
      // $data['real_code'] = $parentCode . $data['code'];
      //return $this->response->item($data);
      //return $data;
      return Structure::create($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      //Get the structures with the nested city
      $structure = Structure::with('city')->findOrFail($id);
      $result = $structure->toArray();
      //Get the country name and attach to the nested city
      $countryCode = $result['city']['country_code'];
      $countryName = DB::table('countries')->where('code', '=', $countryCode)->pluck('name');
      $result['city']['country_name'] = $countryName;
      //Get the children

      return $result;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\StructureUpdateRequest $request, $id)
    {
      $data = $request->all();
      $structure = Structure::findOrFail($id);

      $structure->name = array_key_exists('name', $data) ? $data['name'] : $structure->name;
      $structure->parent_id = array_key_exists('parent_id', $data) ? $data['parent_id'] : $structure->parent_id;
      $structure->structure_type_id = array_key_exists('structure_type_id', $data) ? $data['structure_type_id'] : $structure->structure_type_id;
      $structure->address = array_key_exists('address', $data) ? $data['address'] : $structure->address;
      $structure->active = array_key_exists('active', $data) ? $data['active'] : $structure->active;
      $structure->code = array_key_exists('code', $data) ? $data['code'] : $structure->code;
      $structure->city_id = array_key_exists('city', $data) ? $data['city']['id'] : $structure->city_id;


      if(array_key_exists('code', $data)) {
        $parentCode = '';
        $parentCode = DB::table('wms_structures')->where('id', $structure->parent_id)->pluck('real_code');
        $structure->real_code = $parentCode . $structure->code;
      }



      $structure->save();

      //Update children code


      return $structure->toArray();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      //Change the status of the structure and all the children
      Structure::whereIn('id', $this->childrenArray($id))->update(['active' => false]);
      return $this->response->noContent();
    }

    //Get the object of all the children
    function getChildren($id, &$children)
    {
      $structure = Structure::where('parent_id','=',$id)->get();
      $structure = $structure->toArray();
      //Check if has children
      foreach ($structure as &$val) {
        $val['children'] = $this->getChildren($val['id'], $children);
      }
      $children[] = $id;

      return $structure;
    }

    //Get the children's id as an array
    public function children($id)
    {
      if($id != 0) {
        $children = array();
        $this->getChildren($id,$children);
        //$structure = $this->getChildren($id,$children);
        $structures = Structure::where('active', true)->whereNotIn('id', $children)->orderBy('name')->get();
      } else {
        $structures = Structure::where('active', true)->orderBy('name')->get();
      }

      return $structures->toArray();

      //return $structure;
    }

    public function childrenArray($id)
    {

      $children = array();
      $id = (int)$id;
      $this->getChildren($id,$children);
      return $children;
    }

    public function getParentName($id)
    {
      $res = DB::table('wms_structures as s1')
      ->join('wms_structures as s2', 's1.parent_id', '=', 's2.id')
      ->where('s1.id', $id)->select('s2.name as parent','s1.name as child')->get();
      return $res;
    }
}
