<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest as Request;

class SettingRequest extends Request
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
        'value' => 'required',
        'key' => 'required|unique:wms_settings'
      ];
    }

    public function messages()
    {
      return [
        'value.required' => 'warehouse_error_value_required',
        'key.required' => 'warehouse_error_real_code_required',
        'key.unique' => 'warehouse_error_key_unique'
      ];
    }
}
