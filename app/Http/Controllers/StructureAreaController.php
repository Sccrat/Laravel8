<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Structure;
use App\Models\Area;
use App\Models\StructureArea;
use App\Models\StructureAreaLevel;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use DB;

class StructureAreaController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $structures = DB::table('wms_structure_areas as sa')
                      ->join('wms_structures as s', 'sa.structure_id', '=', 's.id')
                      //->join('wms_areas as a', 'sa.area_id', '=', 'a.id')
                      ->select('structure_id as id','s.name')
                      //->select('structure_id as id','s.name', 'sa.description', 'a.name as area', 'sa.quantity')
                      ->groupBy('structure_id', 'name')
                      ->orderBy('name')
                      //->select('structure_id as id','wms_structures.name', DB::raw('SUM(quantity) as areas'))
                      ->get();

        return $structures;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\StructureAreaRequest $request)
    {

      $data = $request->all();

      //DB::table('wms_structure_areas')->where('structure_id', $data['structure_id'])->delete();

      StructureArea::insert($data['structure_area_detail']);

      $this->InsertLevels($data['structure_id']);

      return $this->response->created();
      // return $structureArea->toArray();
        // $data = $request->all();
        // $structureArea = new StructureArea();
        // $structureArea->name = $data['name'];
        // $structureArea->levels = $data['levels'];
        // $structureArea->positions = $data['positions'];
        // $structureArea->structure_id = $data['structure_id'];
        //
        // $structureArea->save();
        //
        // $structureArea->structure_area_detail()->createMany($data['structure_area_detail']);
        //
        // return $structureArea->toArray();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //Get the structure areas by structure id
        $struct = DB::table('wms_structure_areas')
                  ->join('wms_areas', 'wms_areas.id', '=', 'wms_structure_areas.area_id')
                  ->select('wms_areas.id as id',
                  'wms_areas.is_storage',
                  'wms_areas.name',
                  'wms_structure_areas.description',
                  'wms_structure_areas.levels',
                  'wms_structure_areas.rows',
                  'wms_structure_areas.modules',
                  'wms_structure_areas.weight',
                  'wms_structure_areas.depth',
                  'wms_structure_areas.width_position',
                  'wms_structure_areas.height_position',
                  'wms_structure_areas.positions',
                  'wms_structure_areas.structure_id',
                  'wms_structure_areas.quantity',
                  'wms_structure_areas.width',
                  'wms_structure_areas.height',
                  'wms_structure_areas.top',
                  'wms_structure_areas.left',
                  'wms_structure_areas.color',
                  'wms_structure_areas.id as structure_area_id')
                  ->where('structure_id', $id)
                  ->get();
        //$struct = StructureArea::where('structure_id', $id)->get();
        $data['structure_area_detail'] = $struct;
        $data['structure_id'] = intval($id);
        return $data;
        // $structures = StructureArea::findOrFail($id);
        // $result = $structures->toArray();
        //
        // $detail = DB::table('wms_structure_areas_detail')
        // ->join('wms_areas', 'wms_areas.id', '=', 'wms_structure_areas_detail.area_id')
        // ->select('wms_areas.id','wms_areas.name', 'wms_areas.id as area_id', 'wms_structure_areas_detail.quantity', 'wms_structure_areas_detail.description')
        // ->where('structure_area_id', $id)
        // ->get();
        //
        // $result['structure_area_detail'] = $detail;
        //
        // return $result;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\StructureAreaRequest $request, $id)
    {
      $data = $request->all();
      //get the actual configuration
      $actual = StructureArea::where('structure_id', $id)->get();
      $sended = $data['structure_area_detail'];
      //Foreach the array
      foreach ($sended as $send) {
        //Check if the field have structure_area_id
        if($send['structure_area_id'] > 0) {
          //Is an update
          $StructureArea = StructureArea::findOrFail($send['structure_area_id']);

          $StructureArea->description = $send['description'];
          $StructureArea->levels = $send['levels'];
          $StructureArea->positions = $send['positions'];
          $StructureArea->modules = $send['modules'];
          $StructureArea->quantity = $send['quantity'];
          $StructureArea->rows = $send['rows'];
          $StructureArea->width_position = $send['width_position'];
          $StructureArea->height_position = $send['height_position'];

          $StructureArea->save();
        } else {
          //Create tje Structure area
          $created = StructureArea::create($send);
          //Create the configurtion
          $this->InsertLevel($created->id);
        }

      }

      //Delete the removed
      $toDelete = [];
      foreach ($actual as $a) {
        $result = $this->searcharray($a->id,'structure_area_id', $sended);
        if(is_null($result)) {
          $toDelete[] = $a->id;
        }
        // $jaja = 'lol';
        // $key = array_search($a->id, array_column($sended, 'structure_area_id'));
        // $ji = 'ji';
      }
      if(count($toDelete)) {
        DB::table('wms_structure_area_levels')->whereIn('structure_area_id', $toDelete)->delete();
        DB::table('wms_structure_areas')->whereIn('id', $toDelete)->delete();
      }
      $lol = 'lol';
      // DB::table('wms_structure_areas')->where('structure_id', $id)->delete();
      //
      // $data = $request->all();
      //
      // StructureArea::insert($data['structure_area_detail']);


      return $this->response->created();
    }

    function searcharray($value, $key, $array) {
       foreach ($array as $k => $val) {
           if ($val[$key] == $value) {
               return $k;
           }
       }
       return null;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      //Delete the detail
      DB::table('wms_structure_areas')->where('structure_id', $id)->delete();

      return $this->response->noContent();
        // //Get the structureArea
        // $structureArea = StructureArea::findOrFail($id);
        //
        // //Delete the detail
        // DB::table('wms_structure_areas_detail')->where('structure_area_id', $id)->delete();
        // $structureArea->delete();
        //
        // return $this->response->noContent();
    }

    public function getByStructure($id)
    {
        //Get the structure areas
        try {
          $struct = StructureArea::where('structure_id', $id)->firstOrFail();
          $data['structure_area_detail'] = $struct->toArray();
          return $data;
          // $struct = StructureArea::where('structure_id', $id)->firstOrFail();
          // return $struct->toArray();
        } catch (ModelNotFoundException $e) {
          return $this->response->error('not_found', 404);
        }
    }

    public function InsertLevels($id)
    {
      //Get all the is storage
      $isStorage = StructureArea::where('structure_id', $id)
      ->whereHas('area', function ($query)
      {
        $query->where('is_storage', true);
      })
      ->get();

      $levelSet = [];
      $abc = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
      foreach ($isStorage as $storage) {
        //Check if have configuration
        $rows = $storage->rows;
        $levels = $storage->levels;
        $modules = $storage->modules;
        $positions = $storage->positions;

        for ($r=1; $r <= $rows; $r++) {
          for ($l=1; $l <= $levels; $l++) {
            for ($m=1; $m <= $modules; $m++) {
              for ($p=0; $p < $positions ; $p++) {
                $pos = $abc[$p];
                $levelSet[] = [
                  'row' => $r,
                  'level' => $l,
                  'module' => $m,
                  'position' => $pos,
                  'structure_area_id' => $storage->id,
                  'description' => $r . ' ' . $l . ' ' . $m . ' ' . $pos,
                  'weight' => $storage->weight,
                  'depth' => $storage->depth,
                  'width' => $storage->width_position,
                  'height' => $storage->height_position
                ];
              }
            }
          }
        }
        //insert the configuration
        StructureAreaLevel::insert($levelSet);
      }
    }

    public function InsertLevel($id)
    {
      //Get all the is storage
      $storage = StructureArea::findOrFail($id);

      $levelSet = [];
      //Check if have configuration
      $rows = $storage->rows;
      $levels = $storage->levels;
      $modules = $storage->modules;
      $positions = $storage->positions;
      $abc = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

      for ($r=1; $r <= $rows; $r++) {
        for ($l=1; $l <= $levels; $l++) {
          for ($m=1; $m <= $modules; $m++) {
            for ($p=0; $p < $positions ; $p++) {
              $pos = $abc[$p];
              $levelSet[] = [
                'row' => $r,
                'level' => $l,
                'module' => $m,
                'position' => $pos,
                'structure_area_id' => $storage->id,
                'description' => $r . ' ' . $l . ' ' . $m . ' ' . $pos,
                'weight' => $storage->weight,
                'depth' => $storage->depth,
                'width' => $storage->width_position,
                'height' => $storage->height_position
              ];
            }
          }
        }
      }
      //insert the configuration
      StructureAreaLevel::insert($levelSet);
    }

    public function GetStorageAreas($id)
    {
      $structureAreas =  StructureArea::where('structure_id', $id)
      ->whereHas('area', function ($query)
      {
        $query->where('is_storage', true);
      })
      ->orderBy('description')
      ->get();

      return $structureAreas->toArray();
    }
}
