<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest as Request;

class ZoneConceptRequest extends Request
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
        'name' => 'required|unique:wms_zone_concepts',
        'is_storage' => 'unique:wms_zone_concepts,is_storage,NULL,id,is_storage,1'
      ];
    }

    public function messages()
    {
      return [
        'name.required' => 'zone_concept_error_name_required',
        'name.unique' => 'zone_concept_error_name_unique',
        'is_storage.unique' => 'zone_concept_error_storage_unique'
      ];
    }
}
