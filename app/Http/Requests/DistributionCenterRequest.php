<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest as Request;

class DistributionCenterRequest extends Request
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
        'code' => 'required|unique:wms_distribution_centers|max:50',
        'address' => 'required',
        'city_id' => 'required|exists:cities,id'
      ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
      return [
        'name.required' => 'dc_error_name_required',
        'code.required' => 'dc_error_code_required',
        'code.unique' => 'dc_error_code_unique',
        'city_id.required' => 'dc_error_city_required',
        'address.required' => 'dc_error_address_required'
      ];
    }
}
