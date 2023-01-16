<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest as Request;

class StructureAreaRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
      $this->sanitize();

      return [];
        // $rules  = [
        //   'name' => 'required|max:255',
        //   'structure_id' => 'required|unique:wms_structure_areas|exists:wms_structures,id',
        // ];
        //
        // // return rules;
        //
        // foreach($this->request->get('structure_area_detail') as $key => $val)
        // {
        //   $rules['structure_area_detail.'.$key.'.quantity'] = 'required';
        //   $rules['structure_area_detail.'.$key.'.description'] = 'required';
        // }

        // return $rules;
    }

    public function sanitize()
    {
      $input = $this->all();
      $method = $this->method();
      $detail = $input['structure_area_detail'];
      $sanitized = array();
      $arr_length = count($detail);
      for($i=0;$i<$arr_length;$i++)
      {
        $sanitized[$i]['description'] = $detail[$i]['description'];
        $sanitized[$i]['levels'] = array_key_exists('levels',  $detail[$i]) ? $detail[$i]['levels'] : 0;
        $sanitized[$i]['rows'] = array_key_exists('rows',  $detail[$i]) ? $detail[$i]['rows'] : 0;
        $sanitized[$i]['width_position'] = array_key_exists('width_position',  $detail[$i]) ? $detail[$i]['width_position'] : 0;
        $sanitized[$i]['height_position'] = array_key_exists('height_position',  $detail[$i]) ? $detail[$i]['height_position'] : 0;
        $sanitized[$i]['positions'] = array_key_exists('positions',  $detail[$i]) ? $detail[$i]['positions'] : 0;
        $sanitized[$i]['weight'] = array_key_exists('weight',  $detail[$i]) ? $detail[$i]['weight'] : 0;
        $sanitized[$i]['depth'] = array_key_exists('depth',  $detail[$i]) ? $detail[$i]['depth'] : 0;
        $sanitized[$i]['modules'] = array_key_exists('modules',  $detail[$i]) ? $detail[$i]['modules'] : 0;
        $sanitized[$i]['structure_id'] = $detail[$i]['structure_id'];
        $sanitized[$i]['area_id'] = array_key_exists('area_id',  $detail[$i]) ? $detail[$i]['area_id'] : $detail[$i]['id'];
        $sanitized[$i]['quantity'] = array_key_exists('quantity',  $detail[$i]) ? $detail[$i]['quantity'] : 1;
        if($method == 'PUT') {
          $sanitized[$i]['structure_area_id'] = array_key_exists('structure_area_id',  $detail[$i]) ? $detail[$i]['structure_area_id'] : 0;
        }

        //$sanitized[$i]['structure_area_levels'] = $detail[$i]['structure_area_levels'];
        //array_key_exists('name', $data) ? $data['name'] : $structure->name;
        // unset($detail[$i]['is_storage']);
        // unset($detail[$i]['$$hashKey']);
        // unset($detail[$i]['id']);
        // unset($detail[$i]['id']);
      }

      $input['structure_area_detail'] = $sanitized;

      $this->replace($input);
    }
}
