<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest as Request;

class WarehouseRequest extends Request
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
        'real_code' => 'required|unique:wms_warehouses',
        'address' => 'required',
        'distribution_center_id' => 'required|exists:wms_distribution_centers,id'
      ];
    }

    public function messages()
    {
      return [
        'name.required' => 'warehouse_error_name_required',
        'code.required' => 'warehouse_error_code_required',
        'address.required' => 'warehouse_error_address_required',
        'distribution_center_id.required' => 'warehouse_error_distribution_center_id_required',
        'distribution_center_id.exists' => 'warehouse_error_distribution_center_id_exists',
        'real_code.required' => 'warehouse_error_real_code_required',
        'real_code.unique' => 'warehouse_error_real_code_unique'        
      ];
    }
}
