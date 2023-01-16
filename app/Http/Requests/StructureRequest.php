<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest as Request;

class StructureRequest extends Request
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
            'structure_type_id' => 'required|exists:wms_structure_types,id',
            'code' => 'required|max:50',
            'real_code' => 'unique:wms_structures|max:50',
            'city.id' => 'required|exists:cities,id'
        ];
    }

    public function messages()
    {
      return [
        'name.required' => 'structure_error_name_required',
        'structure_type_id.required' => 'structure_error_structure_type_id_required',
        'structure_type_id.exists' => 'structure_error_structure_type_id_exists',
        'code.required' => 'structure_error_code_required',
        'real_code.unique' => 'structure_error_code_unique'
      ];
    }
}
