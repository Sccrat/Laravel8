<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest as Request;

class IaCodeRequest extends Request
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
        'code_ia' => 'required|unique:wms_ia_codes'
      ];
    }

    public function messages()
    {
      return [
        'name.required' => 'warehouse_error_name_required',
        'code_ia.required' => 'warehouse_error_real_code_required',
        'code_ia.unique' => 'warehouse_error_real_code_unique'
      ];
    }
}
