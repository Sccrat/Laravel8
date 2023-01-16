<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest as Request;

class ZoneRequest extends Request
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
       return [
         'name' => 'required',
         'code' => 'required|max:50',
         'real_code' => 'required|unique:wms_zones',
         'warehouse_id' => 'required|exists:wms_warehouses,id'
       ];
     }

     public function messages()
     {
       return [
         'name.required' => 'zone_error_name_required',
         'code.required' => 'zone_error_code_required',
         'warehouse_id.required' => 'zone_error_warehouse_id_required',
         'warehouse_id.exists' => 'zone_error_warehouse_id_exists',
         'real_code.required' => 'zone_error_real_code_required',
         'real_code.unique' => 'zone_error_real_code_unique'
       ];
     }
}
