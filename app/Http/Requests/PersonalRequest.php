<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest as Request;

class PersonalRequest extends Request
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
        'identification' => 'required|unique:wms_personal'
      ];
    }

    public function messages()
    {
      return [
        'identification.required' => 'personal_id_error_required',
        'identification.unique' => 'personal_id_error_unique'
      ];
    }
}
