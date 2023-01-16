<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest as Request;

class StructureUpdateRequest extends Request
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
            'structure_type_id' => 'exists:wms_structure_types,id',
            'code' => 'unique:wms_structures|max:50',
            'city.id' => 'exists:cities,id'
        ];
    }

    public function messages()
    {
      return [
        'structure_type_id.exists' => 'structure_error_structure_type_id_exists',
        'code.unique' => 'structure_error_code_unique'
      ];
    }
}
