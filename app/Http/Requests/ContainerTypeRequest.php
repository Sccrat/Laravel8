<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest as Request;

class ContainerTypeRequest extends Request
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
        'code_container_type' => 'required|unique:wms_container_types',
        'ia_code_id' => 'required|exists:wms_ia_codes,id'
      ];
    }

    public function messages()
    {
      return [
        'name.required' => 'warehouse_error_name_required',
        'ia_code_id.required' => 'container_type_error_ia_code_id_required',
        'ia_code_id.exists' => 'container_type_error_ia_code_id_exists',
        'code_container_type.required' => 'warehouse_error_real_code_required',
        'code_container_type.unique' => 'warehouse_error_real_code_unique'
      ];
    }
}
