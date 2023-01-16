<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest as Request;

class ZoneTypeRequest extends Request
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
        'name' => 'required|unique:wms_zone_types'
      ];
    }

    public function messages()
    {
      return [
        'name.required' => 'zone_type_error_name_required',
        'name.unique' => 'zone_type_error_name_unique'
      ];
    }
}
